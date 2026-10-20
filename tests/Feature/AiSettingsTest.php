<?php

namespace Tests\Feature;

use App\Http\Livewire\AiSettings\Index;
use App\Models\AiProviderSetting;
use App\Models\AiUsageLog;
use App\Models\User;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiManager;
use App\Services\Ai\AiResponse;
use App\Services\Ai\AiUsageMeter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\FakesAnthropic;
use Tests\Concerns\FakesGemini;
use Tests\TestCase;

class AiSettingsTest extends TestCase
{
    use DatabaseTransactions;
    use FakesAnthropic;
    use FakesGemini;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->configureGemini();
        $this->configureAnthropic();
        config(['ai.providers.openai.key' => null]);

        AiProviderSetting::query()->delete();
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

    // -------------------------------------------------------------------------
    // Saved settings drive the AI client
    // -------------------------------------------------------------------------

    public function test_without_saved_settings_the_env_order_applies(): void
    {
        config(['ai.failover' => 'claude']);

        $this->assertFalse(app(AiManager::class)->isManagedInApp());
        $this->assertSame(['gemini', 'anthropic'], app(AiClient::class)->configuredNames());
    }

    public function test_saved_settings_replace_the_env_order_model_and_effort(): void
    {
        config(['ai.failover' => '']);
        AiProviderSetting::factory()->create(['name' => 'anthropic', 'priority' => 1, 'model' => 'claude-saved', 'effort' => 'high']);
        AiProviderSetting::factory()->create(['name' => 'gemini', 'priority' => 2, 'model' => 'gemini-saved', 'effort' => null]);
        AiProviderSetting::factory()->disabled()->create(['name' => 'openai', 'priority' => 3, 'model' => 'gpt-saved']);

        $client = app(AiClient::class);

        $this->assertSame(['anthropic', 'gemini'], $client->configuredNames());
        $this->assertSame(['claude-saved', 'gemini-saved'], array_map(fn ($provider) => $provider->model(), $client->chain()));

        $this->fakeAnthropic([$this->claudeError(500, 'api_error', 'down')]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->geminiReply(['ok' => true]))]);

        $client->generateJson('Be exact.', 'Read this.', [], ['type' => 'object', 'properties' => []], 1000);

        $this->assertSame('claude-saved', $this->anthropicRequestBody()['model']);
        $this->assertSame('high', $this->anthropicRequestBody()['output_config']['effort']);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/interactions')
            && data_get($request->data(), 'model') === 'gemini-saved'
            && !array_key_exists('thinking_level', (array) data_get($request->data(), 'generation_config')));
    }

    public function test_with_every_saved_provider_turned_off_ai_says_so(): void
    {
        AiProviderSetting::factory()->disabled()->create(['name' => 'gemini']);

        $this->assertSame('AI is not set up yet. Turn on a provider on the AI Settings page.', app(AiClient::class)->setupProblem());
    }

    public function test_the_cost_of_a_call_is_estimated_from_the_saved_prices(): void
    {
        AiProviderSetting::factory()->priced(0.5, 3.0)->create(['name' => 'gemini']);

        $log = app(AiUsageMeter::class)->record(
            ['feature' => AiUsageLog::FEATURE_PO_SCAN, 'status' => AiUsageLog::STATUS_SUCCESS],
            new AiResponse('{}', 'gemini', 'gemini-test', 1_000_000, 100_000, 1_100_000, 1000)
        );

        $this->assertEqualsWithDelta(0.8, $log->fresh()->estimated_cost, 0.000001);
    }

    public function test_a_provider_without_a_price_has_no_estimated_cost(): void
    {
        AiProviderSetting::factory()->create(['name' => 'gemini']);

        $log = app(AiUsageMeter::class)->record(
            ['feature' => AiUsageLog::FEATURE_PO_SCAN, 'status' => AiUsageLog::STATUS_SUCCESS],
            new AiResponse('{}', 'gemini', 'gemini-test', 1000, 100, 1100, 1000)
        );

        $this->assertNull($log->fresh()->estimated_cost);
    }

    // -------------------------------------------------------------------------
    // The AI Settings page
    // -------------------------------------------------------------------------

    public function test_the_page_needs_the_settings_permission(): void
    {
        $this->actingAsUser([]);

        $this->get(route('ai-settings.index'))->assertForbidden();
    }

    public function test_the_page_starts_from_the_env_file(): void
    {
        $this->actingAsUser();
        config(['ai.provider' => 'claude', 'ai.failover' => 'gemini', 'ai.providers.anthropic.model' => 'claude-env']);

        $this->get(route('ai-settings.index'))->assertOk()->assertSeeLivewire('ai-settings.index');

        Livewire::test(Index::class)
            ->assertSet('providers.0.name', 'anthropic')
            ->assertSet('providers.0.model', 'claude-env')
            ->assertSet('providers.0.is_enabled', true)
            ->assertSet('providers.1.name', 'gemini')
            ->assertSet('providers.1.is_enabled', true)
            ->assertSet('providers.2.name', 'openai')
            ->assertSet('providers.2.is_enabled', false)
            ->assertSet('providers.2.base_url', 'https://api.openai.com/v1/')
            ->assertSee('still follow the');
    }

    public function test_saving_stores_the_order_and_settings_of_every_provider(): void
    {
        $user = $this->actingAsUser();

        Livewire::test(Index::class)
            ->call('moveDown', 0)
            ->set('providers.0.is_enabled', true)
            ->set('providers.0.effort', 'max')
            ->set('providers.1.model', 'gemini-new')
            ->set('providers.1.effort', '')
            ->set('providers.1.input_price', '0.5')
            ->set('providers.1.output_price', '3')
            ->set('providers.2.base_url', 'https://openrouter.ai/api/v1/')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('AI settings saved.');

        $saved = AiProviderSetting::query()->orderBy('priority')->get()->keyBy('name');

        $this->assertSame(['anthropic', 'gemini', 'openai'], $saved->keys()->all());
        $this->assertSame([1, 2, 3], $saved->pluck('priority')->all());
        $this->assertTrue($saved['anthropic']->is_enabled);
        $this->assertSame('max', $saved['anthropic']->effort);
        $this->assertSame('gemini-new', $saved['gemini']->model);
        $this->assertNull($saved['gemini']->effort);
        $this->assertSame(0.5, $saved['gemini']->input_price);
        $this->assertSame(3.0, $saved['gemini']->output_price);
        $this->assertNull($saved['anthropic']->base_url);
        $this->assertSame('https://openrouter.ai/api/v1/', $saved['openai']->base_url);
        $this->assertFalse($saved['openai']->is_enabled);
        $this->assertSame($user->id, $saved['gemini']->updated_by);

        $this->assertSame(['anthropic', 'gemini'], app(AiClient::class)->configuredNames());
    }

    public function test_invalid_values_are_not_saved(): void
    {
        $this->actingAsUser();

        Livewire::test(Index::class)
            ->set('providers.0.effort', 'xhigh')
            ->set('providers.0.model', '')
            ->set('providers.1.input_price', '-1')
            ->set('providers.2.base_url', 'not a url')
            ->call('save')
            ->assertHasErrors([
                'providers.0.effort' => 'in',
                'providers.0.model' => 'required',
                'providers.1.input_price' => 'min',
                'providers.2.base_url' => 'url',
            ]);

        $this->assertSame(0, AiProviderSetting::query()->count());
    }

    public function test_the_connection_test_uses_the_unsaved_model(): void
    {
        $this->actingAsUser();
        Http::fake(['generativelanguage.googleapis.com/v1beta/models/gemini-typed' => Http::response(['displayName' => 'Gemini Typed'])]);

        Livewire::test(Index::class)
            ->set('providers.0.model', 'gemini-typed')
            ->call('testConnection', 0)
            ->assertSet('checks.gemini', ['ok' => true, 'message' => 'Connected. Gemini Typed (gemini-typed) is ready.'])
            ->assertSee('Connected. Gemini Typed (gemini-typed) is ready.');

        $this->assertSame(0, AiProviderSetting::query()->count());
    }

    public function test_a_failed_connection_test_shows_why(): void
    {
        $this->actingAsUser();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'not found']], 404)]);

        Livewire::test(Index::class)
            ->call('testConnection', 0)
            ->assertSet('checks.gemini.ok', false)
            ->assertSee('was not found. Choose another model on the AI Settings page.');
    }

    public function test_load_models_fills_the_model_suggestions(): void
    {
        $this->actingAsUser();
        Http::fake(['generativelanguage.googleapis.com/v1beta/models*' => Http::response(['models' => [
            ['name' => 'models/gemini-b', 'supportedGenerationMethods' => ['generateContent']],
            ['name' => 'models/gemini-a', 'supportedGenerationMethods' => ['generateContent']],
        ]])]);

        Livewire::test(Index::class)
            ->call('loadModels', 0)
            ->assertSet('modelLists.gemini', ['gemini-a', 'gemini-b'])
            ->assertSee('2 models found.')
            ->call('loadModels', 0);

        Http::assertSentCount(1);
    }

    public function test_resetting_goes_back_to_the_env_file(): void
    {
        $this->actingAsUser();
        AiProviderSetting::factory()->create(['name' => 'anthropic', 'model' => 'claude-saved']);

        Livewire::test(Index::class)
            ->assertSet('providers.0.model', 'claude-saved')
            ->call('resetToEnv')
            ->assertSet('providers.0.name', 'gemini')
            ->assertSee('The .env file applies again.');

        $this->assertSame(0, AiProviderSetting::query()->count());
    }
}
