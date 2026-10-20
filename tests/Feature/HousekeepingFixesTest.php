<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\SalesOrderCutOff;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\SeedsReferenceData;
use Tests\TestCase;

class HousekeepingFixesTest extends TestCase
{
    use DatabaseTransactions;
    use SeedsReferenceData;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seedSettings();
    }

    /**
     * @param array<int, string> $permissions
     */
    private function actingAsUser(array $permissions = []): User
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web'], ['module' => 'Test', 'description' => $permission]);
        }

        $user = User::factory()->create(['status' => 'active']);
        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }

    public function test_the_cost_center_list_renders_a_cost_center_without_a_user(): void
    {
        $this->actingAsUser(['cost center access']);
        $company = \App\Models\Company::create(['name' => 'HK CO', 'order_limit' => 10]);
        \App\Models\CostCenter::create(['company_id' => $company->id, 'cost_center' => 987654]);

        $this->get(route('cost-center.index'))->assertOk()->assertSee('987654');
    }

    public function test_the_department_list_renders_departments_without_a_head_and_has_pagination(): void
    {
        $this->actingAsUser(['department access']);
        Department::create(['department_code' => 'HK-DEPT', 'department_name' => 'Headless Department']);

        $this->get(route('department.index'))
            ->assertOk()
            ->assertSee('Headless Department')
            ->assertSee("Livewire.dispatch('setDeleteModel', ['Department', id])", false);
    }

    public function test_the_cut_off_list_shows_minutes_and_twelve_hour_times(): void
    {
        $user = $this->actingAsUser(['so cut-off access']);
        SalesOrderCutOff::create([
            'user_id' => $user->id,
            'start_date' => strtotime('2026-10-01 08:30'),
            'end_date' => strtotime('2026-10-01 17:45'),
            'message' => 'Housekeeping cut-off',
        ]);

        $this->get(route('cut-off.index'))
            ->assertOk()
            ->assertSee('2026-10-01 08:30:00 am')
            ->assertSee('2026-10-01 05:45:00 pm')
            ->assertSee("Livewire.dispatch('setDeleteModel', ['SalesOrderCutOff', id])", false);
    }

    public function test_the_error_log_viewer_needs_the_permission_the_menu_checks(): void
    {
        $this->actingAsUser();

        $this->get('/logs')->assertForbidden();
    }

    public function test_the_error_log_viewer_opens_for_role_access(): void
    {
        $this->actingAsUser(['role access']);

        $this->get('/logs')->assertOk();
    }

    public function test_site_clear_needs_the_settings_permission(): void
    {
        $this->actingAsUser();

        $this->get('/site-clear')->assertForbidden();
    }

    public function test_the_generate_xml_route_without_a_controller_method_is_gone(): void
    {
        $this->assertFalse(collect(Route::getRoutes())->contains(fn ($route) => $route->uri() === 'generateXML'));
    }

    public function test_get_xml_needs_the_sales_order_list_permission(): void
    {
        $this->actingAsUser();

        $this->get(route('sales-order.xml'))->assertForbidden();
    }

    public function test_the_salesman_location_migration_grants_the_route_names_to_holders_of_the_old_names(): void
    {
        Permission::firstOrCreate(['name' => 'salesmen location access', 'guard_name' => 'web'], ['module' => 'Salesman Location', 'description' => 'old name']);
        $role = Role::create(['name' => 'housekeeping-role-'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo('salesmen location access');

        $migration = require database_path('migrations/2026_10_16_083259_add_salesman_location_permissions.php');
        $migration->up();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role->refresh();

        $this->assertTrue($role->hasPermissionTo('salesman location access'));
        $this->assertFalse($role->hasPermissionTo('salesman location create'));
        $this->assertTrue(Permission::where('name', 'salesman location edit')->exists());
    }

    public function test_the_salesman_location_page_opens_with_the_permission_the_routes_check(): void
    {
        $this->actingAsUser(['salesman location access']);

        $this->get(route('salesman-location.index'))->assertOk();
    }

    public function test_the_permission_seeder_creates_the_names_the_app_checks(): void
    {
        $seeder = file_get_contents(database_path('seeders/PermissionSeeder.php'));

        $this->assertStringContainsString("'salesman location access'", $seeder);
        $this->assertStringNotContainsString("'salesmen location access'", $seeder);
        $this->assertStringContainsString("'settings access'", $seeder);
    }

    public function test_no_view_loads_scripts_from_a_cdn(): void
    {
        $offenders = collect(\Illuminate\Support\Facades\File::allFiles(resource_path('views')))
            ->filter(fn ($file) => preg_match('#(code\.highcharts\.com|cdnjs\.cloudflare\.com|cdn\.tailwindcss\.com)#', $file->getContents()))
            ->map(fn ($file) => $file->getRelativePathname())
            ->values()
            ->all();

        $this->assertSame([], $offenders);
    }

    public function test_the_layout_serves_source_sans_pro_locally_instead_of_from_google(): void
    {
        $this->actingAsUser(['department access']);

        $this->get(route('department.index'))
            ->assertOk()
            ->assertSee('vendor/source-sans-pro/source-sans-pro.css', false)
            ->assertDontSee('fonts.googleapis.com', false);
    }

    public function test_every_font_file_the_local_stylesheet_names_exists(): void
    {
        $css = file_get_contents(public_path('vendor/source-sans-pro/source-sans-pro.css'));
        preg_match_all('/url\(([^)]+)\)/', $css, $matches);

        $this->assertCount(16, $matches[1]);

        foreach ($matches[1] as $file) {
            $this->assertFileExists(public_path('vendor/source-sans-pro/' . $file));
        }
    }
}
