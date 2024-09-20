<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([PermissionSeeder::class]);
        $this->call([RoleSeeder::class]);
        $this->call([UserSeeder::class]);

        $this->call([CompanySeeder::class]);
        $this->call([InvoiceTermSeeder::class]);
        $this->call([SettingSeeder::class]);

        $this->call([AccountSeeder::class]);

        $this->call([PafSupportTypeSeeder::class]);
        $this->call([PafExpenseTypeSeeder::class]);
        $this->call([PafActivitySeeder::class]);
        $this->call([BrandSeeder::class]);
    }
}
