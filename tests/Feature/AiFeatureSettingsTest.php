<?php

namespace Tests\Feature;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiUnavailableException;
use App\Http\Livewire\AiSettings\Features;
use App\Http\Livewire\AiSettings\Index;
use App\Models\Account;
use App\Models\AiFeatureSetting;
use App\Models\AiProviderSetting;
use App\Models\AiUsageLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\Ai\AgentRunner;
use App\Services\Ai\Agents\ReportInsights;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiQuota;
use App\Services\Ai\AiResponse;
use App\Services\Ai\AiUsageMeter;
use App\Services\PoScanner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\FakesAnthropic;
use Tests\Concerns\FakesGemini;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * Each AI function can use its own providers and models instead of the main order on the AI Settings page.
 */
class AiFeatureSettingsTest extends TestCase
{
    use DatabaseTransactions;
    use FakesAnthropic;
    use FakesGemini;
    use SeedsReferenceData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedSettings();
        Setting::query()->orderBy('id')->first()->update(['ai_monthly_token_limit' => null, 'ai_daily_user_token_limit' => null]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureGemini();
        $this->configureAnthropic();
        config(['ai.providers.groq.key' => 'test-groq-key']);
        Cache::flush();

        AiProviderSetting::query()->delete();
        AiFeatureSetting::query()->delete();

        AiProviderSetting::factory()->create(['name' => 'gemini', 'priority' => 1, 'model' => 'gemini-main', 'effort' => 'low']);
        AiProviderSetting::factory()->create(['name' => 'anthropic', 'priority' => 2, 'model' => 'claude-main', 'effort' => 'high']);
        AiProviderSetting::factory()->disabled()->create(['name' => 'groq', 'priority' => 3, 'model' => 'groq-main', 'effort' => null]);
    }

