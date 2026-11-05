<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

/**
 * The maintenance page switched on from the .env file (MAINTENANCE_MODE, or by itself at MAINTENANCE_STARTS_AT),
 * and the countdown shown on every page before scheduled maintenance.
 */
class MaintenanceModeTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedSettings();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        config([
            'maintenance.enabled'         => false,
            'maintenance.secret'          => null,
            'maintenance.allowed_ips'     => [],
            'maintenance.until'           => null,
            'maintenance.message'         => null,
            'maintenance.starts_at'       => null,
            'maintenance.warning_minutes' => 30,
        ]);
    }

    private function signedInUser(): User
    {
        Permission::firstOrCreate(['name' => 'ai settings manage', 'guard_name' => 'web'], ['module' => 'Test', 'description' => 'ai settings manage']);

        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo('ai settings manage');

        return $user;
    }

    // -------------------------------------------------------------------------
    // The maintenance page
    // -------------------------------------------------------------------------

    public function test_the_site_is_left_alone_while_maintenance_mode_is_off(): void
    {
        $this->get(route('login'))->assertOk()->assertDontSee('Play while you wait');
    }

    public function test_everyone_gets_the_maintenance_page_with_a_503_while_it_is_on(): void
    {
        config(['maintenance.enabled' => true, 'maintenance.until' => '2026-11-24 18:00', 'maintenance.message' => 'Moving to the new server.']);

        $this->get(route('login'))
            ->assertStatus(503)
            ->assertHeader('Retry-After', '300')
            ->assertSee('Under maintenance')
            ->assertSee('We are making the Sales Management System better.')
            ->assertSee('Moving to the new server.')
            ->assertSee('Expected back Tue, 24 Nov 2026 6:00 PM')
            ->assertSee('Play while you wait')
            ->assertSee(asset('vendor/three/three.module.min.js'), false)
            ->assertDontSee('BEVI Central');

        $this->actingAs($this->signedInUser())->get(route('ai-settings.index'))->assertStatus(503);
    }

    public function test_api_callers_get_json(): void
    {
        config(['maintenance.enabled' => true]);

        $this->getJson('/api/sales-orders')
            ->assertStatus(503)
            ->assertJson(['message' => 'The system is being updated. It will be back shortly.']);
    }

    public function test_the_team_gets_in_through_the_secret_link_and_the_addresses_allowed(): void
    {
        config(['maintenance.enabled' => true, 'maintenance.secret' => 'letmein']);

        $this->get('/login?maintenance=wrong')->assertStatus(503);

        $pass = $this->get('/login?maintenance=letmein')->assertRedirect(url('/login'));
        $cookie = collect($pass->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'sms_maintenance_pass');

        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());

        $this->withUnencryptedCookie('sms_maintenance_pass', $cookie->getValue())->get(route('login'))->assertOk();
        $this->withUnencryptedCookie('sms_maintenance_pass', 'made-up')->get(route('login'))->assertStatus(503);

        config(['maintenance.allowed_ips' => ['127.0.0.1']]);
        $this->get(route('login'))->assertOk();
    }

    public function test_php_artisan_down_shows_the_same_page(): void
    {
        $html = view('errors.503', ['exception' => new HttpException(503, '')])->render();

        $this->assertStringContainsString('Under maintenance', $html);
        $this->assertStringContainsString('The system is being updated. It will be back shortly.', $html);
        $this->assertStringContainsString('Squash', $html);
        $this->assertStringNotContainsString('cdn.', $html);
    }

    public function test_three_js_comes_from_the_app_never_a_cdn(): void
    {
        $this->assertTrue(File::exists(public_path('vendor/three/three.module.min.js')));
        $this->assertDoesNotMatchRegularExpression('#https?://(cdn|unpkg|cdnjs)#', File::get(resource_path('views/maintenance/page.blade.php')));
    }

    // -------------------------------------------------------------------------
    // The countdown before scheduled maintenance
    // -------------------------------------------------------------------------

    public function test_every_page_counts_down_to_scheduled_maintenance_in_the_warning_window(): void
    {
        $this->travelTo(now()->setTime(16, 55, 0));
        config(['maintenance.starts_at' => now()->setTime(17, 0)->format('Y-m-d H:i')]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-seconds-left="300"', false)
            ->assertSee('The system goes into maintenance in')
            ->assertSee('05:00')
            ->assertSee('(at 5:00 PM). Save your work before then.');

        $this->actingAs($this->signedInUser())->get(route('ai-settings.index'))->assertOk()->assertSee('data-maintenance-warning', false);
    }

    public function test_a_countdown_longer_than_an_hour_shows_hours(): void
    {
        $this->travelTo(now()->setTime(15, 0, 0));
        config(['maintenance.starts_at' => now()->setTime(16, 30)->format('Y-m-d H:i'), 'maintenance.warning_minutes' => 120]);

        $this->get(route('login'))->assertSee('data-seconds-left="5400"', false)->assertSee('1:30:00');
    }

    public function test_there_is_no_countdown_before_the_window_without_a_start_or_with_a_time_it_cannot_read(): void
    {
        $this->travelTo(now()->setTime(16, 0, 0));

        config(['maintenance.starts_at' => now()->setTime(17, 0)->format('Y-m-d H:i'), 'maintenance.warning_minutes' => 30]);
        $this->get(route('login'))->assertOk()->assertDontSee('data-maintenance-warning', false);

        config(['maintenance.warning_minutes' => 0]);
        $this->get(route('login'))->assertOk()->assertDontSee('data-maintenance-warning', false);

        config(['maintenance.starts_at' => null, 'maintenance.warning_minutes' => 30]);
        $this->get(route('login'))->assertOk()->assertDontSee('data-maintenance-warning', false);

        config(['maintenance.starts_at' => 'next payday']);
        $this->get(route('login'))->assertOk()->assertDontSee('data-maintenance-warning', false);
    }

    public function test_maintenance_comes_on_by_itself_at_the_scheduled_time_and_stays_on(): void
    {
        $this->travelTo(now()->setTime(16, 59, 59));
        config(['maintenance.starts_at' => now()->setTime(17, 0)->format('Y-m-d H:i')]);

        $this->get(route('login'))->assertOk()->assertSee('data-seconds-left="1"', false);

        $this->travelTo(now()->setTime(17, 0, 0));
        $this->get(route('login'))->assertStatus(503)->assertSee('Under maintenance')->assertDontSee('data-maintenance-warning', false);

        $this->travelTo(now()->addDay());
        $this->get(route('login'))->assertStatus(503);
        $this->getJson('/api/sales-orders')->assertStatus(503);
    }

    public function test_there_is_no_countdown_while_maintenance_mode_is_already_on(): void
    {
        $this->travelTo(now()->setTime(16, 55, 0));
        config(['maintenance.enabled' => true, 'maintenance.starts_at' => now()->setTime(17, 0)->format('Y-m-d H:i')]);

        $this->get(route('login'))->assertStatus(503)->assertDontSee('data-maintenance-warning', false);
    }

    public function test_the_team_is_told_maintenance_is_on_and_nobody_else(): void
    {
        config(['maintenance.enabled' => true, 'maintenance.allowed_ips' => ['127.0.0.1']]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Maintenance mode is on. Only the team can use the system right now.')
            ->assertDontSee('data-maintenance-warning', false);

        config(['maintenance.enabled' => false]);
        $this->get(route('login'))->assertOk()->assertDontSee('Maintenance mode is on.');
    }
}
