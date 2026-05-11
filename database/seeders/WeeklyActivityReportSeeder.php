<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WeeklyActivityReport;
use App\Models\User;
use App\Models\Area;

class WeeklyActivityReportSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $areaIds = Area::pluck('id')->toArray();

        if (empty($userIds)) {
            return;
        }

        WeeklyActivityReport::factory()->count(20)->create([
            'user_id' => fake()->randomElement($userIds),
            'area_id' => !empty($areaIds) ? fake()->randomElement($areaIds) : null,
        ]);
    }
}