    /**
     * @param array<int, string> $permissions
     */
    private function actingAsUser(array $permissions = ['ai settings manage']): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['module' => 'Test', 'description' => $permission]);
        }

        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }

    /**
     * @param array<int, AiProviderInterface> $chain
     * @return array<int, string>
     */
    private function describe(array $chain): array
    {
        return array_map(fn ($provider): string => $provider->name() . ':' . $provider->model(), $chain);
    }

    // -------------------------------------------------------------------------
    // Which providers and models a function uses
    // -------------------------------------------------------------------------

    public function test_a_function_without_its_own_providers_follows_the_main_order(): void
    {
        $client = app(AiClient::class)->forFeature(AiUsageLog::FEATURE_INSIGHTS);

        $this->assertFalse($client->hasOwnProviders());
        $this->assertSame(['gemini:gemini-main', 'anthropic:claude-main'], $this->describe($client->chain()));
    }

    public function test_a_function_with_its_own_providers_uses_only_those_with_their_models(): void
    {
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_INSIGHTS)->steps([['groq', 'llama-insights'], ['gemini', 'gemini-lite']])->create();

        $insights = app(AiClient::class)->forFeature(AiUsageLog::FEATURE_INSIGHTS);
        $mapping = app(AiClient::class)->forFeature(AiUsageLog::FEATURE_MAPPING);

        $this->assertTrue($insights->hasOwnProviders());
        $this->assertSame(['groq:llama-insights', 'gemini:gemini-lite'], $this->describe($insights->chain()), 'Groq is used although it is turned off in the main order.');
        $this->assertSame(['gemini:gemini-main', 'anthropic:claude-main'], $this->describe($mapping->chain()), 'Other functions keep the main order.');
        $this->assertSame(['gemini:gemini-main', 'anthropic:claude-main'], $this->describe(app(AiClient::class)->chain()));
    }

    public function test_a_blank_model_uses_the_providers_own_model_and_effort_stays_the_providers(): void
    {
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_MAPPING)->steps([['anthropic', null], ['gemini', 'gemini-lite']])->create();

        $chain = app(AiClient::class)->forFeature(AiUsageLog::FEATURE_MAPPING)->chain();

        $this->assertSame(['anthropic:claude-main', 'gemini:gemini-lite'], $this->describe($chain));
        $this->assertSame(['effort' => 'high'], $chain[0]->laravelAiOptions()['output_config'] ?? null);
    }

    public function test_a_function_whose_providers_have_no_key_says_so(): void
    {
        config(['ai.providers.groq.key' => null]);
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_ASSISTANT)->steps([['groq', 'llama']])->create();

        $problem = app(AiClient::class)->forFeature(AiUsageLog::FEATURE_ASSISTANT)->setupProblem();

        $this->assertStringContainsString('Sales assistant', (string) $problem);
        $this->assertStringContainsString('GROQ_API_KEY', (string) $problem);
        $this->assertNull(app(AiClient::class)->setupProblem(), 'The main order still works.');
    }

    public function test_an_agent_runs_on_the_model_set_for_its_function(): void
    {
        $user = $this->actingAsUser([]);
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_INSIGHTS)->steps([['gemini', 'gemini-insights']])->create();
        ReportInsights::fake(['Sales went up.']);

        app(AgentRunner::class)->prompt(new ReportInsights, 'Figures', AiUsageLog::FEATURE_INSIGHTS, $user);

        $log = AiUsageLog::query()->where('user_id', $user->id)->sole();
        $this->assertSame('gemini', $log->provider);
        $this->assertSame('gemini-insights', $log->model);
    }

    public function test_an_agent_fails_over_to_the_backup_set_for_its_function(): void
    {
        $user = $this->actingAsUser([]);
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_INSIGHTS)->steps([['groq', 'llama-down'], ['anthropic', 'claude-backup']])->create();
        config(['ai.providers.groq.key' => null]);
        ReportInsights::fake(['From the backup.']);

        app(AgentRunner::class)->prompt(new ReportInsights, 'Figures', AiUsageLog::FEATURE_INSIGHTS, $user);

        $log = AiUsageLog::query()->where('user_id', $user->id)->sole();
        $this->assertSame(['anthropic', 'claude-backup'], [$log->provider, $log->model]);
    }

    public function test_a_po_scan_is_sent_to_the_model_set_for_po_scans(): void
    {
        $user = $this->actingAsUser([]);
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_PO_SCAN)->steps([['gemini', 'gemini-scan']])->create();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['purchase_orders' => []]))]);

        try {
            app(PoScanner::class)->scan('%PDF-1.4 test', 'po.pdf', Account::factory()->create(), null, $user);
        } catch (AiUnavailableException) {
            // An empty reply is reported as "no PO lines"; only the request matters here.
        }

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/interactions') && data_get($request->data(), 'model') === 'gemini-scan');
    }

    public function test_a_function_step_with_its_own_prices_is_charged_those(): void
    {
        AiProviderSetting::query()->where('name', 'gemini')->update(['input_price' => 1.0, 'output_price' => 10.0]);
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_INSIGHTS)->steps([['gemini', 'gemini-lite', 0.1, 0.4]])->create();
        $meter = app(AiUsageMeter::class);

        $own = $meter->record(['feature' => AiUsageLog::FEATURE_INSIGHTS, 'status' => AiUsageLog::STATUS_SUCCESS], new AiResponse('ok', 'gemini', 'gemini-lite', 1_000_000, 1_000_000, 2_000_000, 10));
        $newerVersion = $meter->record(['feature' => AiUsageLog::FEATURE_INSIGHTS, 'status' => AiUsageLog::STATUS_SUCCESS], new AiResponse('ok', 'gemini', 'gemini-lite-002', 1_000_000, 1_000_000, 2_000_000, 10));
        $main = $meter->record(['feature' => AiUsageLog::FEATURE_MAPPING, 'status' => AiUsageLog::STATUS_SUCCESS], new AiResponse('ok', 'gemini', 'gemini-main', 1_000_000, 1_000_000, 2_000_000, 10));

        $this->assertEqualsWithDelta(0.5, (float) $own->estimated_cost, 0.000001);
        $this->assertEqualsWithDelta(0.5, (float) $newerVersion->estimated_cost, 0.000001, 'A reply from a newer version of the model still gets the step price.');
        $this->assertEqualsWithDelta(11.0, (float) $main->estimated_cost, 0.000001);
    }

    public function test_a_step_without_prices_is_charged_the_providers_prices(): void
    {
        AiProviderSetting::query()->where('name', 'gemini')->update(['input_price' => 1.0, 'output_price' => 10.0]);
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_INSIGHTS)->steps([['gemini', 'gemini-lite']])->create();

        $log = app(AiUsageMeter::class)->record(['feature' => AiUsageLog::FEATURE_INSIGHTS, 'status' => AiUsageLog::STATUS_SUCCESS], new AiResponse('ok', 'gemini', 'gemini-lite', 1_000_000, 1_000_000, 2_000_000, 10));

        $this->assertEqualsWithDelta(11.0, (float) $log->estimated_cost, 0.000001);
    }

    public function test_a_used_up_daily_quota_of_one_gemini_model_leaves_the_other_models_working(): void
    {
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_INSIGHTS)->steps([['gemini', 'gemini-lite']])->create();
        AiQuota::block('gemini', now()->addHours(3), 'Gemini\'s daily free-tier request limit for gemini-main is used up.', 'gemini-main');

        $this->assertSame(['anthropic:claude-main'], $this->describe(app(AiClient::class)->availableProviders()));
        $this->assertSame(['gemini:gemini-lite'], $this->describe(app(AiClient::class)->forFeature(AiUsageLog::FEATURE_INSIGHTS)->availableProviders()));

        AiQuota::clear('gemini', 'gemini-main');
        $this->assertCount(2, app(AiClient::class)->availableProviders());
    }

    public function test_a_used_up_quota_of_the_whole_provider_blocks_every_model(): void
    {
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_INSIGHTS)->steps([['anthropic', 'claude-other']])->create();
        AiQuota::block('anthropic', now()->addHour(), 'Claude has no credits left.');

        $this->assertSame([], app(AiClient::class)->forFeature(AiUsageLog::FEATURE_INSIGHTS)->availableProviders());
        $this->assertStringContainsString('no credits', (string) app(AiClient::class)->forFeature(AiUsageLog::FEATURE_INSIGHTS)->setupProblem());
    }

    public function test_the_gemini_daily_per_model_quota_blocks_only_that_model(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => [
            'code' => 429, 'status' => 'RESOURCE_EXHAUSTED', 'message' => 'Quota exceeded',
            'details' => [['@type' => 'type.googleapis.com/google.rpc.QuotaFailure', 'violations' => [['quotaId' => 'GenerateRequestsPerDayPerProjectPerModel-FreeTier']]]],
        ]], 429)]);

        try {
            app(AiClient::class)->chain()[0]->generateJson('Read it.', 'Prompt', [], ['type' => 'object', 'properties' => ['a' => ['type' => 'string']]], 100);
            $this->fail('The used-up quota should fail the call.');
        } catch (AiUnavailableException) {
        }

        $this->assertNotNull(AiQuota::blocked('gemini', 'gemini-main'));
        $this->assertNull(AiQuota::blocked('gemini', 'gemini-lite'));
        $this->assertNull(AiQuota::blocked('gemini'));
    }

    // -------------------------------------------------------------------------
    // The AI Settings page
    // -------------------------------------------------------------------------

    public function test_the_function_settings_need_the_settings_permission(): void
    {
        $this->actingAsUser([]);

        Livewire::test(Features::class)->assertForbidden();
    }

    public function test_every_function_starts_on_the_main_order(): void
    {
        $this->actingAsUser();

        Livewire::test(Features::class)
            ->assertSet('features.po_scan.own', false)
            ->assertSet('features.sales_assistant.own', false)
            ->assertSee('PO scan')
            ->assertSee('Upload mapping help')
            ->assertSee('Gemini · gemini-main');
    }

    public function test_turning_on_own_providers_starts_from_the_main_order(): void
    {
        $this->actingAsUser();

        Livewire::test(Features::class)
            ->set('features.report_insights.own', true)
            ->assertSet('features.report_insights.steps', [
                ['name' => 'gemini', 'model' => 'gemini-main', 'input_price' => '', 'output_price' => ''],
                ['name' => 'anthropic', 'model' => 'claude-main', 'input_price' => '', 'output_price' => ''],
            ]);
    }

    public function test_saving_stores_the_providers_and_models_of_a_function(): void
    {
        $this->actingAsUser();

        Livewire::test(Features::class)
            ->set('features.report_insights.own', true)
            ->set('features.report_insights.steps.0.model', ' gemini-lite ')
            ->set('features.report_insights.steps.0.input_price', '0.1')
            ->call('moveStep', 'report_insights', 0, 1)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('AI function models saved.');

        $saved = AiFeatureSetting::query()->where('feature', AiUsageLog::FEATURE_INSIGHTS)->sole();
        $this->assertSame([
            ['name' => 'anthropic', 'model' => 'claude-main', 'input_price' => null, 'output_price' => null],
            ['name' => 'gemini', 'model' => 'gemini-lite', 'input_price' => 0.1, 'output_price' => null],
        ], $saved->providers);
        $this->assertSame(['anthropic:claude-main', 'gemini:gemini-lite'], $this->describe(app(AiClient::class)->forFeature(AiUsageLog::FEATURE_INSIGHTS)->chain()));
        $this->assertSame(0, AiFeatureSetting::query()->where('feature', '!=', AiUsageLog::FEATURE_INSIGHTS)->count(), 'Functions left on the main order get no row.');
    }

    public function test_turning_own_providers_off_puts_the_function_back_on_the_main_order(): void
    {
        $this->actingAsUser();
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_MAPPING)->steps([['groq', 'llama']])->create();

        Livewire::test(Features::class)
            ->assertSet('features.mapping_help.own', true)
            ->set('features.mapping_help.own', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse(AiFeatureSetting::query()->where('feature', AiUsageLog::FEATURE_MAPPING)->exists());
        $this->assertFalse(app(AiClient::class)->forFeature(AiUsageLog::FEATURE_MAPPING)->hasOwnProviders());
    }

    public function test_po_scans_can_only_pick_providers_that_read_pdfs(): void
    {
        $this->actingAsUser();

        $component = Livewire::test(Features::class)->set('features.po_scan.own', true);

        $this->assertNotContains('groq', array_column($component->get('features.po_scan.steps'), 'name'));
        $component->assertDontSeeHtml('<option value="groq">Groq</option>');

        $component->set('features.po_scan.steps.0.name', 'groq')
            ->call('save')
            ->assertHasErrors(['features.po_scan.steps.0.name']);

        $this->assertFalse(AiFeatureSetting::query()->exists());
    }

    public function test_the_same_provider_and_model_twice_is_refused_but_another_model_is_fine(): void
    {
        $this->actingAsUser();

        $component = Livewire::test(Features::class)
            ->set('features.sales_assistant.own', true)
            ->set('features.sales_assistant.steps.1.name', 'gemini')
            ->set('features.sales_assistant.steps.1.model', 'gemini-main')
            ->call('save')
            ->assertHasErrors(['features.sales_assistant.steps.1.model']);

        $component->set('features.sales_assistant.steps.1.model', 'gemini-lite')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['gemini:gemini-main', 'gemini:gemini-lite'], $this->describe(app(AiClient::class)->forFeature(AiUsageLog::FEATURE_ASSISTANT)->chain()));
    }

    public function test_a_function_with_own_providers_needs_at_least_one_and_valid_prices(): void
    {
        $this->actingAsUser();

        Livewire::test(Features::class)
            ->set('features.report_insights.own', true)
            ->set('features.report_insights.steps.0.output_price', '-1')
            ->call('save')
            ->assertHasErrors(['features.report_insights.steps.0.output_price' => 'min'])
            ->call('removeStep', 'report_insights', 1)
            ->call('removeStep', 'report_insights', 0)
            ->call('save')
            ->assertHasErrors(['features.report_insights.steps' => 'required']);

        $this->assertFalse(AiFeatureSetting::query()->exists());
    }

    public function test_choosing_another_provider_in_a_row_switches_to_its_model(): void
    {
        $this->actingAsUser();

        Livewire::test(Features::class)
            ->set('features.report_insights.own', true)
            ->set('features.report_insights.steps.0.name', 'groq')
            ->assertSet('features.report_insights.steps.0.model', 'groq-main');
    }

    public function test_steps_can_be_added_up_to_the_limit(): void
    {
        $this->actingAsUser();

        $component = Livewire::test(Features::class)->set('features.mapping_help.own', true);

        for ($i = 0; $i < Features::MAX_STEPS + 2; $i++) {
            $component->call('addStep', 'mapping_help');
        }

        $this->assertCount(Features::MAX_STEPS, $component->get('features.mapping_help.steps'));
    }

    public function test_load_models_fills_the_suggestions_for_a_provider(): void
    {
        $this->actingAsUser();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['models' => [
            ['name' => 'models/gemini-3.5-flash', 'supportedGenerationMethods' => ['generateContent']],
            ['name' => 'models/gemini-3.5-flash-lite', 'supportedGenerationMethods' => ['generateContent']],
        ]])]);

        Livewire::test(Features::class)
            ->set('features.report_insights.own', true)
            ->call('loadModels', 'gemini')
            ->assertSet('modelLists.gemini', ['gemini-3.5-flash', 'gemini-3.5-flash-lite'])
            ->assertSee('2 models listed.');
    }

    public function test_a_blocked_model_shows_on_its_row_and_can_be_tried_again(): void
    {
        $this->actingAsUser();
        AiFeatureSetting::factory()->forFeature(AiUsageLog::FEATURE_INSIGHTS)->steps([['gemini', 'gemini-lite']])->create();
        AiQuota::block('gemini', now()->addHours(2), 'Gemini\'s daily request limit for gemini-lite is used up.', 'gemini-lite');

        Livewire::test(Features::class)
            ->assertSee('daily request limit for gemini-lite is used up')
            ->call('tryAgainNow', 'gemini', 'gemini-lite')
            ->assertDontSee('daily request limit for gemini-lite is used up');

        $this->assertNull(AiQuota::blocked('gemini', 'gemini-lite'));
    }

    public function test_saving_the_main_order_tells_the_function_settings(): void
    {
        $this->actingAsUser();

        Livewire::test(Index::class)->call('save')->assertDispatched('ai-settings-saved');
    }

    public function test_the_ai_settings_page_shows_both_parts(): void
    {
        $this->actingAsUser();

        $this->get(route('ai-settings.index'))
            ->assertOk()
            ->assertSeeLivewire('ai-settings.index')
            ->assertSeeLivewire('ai-settings.features');
    }
}
