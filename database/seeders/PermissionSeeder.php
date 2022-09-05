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
            // discounts
            'discount access',
            'discount create',
            'discount edit',
            'discount delete',
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
        ];

        foreach($permissions_arr as $permissions) {
            Permission::create(['name' => $permissions]);
        }
    }
}
