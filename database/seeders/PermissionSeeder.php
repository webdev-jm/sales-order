<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
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

        $permissions_arr = [
            // sales order
            'sales order access',
            'sales order create',
            'sales order edit',
            'sales order delete',
            // company
            'company access',
            'company create',
            'company edit',
            'company delete',
            // discounts
            'discount access',
            'discount create',
            'discount edit',
            'discount delete',
            // accounts
            'account access',
            'account create',
            'account edit',
            'account delete',
            // Branches
            'branch access',
            'branch create',
            'branch edit',
            'branch delete',
            // Invoice Terms
            'invoice term access',
            'invoice term create',
            'invoice term edit',
            'invoice term delete',
            // Products
            'product access',
            'product create',
            'product edit',
            'product delete',
            // Price Code
            'price code access',
            'price code create',
            'price code edit',
            'price code delete',
            // sales people
            'sales people access',
            'sales person create',
            'sales person edit',
            'sales person delete',
            // users
            'user access',
            'user create',
            'user upload',
            'user edit',
            'user delete',
            // roles
            'role access',
            'role create',
            'role edit',
            'role delete',
            // Settings
            'settings access',
        ];

        foreach($permissions_arr as $permissions) {
            Permission::create(['name' => $permissions]);
        }
    }
}
