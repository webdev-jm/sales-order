<?php

namespace Tests\Feature;

use App\Http\Livewire\AiUsage\Index;
use App\Models\Account;
use App\Models\AiUsageLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

class AiUsageTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seedSettings();
        Setting::query()->orderBy('id')->first()->update(['ai_monthly_token_limit' => null]);

        // Other tests' rows are rolled back, but committed rows from earlier runs would skew the totals.
        AiUsageLog::query()->delete();
    }

    /**
     * @param array<int, string> $permissions
     */
    private function actingAsUser(array $permissions = ['ai usage access']): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['module' => 'Test', 'description' => $permission]);
        }

        $user = User::factory()->create(['status' => 'active', 'firstname' => 'Ana', 'lastname' => 'Reyes']);
        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }

    public function test_the_page_needs_the_usage_permission(): void
    {
        $this->actingAsUser([]);

        $this->get(route('ai-usage.index'))->assertForbidden();
    }

    public function test_the_page_opens_with_the_usage_permission(): void
    {
        $this->actingAsUser();

        $this->get(route('ai-usage.index'))->assertOk()->assertSeeLivewire('ai-usage.index');
    }

    public function test_it_totals_the_selected_month_by_user_and_account(): void
    {
        $user = $this->actingAsUser();
        $account = Account::factory()->create(['account_code' => 'PG001', 'account_name' => 'PUREGOLD']);

        AiUsageLog::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'input_tokens' => 900, 'output_tokens' => 100, 'total_tokens' => 1000]);
        AiUsageLog::factory()->failed()->create(['user_id' => $user->id, 'account_id' => $account->id, 'input_tokens' => 400, 'output_tokens' => 100, 'total_tokens' => 500]);
        AiUsageLog::factory()->create(['total_tokens' => 99999, 'created_at' => now()->subMonthNoOverflow()]);

        Livewire::test(Index::class)
            ->assertSet('month', now()->format('Y-m'))
            ->assertViewHas('totals', fn ($totals) => (int) $totals->calls === 2
                && (int) $totals->failed === 1
                && (int) $totals->input_tokens === 1300
                && (int) $totals->output_tokens === 200
                && (int) $totals->total_tokens === 1500)
            ->assertSee('1,500')
            ->assertSee('Ana Reyes')
            ->assertSee('[PG001] PUREGOLD')
            ->assertSee('No monthly limit set')
            ->assertDontSee('99,999');
    }

    public function test_an_earlier_month_can_be_shown(): void
    {
        $this->actingAsUser();
        AiUsageLog::factory()->create(['total_tokens' => 4321, 'created_at' => now()->subMonthNoOverflow()]);

        Livewire::test(Index::class)
            ->set('month', now()->subMonthNoOverflow()->format('Y-m'))
            ->assertViewHas('totals', fn ($totals) => (int) $totals->total_tokens === 4321)
            ->assertSee(now()->subMonthNoOverflow()->format('F Y'));
    }

    public function test_a_malformed_month_falls_back_to_this_month(): void
    {
        $this->actingAsUser();

        Livewire::test(Index::class)
            ->set('month', 'not-a-month')
            ->assertSee(now()->format('F Y'));
    }

    public function test_usage_is_broken_down_by_provider_and_model(): void
    {
        $this->actingAsUser();
        AiUsageLog::factory()->failed()->create(['provider' => 'gemini', 'model' => 'gemini-3.5-flash', 'total_tokens' => 0]);
        AiUsageLog::factory()->create(['provider' => 'anthropic', 'model' => 'claude-haiku-5-5', 'total_tokens' => 1234]);

        Livewire::test(Index::class)
            ->assertViewHas('byProvider', fn ($rows) => $rows->count() === 2
                && $rows->firstWhere('provider', 'anthropic')->total_tokens == 1234
                && (int) $rows->firstWhere('provider', 'gemini')->failed === 1)
            ->assertSee('claude-haiku-5-5')
            ->assertSee('(1 failed)');
    }

    public function test_calls_can_be_filtered_to_failures(): void
    {
        $this->actingAsUser();
        AiUsageLog::factory()->create(['reference' => 'good-po.pdf']);
        AiUsageLog::factory()->failed()->create(['reference' => 'bad-po.pdf']);

        Livewire::test(Index::class)
            ->set('status', AiUsageLog::STATUS_FAILED)
            ->assertSee('bad-po.pdf')
            ->assertDontSee('good-po.pdf');
    }

    public function test_it_shows_progress_against_the_limit_and_warns_when_it_is_used_up(): void
    {
        $this->actingAsUser();
        Setting::query()->orderBy('id')->first()->update(['ai_monthly_token_limit' => 2000]);
        AiUsageLog::factory()->create(['total_tokens' => 2000]);

        Livewire::test(Index::class)
            ->assertSee('100% of the 2,000 monthly limit')
            ->assertSee('PDF scanning is paused');
    }

    public function test_the_monthly_limit_is_saved_from_settings(): void
    {
        $this->actingAsUser(['settings', 'settings access']);
        $setting = Setting::query()->orderBy('id')->first();

        $this->post(route('setting.update', $setting->id), [
            'data_per_page'          => $setting->data_per_page,
            'sales_order_limit'      => $setting->sales_order_limit,
            'mcp_deadline'           => $setting->mcp_deadline,
            'ai_monthly_token_limit' => 5000000,
        ])->assertSessionHasNoErrors();

        $this->assertSame(5000000, (int) $setting->fresh()->ai_monthly_token_limit);

        $this->post(route('setting.update', $setting->id), [
            'data_per_page'          => $setting->data_per_page,
            'sales_order_limit'      => $setting->sales_order_limit,
            'mcp_deadline'           => $setting->mcp_deadline,
            'ai_monthly_token_limit' => '',
        ])->assertSessionHasNoErrors();

        $this->assertNull($setting->fresh()->ai_monthly_token_limit);
    }

    public function test_the_monthly_limit_must_be_a_positive_whole_number(): void
    {
        $this->actingAsUser(['settings', 'settings access']);
        $setting = Setting::query()->orderBy('id')->first();

        $this->post(route('setting.update', $setting->id), [
            'data_per_page'          => $setting->data_per_page,
            'sales_order_limit'      => $setting->sales_order_limit,
            'mcp_deadline'           => $setting->mcp_deadline,
            'ai_monthly_token_limit' => -5,
        ])->assertSessionHasErrors('ai_monthly_token_limit');
    }

    public function test_each_users_tokens_cost_and_models_are_shown(): void
    {
        $ana = $this->actingAsUser();
        $ben = User::factory()->create(['status' => 'active', 'firstname' => 'Ben', 'lastname' => 'Cruz']);

        AiUsageLog::factory()->create(['user_id' => $ana->id, 'provider' => 'gemini', 'model' => 'gemini-3.5-flash', 'input_tokens' => 800, 'output_tokens' => 200, 'total_tokens' => 1000, 'estimated_cost' => 0.01]);
        AiUsageLog::factory()->create(['user_id' => $ana->id, 'provider' => 'anthropic', 'model' => 'claude-haiku-5-5', 'input_tokens' => 2500, 'output_tokens' => 500, 'total_tokens' => 3000, 'estimated_cost' => 0.02]);
        AiUsageLog::factory()->failed()->create(['user_id' => $ana->id, 'provider' => 'gemini', 'model' => 'gemini-3.5-flash', 'input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0]);
        AiUsageLog::factory()->create(['user_id' => $ana->id, 'status' => AiUsageLog::STATUS_CACHED, 'input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0]);
        AiUsageLog::factory()->create(['user_id' => $ben->id, 'provider' => 'gemini', 'model' => 'gemini-3.5-flash', 'input_tokens' => 900, 'output_tokens' => 100, 'total_tokens' => 1000, 'estimated_cost' => null]);
        AiUsageLog::factory()->create(['user_id' => $ben->id, 'total_tokens' => 7777, 'created_at' => now()->subMonthNoOverflow()]);

        Livewire::test(Index::class)
            ->assertViewHas('byUser', function ($rows) use ($ana, $ben): bool {
                $anaRow = $rows->firstWhere('user_id', $ana->id);
                $benRow = $rows->firstWhere('user_id', $ben->id);

                return $rows->first()->user_id === $ana->id
                    && (int) $anaRow->calls === 3
                    && (int) $anaRow->failed === 1
                    && (int) $anaRow->cached_scans === 1
                    && (int) $anaRow->input_tokens === 3300
                    && (int) $anaRow->output_tokens === 700
                    && (int) $anaRow->total_tokens === 4000
                    && abs((float) $anaRow->estimated_cost - 0.03) < 0.000001
                    && $anaRow->models->pluck('model')->all() === ['claude-haiku-5-5', 'gemini-3.5-flash']
                    && (int) $anaRow->models->firstWhere('model', 'gemini-3.5-flash')->calls === 2
                    && (int) $benRow->total_tokens === 1000
                    && $benRow->estimated_cost === null
                    && $benRow->models->pluck('model')->all() === ['gemini-3.5-flash'];
            })
            ->assertSee('Token usage per user')
            ->assertSee('Ben Cruz')
            ->assertSee('anthropic · claude-haiku-5-5')
            ->assertSee('80%')
            ->assertSee('+1 saved scans')
            ->assertDontSee('7,777');
    }

    public function test_each_users_tokens_today_are_shown_against_the_daily_limit(): void
    {
        $user = $this->actingAsUser();
        Setting::query()->orderBy('id')->first()->update(['ai_daily_user_token_limit' => 10000]);
        AiUsageLog::factory()->create(['user_id' => $user->id, 'total_tokens' => 2500]);
        AiUsageLog::factory()->create(['user_id' => $user->id, 'total_tokens' => 4000, 'created_at' => now()->subDay()]);

        Livewire::test(Index::class)
            ->assertViewHas('byUser', fn ($rows) => $rows->first()->tokens_today === 2500)
            ->assertSee('Each user may use 10,000 tokens a day.')
            ->assertSee('/ 10,000');
    }

    public function test_clicking_a_user_shows_only_their_calls(): void
    {
        $ana = $this->actingAsUser();
        $ben = User::factory()->create(['status' => 'active', 'firstname' => 'Ben', 'lastname' => 'Cruz']);
        AiUsageLog::factory()->create(['user_id' => $ana->id, 'reference' => 'ana-po.pdf']);
        AiUsageLog::factory()->create(['user_id' => $ben->id, 'reference' => 'ben-po.pdf']);
        AiUsageLog::factory()->create(['user_id' => null, 'reference' => 'system-po.pdf']);

        Livewire::test(Index::class)
            ->assertSee('ana-po.pdf')
            ->assertSee('ben-po.pdf')
            ->call('showUser', (string) $ben->id)
            ->assertSet('userId', (string) $ben->id)
            ->assertSee('ben-po.pdf')
            ->assertDontSee('ana-po.pdf')
            ->set('userId', 'none')
            ->assertSee('system-po.pdf')
            ->assertDontSee('ben-po.pdf')
            ->set('userId', '')
            ->assertSee('ana-po.pdf');
    }

    public function test_the_daily_user_limit_is_saved_from_settings(): void
    {
        $this->actingAsUser(['settings', 'settings access']);
        $setting = Setting::query()->orderBy('id')->first();
        $payload = [
            'data_per_page'     => $setting->data_per_page,
            'sales_order_limit' => $setting->sales_order_limit,
            'mcp_deadline'      => $setting->mcp_deadline,
        ];

        $this->post(route('setting.update', $setting->id), $payload + ['ai_daily_user_token_limit' => 50000])->assertSessionHasNoErrors();
        $this->assertSame(50000, (int) $setting->fresh()->ai_daily_user_token_limit);

        $this->post(route('setting.update', $setting->id), $payload + ['ai_daily_user_token_limit' => 0])->assertSessionHasErrors('ai_daily_user_token_limit');

        $this->post(route('setting.update', $setting->id), $payload + ['ai_daily_user_token_limit' => ''])->assertSessionHasNoErrors();
        $this->assertNull($setting->fresh()->ai_daily_user_token_limit);
    }

    public function test_it_totals_the_estimated_cost_and_counts_calls_without_a_price(): void
    {
        $this->actingAsUser();
        AiUsageLog::factory()->create(['total_tokens' => 1000, 'estimated_cost' => 0.0125]);
        AiUsageLog::factory()->create(['total_tokens' => 1000, 'estimated_cost' => 0.0075]);
        AiUsageLog::factory()->create(['total_tokens' => 1000, 'estimated_cost' => null]);

        Livewire::test(Index::class)
            ->assertViewHas('totals', fn ($totals) => abs((float) $totals->estimated_cost - 0.02) < 0.000001 && (int) $totals->unpriced === 1)
            ->assertSee('$0.0200')
            ->assertSee('1 call(s) had no price set');
    }

    public function test_saved_scans_are_counted_apart_from_ai_calls(): void
    {
        $this->actingAsUser();
        AiUsageLog::factory()->create(['total_tokens' => 1000, 'reference' => 'first-read.pdf']);
        AiUsageLog::factory()->create([
            'status' => AiUsageLog::STATUS_CACHED, 'input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0, 'reference' => 'reused.pdf',
        ]);

        Livewire::test(Index::class)
            ->assertViewHas('totals', fn ($totals) => (int) $totals->calls === 1)
            ->assertViewHas('cachedScans', 1)
            ->assertViewHas('byProvider', fn ($rows) => (int) $rows->sum('calls') === 1)
            ->assertSee('1 from saved scans')
            ->assertSee('reused.pdf')
            ->set('status', AiUsageLog::STATUS_CACHED)
            ->assertSee('reused.pdf')
            ->assertDontSee('first-read.pdf');
    }
}
