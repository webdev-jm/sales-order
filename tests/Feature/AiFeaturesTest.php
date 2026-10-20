<?php

namespace Tests\Feature;

use App\Http\Livewire\Ai\Insights;
use App\Http\Livewire\Ai\MappingHelp;
use App\Http\Livewire\AiAssistant\Chat;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\AccountProductReference;
use App\Models\AiUsageLog;
use App\Models\Company;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\Setting;
use App\Models\User;
use App\Services\Ai\Agents\MappingHelper;
use App\Services\Ai\Agents\ReportInsights;
use App\Services\Ai\Agents\SalesAssistant;
use App\Services\Ai\SalesFigures;
use App\Services\Ai\Tools\SalesOrderDetail;
use App\Services\Ai\Tools\SalesSummary;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Session;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Tools\Request as ToolRequest;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\FakesGemini;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * The laravel/ai features: the sales assistant, report insights and upload mapping help.
 */
class AiFeaturesTest extends TestCase
{
    use DatabaseTransactions;
    use FakesGemini;
    use SeedsReferenceData;

    private Account $account;

    private Account $otherAccount;

    private AccountLogin $login;

    private AccountLogin $otherLogin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedSettings();
        $this->configureGemini();
        Setting::query()->orderBy('id')->first()->update(['ai_monthly_token_limit' => null, 'ai_daily_user_token_limit' => null]);
        Config::set('activitylog.enabled', false);
        Queue::fake();
        Cache::flush();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $company = Company::factory()->create();
        $this->account = Account::factory()->create(['company_id' => $company->id, 'account_code' => 'AIT001', 'short_name' => 'AI TEST', 'discount_id' => null]);
        $this->otherAccount = Account::factory()->create(['company_id' => $company->id, 'account_code' => 'AIT999', 'short_name' => 'NOT MINE', 'discount_id' => null]);

