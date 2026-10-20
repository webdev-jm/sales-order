<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permissions the code checks that only PermissionSeeder created, so the live database never had them.
 * Saving the settings page checks "settings access" while opening it checks "settings", so every role that can
 * open the page is also allowed to save it.
 */
return new class extends Migration
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $permissions = [
        'Brand' => [
            'brand access' => 'access to brand module',
            'brand create' => 'access to create brand',
            'brand edit'   => 'access to edit brand',
            'brand delete' => 'access to delete brand',
        ],
        'Upload Templates' => [
            'upload template access' => 'access to upload templates',
            'upload template create' => 'access to create upload templates',
            'upload template edit'   => 'access to edit upload templates',
            'upload template delete' => 'access to delete upload templates',
        ],
        'Setting' => [
            'settings access' => 'access to save the settings page',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $module => $permissions) {
            foreach ($permissions as $name => $description) {
                Permission::firstOrCreate(
                    ['name' => $name],
                    ['module' => $module, 'description' => $description]
                );
            }
        }

        Role::whereHas('permissions', fn ($query) => $query->where('name', 'settings'))
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo('settings access'));

        Role::where('name', 'superadmin')->first()?->givePermissionTo(array_merge(...array_map('array_keys', array_values($this->permissions))));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', array_merge(...array_map('array_keys', array_values($this->permissions))))->delete();
    }
};
