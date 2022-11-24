<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setting = new Setting([
            'data_per_page' => 10,
            'sales_order_limit' => 20,
            'mcp_deadline' => 25
        ]);
        $setting->save();
    }
}
