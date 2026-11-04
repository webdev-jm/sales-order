<?php

namespace Tests\Feature;

use App\Http\Livewire\Ai\Insights;
use App\Http\Livewire\Ai\MappingHelp;
use App\Http\Livewire\AiAssistant\Chat;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\AccountProductReference;
use App\Models\AiProviderSetting;
use App\Models\AiUsageLog;
use App\Models\Company;
use App\Models\PriceCode;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\Setting;
use App\Models\User;
use App\Services\Ai\Agents\MappingHelper;
use App\Services\Ai\Agents\ReportInsights;
use App\Services\Ai\Agents\SalesAssistant;
use App\Services\Ai\AiResponse;
use App\Services\Ai\AiUsageMeter;
use App\Services\Ai\Providers\AnthropicProvider;
use App\Services\Ai\SalesFigures;
use App\Services\Ai\Tools\SalesOrderDetail;
use App\Services\Ai\Tools\SalesSummary;
use App\Services\Ai\Tools\SalesTool;
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

    /**
     * Runs $ask while catching what Livewire streams to the page, and returns the streamed text.
     */
    private function streamedText(callable $ask): string
    {
        $output = '';
        ob_start(function (string $chunk) use (&$output): string {
            $output .= $chunk;

            return '';
        });

        try {
            $ask();
        } finally {
            ob_end_clean();
        }

        preg_match_all('/\{"stream":true,"body":(\{.*?\}),"endStream":true\}/', $output, $matches);

        return collect($matches[1])->map(fn (string $body) => json_decode($body, true))
            ->filter(fn (?array $body): bool => ($body['name'] ?? null) === 'answer')
            ->pluck('content')
            ->join('');
    }

    public function test_a_question_is_answered_and_logged(): void
    {
        $user = $this->actingAsUser(['ai assistant access']);
        SalesAssistant::fake(['Your accounts ordered **PHP 900.00** this month.']);

        $streamed = $this->streamedText(fn () => Livewire::test(Chat::class)
            ->set('question', 'How much did we order this month?')
            ->call('ask')
            ->assertSet('error', '')
            ->assertSet('question', '')
            ->assertSee('PHP 900.00'));

        $this->assertSame('Your accounts ordered **PHP 900.00** this month.', $streamed, 'The answer is streamed to the page as it is written.');

        SalesAssistant::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->prompt === 'How much did we order this month?');

        $log = $this->logs()->sole();
        $this->assertSame(AiUsageLog::FEATURE_ASSISTANT, $log->feature);
        $this->assertSame(AiUsageLog::STATUS_SUCCESS, $log->status);
        $this->assertSame('gemini', $log->provider);
        $this->assertSame($user->id, $log->user_id);

        $this->assertCount(2, Session::get('ai_assistant.history'), 'The question and the answer are kept for follow-ups.');
    }

    public function test_answers_list_the_figures_they_used(): void
    {
        $this->actingAsUser(['ai assistant access']);
        Session::put('ai_assistant.history', [
            ['role' => 'user', 'content' => 'Sales this month?'],
            ['role' => 'assistant', 'content' => 'PHP 900.00.', 'sources' => ['Sales summary · Nov 1–4, 2026 · all your accounts']],
        ]);

        Livewire::test(Chat::class)
            ->assertSee('Figures used:')
            ->assertSee('Sales summary · Nov 1–4, 2026 · all your accounts');
    }

    public function test_tool_calls_are_described_in_words_a_clerk_can_check(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-04 10:00'));

        $this->assertSame('Sales summary · Nov 1–4, 2026 · all your accounts', SalesTool::describeCall('SalesSummary', []));
        $this->assertSame('Top products · Oct 1–31, 2026 · account AIT001', SalesTool::describeCall('TopProducts', ['date_from' => '2026-10-01', 'date_to' => '2026-10-31', 'account_code' => 'AIT001']));
        $this->assertSame('Sales by brand · Sep 15, 2026 – Nov 4, 2026 · all your accounts', SalesTool::describeCall('SalesByBrand', ['date_from' => '2026-09-15']));
        $this->assertSame('Order search · PO 4500123 · all your accounts', SalesTool::describeCall('FindSalesOrders', ['po_number' => '4500123']));
        $this->assertSame('Order details · SO-20261104-001', SalesTool::describeCall('SalesOrderDetail', ['number' => 'SO-20261104-001']));
        $this->assertSame('Your accounts', SalesTool::describeCall('ListMyAccounts', []));
    }

    public function test_only_the_last_turns_are_kept_in_the_session(): void
    {
        $this->actingAsUser(['ai assistant access']);
        Session::put('ai_assistant.history', array_map(fn (int $i): array => ['role' => $i % 2 ? 'assistant' : 'user', 'content' => 'Turn ' . $i], range(0, 19)));
        SalesAssistant::fake(['Newest answer.']);

        $this->streamedText(fn () => Livewire::test(Chat::class)->set('question', 'Newest question?')->call('ask'));

        $history = Session::get('ai_assistant.history');
        $this->assertCount(20, $history);
        $this->assertSame('Turn 2', $history[0]['content']);
        $this->assertSame('Newest answer.', end($history)['content']);
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

        ReportInsights::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, '"period":"September 2026"')
            && str_contains($prompt->prompt, '"orders":1')
            && str_contains($prompt->prompt, 'August 2026'));

        $component->call('dismiss')->call('explain')->assertSee('September net sales reached PHP 900.');

        ReportInsights::assertPromptedTimes(1);
        $this->assertSame([AiUsageLog::STATUS_SUCCESS, AiUsageLog::STATUS_CACHED], $this->logs()->pluck('status')->all());
        $this->assertSame(AiUsageLog::FEATURE_INSIGHTS, $this->logs()->first()->feature);
    }

    public function test_insights_get_changes_and_shares_worked_out_and_are_told_not_to_calculate(): void
    {
        $this->actingAsUser(['ai insights access']);
        $this->order($this->login, ['order_date' => '2026-09-10', 'grand_total' => 900, 'total_sales' => 1000]);
        $this->order($this->login, ['order_date' => '2026-08-10', 'grand_total' => 600, 'total_sales' => 800]);
        ReportInsights::fake([['headline' => 'Up.', 'highlights' => ['Up.'], 'watch' => []]]);

        Livewire::test(Insights::class, ['source' => 'so-report', 'year' => '2026', 'month' => '09'])->call('explain');

        ReportInsights::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, '"change_pct":{"orders":0,"quantity":0,"gross_sales":25,"net_sales":50}')
            && str_contains($prompt->prompt, '"net_sales":900,"share_pct":100'));
        $this->assertStringContainsString('Never calculate a percentage', (new ReportInsights)->instructions());
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

        ReportInsights::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, '"period":"Oct 5, 2026"')
            && str_contains($prompt->prompt, '"orders":1'));
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
        $this->priceFor($soap);
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

    private function priceFor(Product $product, ?int $companyId = null): void
    {
        PriceCode::factory()->create([
            'company_id'    => $companyId ?? $this->account->company_id,
            'product_id'    => $product->id,
            'code'          => 'A',
            'selling_price' => 100,
            'price_basis'   => 'S',
        ]);
    }

    public function test_mapping_help_finds_the_best_candidate_even_when_many_products_share_a_word(): void
    {
        $this->actingAsUser(['ai mapping help']);
        foreach (range(1, 90) as $i) {
            $this->priceFor(Product::factory()->create(['stock_code' => 'BV-OTHER-' . $i, 'description' => 'SOAP BAR ' . $i, 'size' => '135G', 'brand' => 'OTHER']));
        }
        $this->priceFor(Product::factory()->create(['stock_code' => 'BV-SOAP-90', 'description' => 'LAVENDER SOAP', 'size' => '90G', 'brand' => 'GLOW']));
        $this->unmatchedRows();
        MappingHelper::fake([['products' => [], 'stores' => []]]);

        Livewire::test(MappingHelp::class, ['accountIds' => [$this->account->id]])->call('suggest');

        MappingHelper::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'BV-SOAP-90'));
    }

    public function test_mapping_help_only_offers_products_priced_in_the_accounts_company(): void
    {
        $this->actingAsUser(['ai mapping help']);
        $this->priceFor(Product::factory()->create(['stock_code' => 'BV-SOAP-OURS', 'description' => 'LAVENDER SOAP', 'size' => '90G']));
        $this->priceFor(Product::factory()->create(['stock_code' => 'BV-SOAP-THEIRS', 'description' => 'LAVENDER SOAP', 'size' => '90G']), Company::factory()->create()->id);
        Product::factory()->create(['stock_code' => 'BV-SOAP-NOPRICE', 'description' => 'LAVENDER SOAP', 'size' => '90G']);
        $this->unmatchedRows();
        MappingHelper::fake([['products' => [], 'stores' => []]]);

        Livewire::test(MappingHelp::class, ['accountIds' => [$this->account->id]])->call('suggest');

        MappingHelper::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'BV-SOAP-OURS')
            && !str_contains($prompt->prompt, 'BV-SOAP-THEIRS')
            && !str_contains($prompt->prompt, 'BV-SOAP-NOPRICE'));
    }

    public function test_mapping_help_says_how_many_lines_wait_and_moves_on_to_them_next(): void
    {
        $this->actingAsUser(['ai mapping help']);
        $this->priceFor(Product::factory()->create(['stock_code' => 'BV-SOAP-90', 'description' => 'LAVENDER SOAP', 'size' => '90G']));
        Session::put('so_template_rows', array_map(fn (int $i): array => [
            'po_number' => 'PO-AI-1', 'raw_sku' => 'SKU-' . $i, 'sku_code' => 'SKU-' . $i, 'description' => 'LAVENDER SOAP 90G',
            'store_code' => '', 'store_name' => '', 'product_id' => null, 'shipping_address_id' => 55,
            'account_id' => $this->account->id, 'account_code' => 'AIT001', 'lookup_status' => 'sku_not_found',
        ], range(1, 17)));
        MappingHelper::fake([['products' => [], 'stores' => []], ['products' => [], 'stores' => []]]);

        $component = Livewire::test(MappingHelp::class, ['accountIds' => [$this->account->id]])
            ->call('suggest')
            ->assertSee('2 more unmatched lines were left for the next search')
            ->assertSee('Suggest matches for the next lines');

        $component->call('suggest')->assertSet('leftOut', 0);

        MappingHelper::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, '"SKU-15"') && !str_contains($prompt->prompt, '"SKU-16"'));
        MappingHelper::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, '"SKU-16"') && str_contains($prompt->prompt, '"SKU-17"') && !str_contains($prompt->prompt, '"SKU-1"'));

        $component->call('suggest')->assertSee('Every unmatched line has been checked already.')->assertSee('Check every line again');
        MappingHelper::assertPromptedTimes(2);
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
    // -------------------------------------------------------------------------
    // Token efficiency: provider prompt caching and reused replies
    // -------------------------------------------------------------------------

    public function test_the_same_figures_are_explained_once_for_everyone_who_sees_them(): void
    {
        $this->order($this->login, ['order_date' => '2026-09-10', 'grand_total' => 900]);
        ReportInsights::fake([['headline' => 'September net sales reached PHP 900.', 'highlights' => [], 'watch' => []]]);

        $this->actingAsUser(['ai insights access']);
        Livewire::test(Insights::class, ['source' => 'so-report', 'year' => '2026', 'month' => '09'])->call('explain');

        $colleague = $this->actingAsUser(['ai insights access']);
        Livewire::test(Insights::class, ['source' => 'so-report', 'year' => '2026', 'month' => '09'])
            ->call('explain')
            ->assertSee('September net sales reached PHP 900.');

        ReportInsights::assertPromptedTimes(1);
        $this->assertSame(AiUsageLog::STATUS_CACHED, AiUsageLog::where('user_id', $colleague->id)->sole()->status);
    }

    public function test_insight_figures_are_sent_as_compact_json(): void
    {
        $this->actingAsUser(['ai insights access']);
        $this->order($this->login, ['order_date' => '2026-09-10']);
        ReportInsights::fake([['headline' => 'Fine.', 'highlights' => [], 'watch' => []]]);

        Livewire::test(Insights::class, ['source' => 'so-report', 'year' => '2026', 'month' => '09'])->call('explain');

        ReportInsights::assertPrompted(fn (AgentPrompt $prompt): bool => !str_contains($prompt->prompt, "\n    ") && str_contains($prompt->prompt, '{"report"'));
    }

    public function test_mapping_suggestions_for_the_same_lines_are_reused(): void
    {
        $this->actingAsUser(['ai mapping help']);
        $this->priceFor(Product::factory()->create(['stock_code' => 'BV-SOAP-90', 'description' => 'LAVENDER SOAP', 'size' => '90G', 'brand' => 'GLOW']));
        $this->unmatchedRows();
        MappingHelper::fake([['products' => [['item' => 1, 'stock_code' => 'BV-SOAP-90', 'confidence' => 'high', 'reason' => 'Same soap.']], 'stores' => []]]);

        Livewire::test(MappingHelp::class, ['accountIds' => [$this->account->id]])->call('suggest')->assertSee('BV-SOAP-90');
        Livewire::test(MappingHelp::class, ['accountIds' => [$this->account->id]])->call('suggest')->assertSee('Same soap.');

        MappingHelper::assertPromptedTimes(1);
        $this->assertSame([AiUsageLog::STATUS_SUCCESS, AiUsageLog::STATUS_CACHED], $this->logs()->pluck('status')->all());
    }

    public function test_cached_input_tokens_are_logged_and_priced_at_the_cache_rate(): void
    {
        $this->actingAsUser([]);
        AiProviderSetting::create(['name' => 'gemini', 'is_enabled' => true, 'priority' => 1, 'model' => 'gemini-test', 'input_price' => 1.0, 'output_price' => 10.0]);
        Config::set('ai.providers.gemini.cache_read_price', 0.1);

        $log = app(AiUsageMeter::class)->record(
            ['user_id' => auth()->id(), 'feature' => AiUsageLog::FEATURE_ASSISTANT, 'status' => AiUsageLog::STATUS_SUCCESS],
            new AiResponse('ok', 'gemini', 'gemini-test', 10_000, 1_000, 11_000, 50, cachedInputTokens: 8_000)
        );

        $this->assertSame(8_000, $log->fresh()->cached_input_tokens);
        // 2,000 fresh input at $1/M + 8,000 cached at 10% of that + 1,000 output at $10/M.
        $this->assertEqualsWithDelta((2_000 * 1.0 + 8_000 * 0.1 + 1_000 * 10.0) / 1_000_000, (float) $log->estimated_cost, 0.0000001);
    }

    public function test_anthropic_cache_writes_cost_more_and_reads_less(): void
    {
        Config::set('ai.providers.anthropic.cache_read_price', 0.1);
        Config::set('ai.providers.anthropic.cache_write_price', 1.25);
        $setting = new AiProviderSetting(['name' => 'anthropic', 'input_price' => 1.0, 'output_price' => 5.0]);

        $this->assertEqualsWithDelta((1_000 + 2_000 * 0.1 + 1_000 * 1.25) / 1_000_000, $setting->costOf(4_000, 0, 2_000, 1_000), 0.0000001);
        $this->assertEqualsWithDelta(4_000 / 1_000_000, $setting->costOf(4_000, 0), 0.0000001);
    }

    public function test_claude_calls_ask_for_automatic_prompt_caching(): void
    {
        $options = (new AnthropicProvider(['key' => 'test', 'effort' => 'low']))->laravelAiOptions();

        $this->assertSame(['type' => 'ephemeral'], $options['cache_control']);
        $this->assertSame(['effort' => 'low'], $options['output_config']);
        $this->assertSame(['type' => 'ephemeral'], (new AnthropicProvider(['key' => 'test']))->laravelAiOptions()['cache_control']);
    }

    public function test_the_assistant_keeps_the_changing_parts_of_its_instructions_last(): void
    {
        $ana = User::factory()->make(['firstname' => 'Ana', 'middlename' => null, 'lastname' => 'Reyes']);
        $ben = User::factory()->make(['firstname' => 'Ben', 'middlename' => null, 'lastname' => 'Cruz']);

        $first = (new SalesAssistant($ana, [1]))->instructions();
        $this->travel(1)->days();
        $second = (new SalesAssistant($ben, [2]))->instructions();

        $sharedLines = fn (string $text): string => implode("\n", array_slice(explode("\n", $text), 0, -1));
        $this->assertSame($sharedLines($first), $sharedLines($second), 'Everything but the last line is the same for every user and day.');
        $this->assertStringContainsString('Ana Reyes', last(explode("\n", $first)));
        $this->assertStringNotContainsString('Ana Reyes', $sharedLines($first));

        $attributes = array_map(fn ($a) => $a->getName(), (new \ReflectionClass(SalesAssistant::class))->getAttributes());
        $this->assertContains(\Laravel\Ai\Attributes\CacheToolDefinitions::class, $attributes);
    }
}