        $owner = User::factory()->create(['status' => 'active']);
        $this->login = AccountLogin::factory()->create(['user_id' => $owner->id, 'account_id' => $this->account->id, 'time_out' => now()]);
        $this->otherLogin = AccountLogin::factory()->create(['user_id' => $owner->id, 'account_id' => $this->otherAccount->id, 'time_out' => now()]);
    }

    /**
     * @param array<int, string> $permissions
     */
    private function actingAsUser(array $permissions, bool $withAccount = true): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['module' => 'Test', 'description' => $permission]);
        }

        $user = User::factory()->create(['status' => 'active', 'firstname' => 'Ana', 'lastname' => 'Reyes']);
        $user->givePermissionTo($permissions);

        if ($withAccount) {
            $user->accounts()->attach($this->account->id);
        }

        $this->actingAs($user);

        return $user;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function order(AccountLogin $login, array $attributes = []): SalesOrder
    {
        return SalesOrder::factory()->create($attributes + [
            'account_login_id' => $login->id,
            'status'           => 'finalized',
            'upload_status'    => 1,
            'reference'        => fake()->unique()->numerify('26####'),
            'order_date'       => now()->toDateString(),
            'total_quantity'   => 10,
            'total_sales'      => 1000,
            'grand_total'      => 900,
        ]);
    }

    private function logs(): \Illuminate\Support\Collection
    {
        return AiUsageLog::query()->where('user_id', auth()->id())->orderBy('id')->get();
    }

    // -------------------------------------------------------------------------
    // Sales assistant
    // -------------------------------------------------------------------------

    public function test_the_assistant_page_needs_its_permission(): void
    {
        $this->actingAsUser([]);
        $this->get(route('ai-assistant.index'))->assertForbidden();

        $this->actingAsUser(['ai assistant access']);
        $this->get(route('ai-assistant.index'))->assertOk()->assertSeeLivewire('ai-assistant.chat');
    }

    public function test_a_question_is_answered_and_logged(): void
    {
        $user = $this->actingAsUser(['ai assistant access']);
        SalesAssistant::fake(['Your accounts ordered **PHP 900.00** this month.']);

        Livewire::test(Chat::class)
            ->set('question', 'How much did we order this month?')
            ->call('ask')
            ->assertSet('error', '')
            ->assertSet('question', '')
            ->assertSee('PHP 900.00');

        SalesAssistant::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->prompt === 'How much did we order this month?');

        $log = $this->logs()->sole();
        $this->assertSame(AiUsageLog::FEATURE_ASSISTANT, $log->feature);
        $this->assertSame(AiUsageLog::STATUS_SUCCESS, $log->status);
        $this->assertSame('gemini', $log->provider);
        $this->assertSame($user->id, $log->user_id);

        $this->assertCount(2, Session::get('ai_assistant.history'), 'The question and the answer are kept for follow-ups.');
    }

    public function test_follow_up_questions_send_the_earlier_turns(): void
    {
        $user = $this->actingAsUser(['ai assistant access']);
        Session::put('ai_assistant.history', [
            ['role' => 'user', 'content' => 'Top products?'],
            ['role' => 'assistant', 'content' => 'Soap and shampoo.'],
        ]);

        $agent = new SalesAssistant($user, [$this->account->id], Session::get('ai_assistant.history'));

        $this->assertCount(2, iterator_to_array($agent->messages()));
    }

    public function test_a_user_without_accounts_is_told_so_without_calling_ai(): void
    {
        $this->actingAsUser(['ai assistant access'], withAccount: false);
        SalesAssistant::fake()->preventStrayPrompts();

        Livewire::test(Chat::class)
            ->set('question', 'How are sales?')
            ->call('ask')
            ->assertSet('error', 'No accounts are assigned to you yet, so there are no sales orders to ask about.');

        SalesAssistant::assertNeverPrompted();
    }

    public function test_the_daily_token_limit_stops_the_assistant(): void
    {
        $user = $this->actingAsUser(['ai assistant access']);
        Setting::query()->orderBy('id')->first()->update(['ai_daily_user_token_limit' => 100]);
        AiUsageLog::factory()->create(['user_id' => $user->id, 'total_tokens' => 150]);
        SalesAssistant::fake()->preventStrayPrompts();

        Livewire::test(Chat::class)
            ->set('question', 'How are sales?')
            ->call('ask')
            ->assertSet('error', fn (string $error): bool => str_contains($error, 'AI token limit for today'));

        SalesAssistant::assertNeverPrompted();
    }

    public function test_the_tools_only_read_the_users_own_accounts(): void
    {
        $this->order($this->login, ['control_number' => 'SO-MINE-1', 'grand_total' => 900]);
        $this->order($this->otherLogin, ['control_number' => 'SO-THEIRS-1', 'grand_total' => 5000]);
        $figures = app(SalesFigures::class);

        $summary = json_decode((new SalesSummary($figures, [$this->account->id]))->handle(new ToolRequest([])), true);
        $this->assertSame(1, $summary['totals']['orders']);
        $this->assertSame(900.0, (float) $summary['totals']['net_sales']);

        $other = json_decode((new SalesSummary($figures, [$this->account->id]))->handle(new ToolRequest(['account_code' => 'AIT999'])), true);
        $this->assertSame(0, $other['totals']['orders'], 'An account that is not the user\'s gives nothing.');

        $detail = json_decode((new SalesOrderDetail($figures, [$this->account->id]))->handle(new ToolRequest(['number' => 'SO-THEIRS-1'])), true);
        $this->assertArrayHasKey('error', $detail);

        $mine = json_decode((new SalesOrderDetail($figures, [$this->account->id]))->handle(new ToolRequest(['number' => 'SO-MINE-1'])), true);
        $this->assertSame('SO-MINE-1', $mine['control_number']);
    }

    public function test_the_summary_counts_only_finalized_uploaded_orders_but_lists_every_status(): void
    {
        $this->order($this->login, ['grand_total' => 900]);
        $this->order($this->login, ['status' => 'draft', 'upload_status' => null, 'grand_total' => 400]);
        $this->order($this->login, ['status' => 'finalized', 'upload_status' => 0, 'grand_total' => 300]);

        $summary = json_decode((new SalesSummary(app(SalesFigures::class), [$this->account->id]))->handle(new ToolRequest([])), true);

        $this->assertSame(1, $summary['totals']['orders']);
        $this->assertSame(900.0, (float) $summary['totals']['net_sales']);
        $this->assertSame(['draft' => 1, 'finalized' => 2], collect($summary['by_status'])->pluck('orders', 'status')->all());
    }

    // -------------------------------------------------------------------------
    // Report insights
    // -------------------------------------------------------------------------

    public function test_insights_explain_the_report_month_and_reuse_the_answer(): void
    {
        $this->actingAsUser(['ai insights access']);
        $this->order($this->login, ['order_date' => '2026-09-10', 'grand_total' => 900]);
        ReportInsights::fake([['headline' => 'September net sales reached PHP 900.', 'highlights' => ['AI TEST placed every order.'], 'watch' => []]]);

        $component = Livewire::test(Insights::class, ['source' => 'so-report', 'year' => '2026', 'month' => '09'])
            ->call('explain')
            ->assertSet('error', '')
            ->assertSee('September net sales reached PHP 900.')
            ->assertSee('AI TEST placed every order.');

        ReportInsights::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, '"period": "September 2026"')
            && str_contains($prompt->prompt, '"orders": 1')
            && str_contains($prompt->prompt, 'August 2026'));

        $component->call('dismiss')->call('explain')->assertSee('September net sales reached PHP 900.');

        ReportInsights::assertPromptedTimes(1);
        $this->assertSame([AiUsageLog::STATUS_SUCCESS, AiUsageLog::STATUS_CACHED], $this->logs()->pluck('status')->all());
        $this->assertSame(AiUsageLog::FEATURE_INSIGHTS, $this->logs()->first()->feature);
    }

    public function test_insights_follow_the_days_picked_on_the_dashboard(): void
    {
        $this->actingAsUser(['ai insights access']);
        $this->order($this->login, ['order_date' => '2026-10-05']);
        $this->order($this->login, ['order_date' => '2026-10-06']);
        ReportInsights::fake([['headline' => 'Two days of orders.', 'highlights' => [], 'watch' => []]]);

        Livewire::test(Insights::class, ['source' => 'so-dashboard'])
            ->call('setDate', 2026, 10, [2026 => [10 => [5]]])
            ->call('explain')
            ->assertSee('Two days of orders.');

        ReportInsights::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, '"period": "Oct 5, 2026"')
            && str_contains($prompt->prompt, '"orders": 1'));
    }

    public function test_a_period_without_orders_is_not_sent_to_ai(): void
    {
        $this->actingAsUser(['ai insights access']);
        ReportInsights::fake()->preventStrayPrompts();

        Livewire::test(Insights::class, ['source' => 'so-report', 'year' => '2001', 'month' => '01'])
            ->call('explain')
            ->assertSet('error', 'There are no finalized, uploaded orders in January 2001 to explain.');

        ReportInsights::assertNeverPrompted();
    }

    public function test_insights_need_their_permission(): void
    {
        $this->actingAsUser([]);

        Livewire::test(Insights::class, ['source' => 'so-report', 'year' => '2026', 'month' => '09'])
            ->call('explain')
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Upload mapping help
    // -------------------------------------------------------------------------

    private function unmatchedRows(): void
    {
        Session::put('so_template_rows', [[
            'po_number'           => 'PO-AI-1',
            'raw_sku'             => '77001',
            'sku_code'            => '77001',
            'description'         => 'LAVENDER SOAP 90G',
            'store_code'          => '',
            'store_name'          => '',
            'product_id'          => null,
            'shipping_address_id' => 55,
            'account_id'          => $this->account->id,
            'account_code'        => 'AIT001',
            'lookup_status'       => 'sku_not_found',
        ]]);
    }

    public function test_mapping_help_suggests_only_real_candidates_and_adds_the_mapping(): void
    {
        $this->actingAsUser(['ai mapping help']);
        $soap = Product::factory()->create(['stock_code' => 'BV-SOAP-90', 'description' => 'LAVENDER SOAP', 'size' => '90G', 'brand' => 'GLOW']);
        $this->unmatchedRows();
        MappingHelper::fake([[
            'products' => [
                ['item' => 1, 'stock_code' => 'BV-SOAP-90', 'confidence' => 'high', 'reason' => 'Same scent and 90 g size.'],
                ['item' => 2, 'stock_code' => 'INVENTED-1', 'confidence' => 'high', 'reason' => 'Not a candidate.'],
            ],
            'stores' => [],
        ]]);

        $component = Livewire::test(MappingHelp::class, ['accountIds' => [$this->account->id]])
            ->call('suggest')
            ->assertSet('error', '')
            ->assertSee('BV-SOAP-90')
            ->assertSee('Same scent and 90 g size.');

        $this->assertCount(1, $component->get('suggestions')['products']);
        MappingHelper::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'LAVENDER SOAP 90G') && str_contains($prompt->prompt, 'BV-SOAP-90'));

        $component->call('addProduct', 0)->assertDispatched('templateRowsChanged');

        $this->assertDatabaseHas('account_product_references', [
            'account_id'        => $this->account->id,
            'product_id'        => $soap->id,
            'account_reference' => '77001',
        ]);
        $this->assertSame('ok', Session::get('so_template_rows')[0]['lookup_status']);
        $this->assertSame('BV-SOAP-90', Session::get('so_template_rows')[0]['internal_sku']);
        $this->assertSame(AiUsageLog::FEATURE_MAPPING, $this->logs()->sole()->feature);
    }

    public function test_mapping_help_cannot_add_mappings_for_another_users_account(): void
    {
        $this->actingAsUser(['ai mapping help'], withAccount: false);
        Product::factory()->create(['stock_code' => 'BV-SOAP-90', 'description' => 'LAVENDER SOAP', 'size' => '90G']);
        $this->unmatchedRows();
        MappingHelper::fake()->preventStrayPrompts();

        Livewire::test(MappingHelp::class, ['accountIds' => [$this->account->id]])
            ->call('suggest')
            ->assertSet('suggestions', null);

        MappingHelper::assertNeverPrompted();
        $this->assertSame(0, AccountProductReference::where('account_reference', '77001')->count());
    }

    public function test_the_usage_page_breaks_calls_down_by_feature(): void
    {
        $this->actingAsUser(['ai usage access']);
        AiUsageLog::query()->delete();
        AiUsageLog::factory()->create(['feature' => AiUsageLog::FEATURE_ASSISTANT, 'total_tokens' => 400]);
        AiUsageLog::factory()->create(['feature' => AiUsageLog::FEATURE_MAPPING, 'total_tokens' => 250]);

        Livewire::test(\App\Http\Livewire\AiUsage\Index::class)
            ->assertSee('By feature')
            ->assertSee('Sales assistant')
            ->assertSee('Upload mapping help');
    }

    public function test_mapping_help_needs_its_permission(): void
    {
        $this->actingAsUser([]);
        $this->unmatchedRows();

        Livewire::test(MappingHelp::class, ['accountIds' => [$this->account->id]])
            ->call('suggest')
            ->assertForbidden();
    }
}
