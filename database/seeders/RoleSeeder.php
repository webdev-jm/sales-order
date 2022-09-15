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

        Role::create(['name' => 'superadmin'])->givePermissionTo(Permission::all());
        Role::create(['name' => 'user'])->givePermissionTo([
            'sales order access',
            'sales order create',
            'sales order edit',
            'sales order delete',
        ]);
    }
}
