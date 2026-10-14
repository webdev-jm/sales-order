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
}
