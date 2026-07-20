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

    protected function seedPermissionsAndRoles(): void
    {
        if (Permission::count() === 0) {
            $this->seed(PermissionSeeder::class);
        }

        if (Role::count() === 0) {
            $this->seed(RoleSeeder::class);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
