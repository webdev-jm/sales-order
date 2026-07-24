<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // "report restricted" is an opt-in restriction rather than a capability,
        // so it is deliberately withheld from the all-access superadmin default.
        Role::create(['name' => 'superadmin'])->givePermissionTo(
            Permission::where('name', '!=', 'report restricted')->get()
        );
        Role::create(['name' => 'user'])->givePermissionTo([
            'sales order access',
            'sales order create',
            'sales order edit',
            'sales order delete',
        ]);
    }
}
