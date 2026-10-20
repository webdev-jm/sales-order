<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Live only gets new permissions through "php artisan migrate" (PermissionSeeder is not run there), so a
 * permission added to the seeder also needs a migration such as this one.
 */
class PermissionMigrationsTest extends TestCase
{
    use DatabaseTransactions;

    private const MIGRATION = '2026_10_20_081840_add_brand_upload_template_and_settings_permissions';

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_every_permission_the_code_checks_is_in_the_permission_seeder(): void
    {
        $seeder = File::get(database_path('seeders/PermissionSeeder.php'));

        $missing = collect($this->permissionsCheckedInCode())
            ->reject(fn (string $name) => str_contains($seeder, "'{$name}'"))
            ->values()
            ->all();

        $this->assertSame([], $missing, 'Permissions missing from PermissionSeeder: ' . implode(', ', $missing));
    }

    public function test_the_migration_creates_the_brand_upload_template_and_settings_permissions_for_superadmin(): void
    {
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        $this->runMigration();

        $superadmin = Role::where('name', 'superadmin')->first();

        foreach (['brand access', 'brand create', 'brand edit', 'brand delete', 'upload template access', 'upload template create', 'upload template edit', 'upload template delete', 'settings access'] as $name) {
            $this->assertTrue(Permission::where('name', $name)->exists(), "{$name} was not created");
            $this->assertTrue($superadmin->hasPermissionTo($name), "superadmin lacks {$name}");
        }
    }

    public function test_a_role_that_can_open_the_settings_page_can_also_save_it(): void
    {
        Permission::firstOrCreate(['name' => 'settings', 'guard_name' => 'web'], ['module' => 'Setting', 'description' => 'settings access']);
        Permission::where('name', 'settings access')->delete();
        $role = Role::create(['name' => 'settings regression role', 'guard_name' => 'web']);
        $role->givePermissionTo('settings');
        $other = Role::create(['name' => 'no settings regression role', 'guard_name' => 'web']);

        $this->runMigration();

        $this->assertTrue($role->fresh()->hasPermissionTo('settings access'));
        $this->assertFalse($other->fresh()->hasPermissionTo('settings access'));
    }

    public function test_running_the_migration_twice_creates_no_duplicates(): void
    {
        $this->runMigration();
        $this->runMigration();

        $this->assertSame(1, Permission::where('name', 'brand access')->count());
        $this->assertSame(1, Permission::where('name', 'settings access')->count());
    }

    private function runMigration(): void
    {
        $migrator = app('migrator');
        $path = database_path('migrations/' . self::MIGRATION . '.php');

        (fn (string $file) => $this->resolvePath($file))->call($migrator, $path)->up();
    }

    /**
     * @return array<int, string>
     */
    private function permissionsCheckedInCode(): array
    {
        $names = [];
        $patterns = [
            "/@(?:can|cannot|canany)\\(\\s*'([^']+)'/",
            "/(?:->|::)(?:can|cannot|hasPermissionTo|checkPermissionTo)\\(\\s*'([^']+)'/",
            "/'permission:([^']+)'/",
            "/'can'\\s*=>\\s*'([^']+)'/",
        ];

        foreach ([app_path(), resource_path('views'), base_path('routes'), config_path()] as $directory) {
            foreach (File::allFiles($directory) as $file) {
                foreach ($patterns as $pattern) {
                    preg_match_all($pattern, $file->getContents(), $matches);

                    foreach ($matches[1] as $match) {
                        foreach (preg_split('/[|,]/', $match) as $name) {
                            if (($name = trim($name)) !== '' && !str_contains($name, '$')) {
                                $names[$name] = true;
                            }
                        }
                    }
                }
            }
        }

        ksort($names);

        return array_keys($names);
    }
}
