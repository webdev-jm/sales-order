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

        $this->call([CreditMemoReasonSeeder::class]);

        $this->call([CategorySeeder::class]);
        $this->call([RemittanceReasonSeeder::class]);
        $this->call([UploadTemplateSeeder::class]);

        $this->call([BranchSeeder::class]);
        $this->call([ProductSeeder::class]);
        $this->call([PriceCodeSeeder::class]);
        $this->call([AccountLoginSeeder::class]);
        $this->call([SalesOrderSeeder::class]);
        $this->call([PPUFormSeeder::class]);
        $this->call([CreditMemoSeeder::class]);
        $this->call([InvoiceSeeder::class]);
        $this->call([RemittanceSeeder::class]);
        $this->call([ActivityPlanSeeder::class]);
        $this->call([WeeklyActivityReportSeeder::class]);
    }
}
