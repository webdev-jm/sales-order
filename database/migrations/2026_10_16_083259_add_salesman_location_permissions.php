<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The Salesman Locations routes and menu check "salesman location *", but the seeder used to
 * create "salesmen location *", so the module was 403 for everyone. This adds the names the
 * routes check and gives them to every role that already held the old spelling.
 */
return new class extends Migration
{
    /**
     * @var array<string, array{old: string, description: string}>
     */
    private array $permissions = [
        'salesman location access' => ['old' => 'salesmen location access', 'description' => 'access to salesman location module'],
        'salesman location create' => ['old' => 'salesmen location create', 'description' => 'access to create salesman location'],
        'salesman location edit'   => ['old' => 'salesmen location edit', 'description' => 'access to edit salesman location'],
        'salesman location delete' => ['old' => 'salesmen location delete', 'description' => 'access to delete salesman location'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $name => $permission) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['module' => 'Salesman Location', 'description' => $permission['description']]
            );

            Role::whereHas('permissions', fn ($query) => $query->where('name', $permission['old']))
                ->get()
                ->each(fn (Role $role) => $role->givePermissionTo($name));
        }

        Role::where('name', 'superadmin')->first()?->givePermissionTo(array_keys($this->permissions));
    }

    /**
     * Reverse the migrations.
     *
     * Left empty on purpose: a database may already have had these names before this
     * migration ran, and deleting them would take the module away from those roles.
     */
    public function down(): void
    {
    }
};
