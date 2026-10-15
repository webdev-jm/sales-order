<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $permissions = [
        'so cancel request create'  => 'access to request the cancelation of sales orders in the accounts assigned to the user',
        'so cancel request process' => 'access to process sales order cancel requests (SCM); users with it are notified of new requests',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['module' => 'SO Cancel Request', 'description' => $description]
            );
        }

        Role::where('name', 'superadmin')->first()?->givePermissionTo(array_keys($this->permissions));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::whereIn('name', array_keys($this->permissions))->delete();
    }
};
