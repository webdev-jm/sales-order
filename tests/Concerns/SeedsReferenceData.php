<?php

namespace Tests\Concerns;

use App\Models\Setting;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the reference data a test needs, but only when it is missing.
 *
 * These tests run inside a transaction against a shared database rather than
 * rebuilding it, so the seeders (which use create(), not firstOrCreate()) must
 * not run against rows that are already there.
 */
trait SeedsReferenceData
{
    protected function seedSettings(): void
    {
        if (Setting::count() === 0) {
            $this->seed(SettingSeeder::class);
        }
    }

    /**
     * Migrations create a few permissions of their own, so "any permission exists" does not
     * mean the seeder ran. Both seeders are idempotent, so they run whenever a core
     * permission or the superadmin role is missing.
     */
    protected function seedPermissionsAndRoles(): void
    {
        $seeded = Permission::where('name', 'sales order access')->exists()
            && Role::where('name', 'superadmin')->exists();

        if (!$seeded) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RoleSeeder::class);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
