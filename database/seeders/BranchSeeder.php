<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Account;
use App\Models\Region;
use App\Models\Classification;
use App\Models\Area;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::pluck('id')->toArray();
        $regionIds = Region::pluck('id')->toArray();
        $classificationIds = Classification::pluck('id')->toArray();
        $areaIds = Area::pluck('id')->toArray();

        foreach ($accounts as $accountId) {
            Branch::factory()->count(rand(2, 5))->create([
                'account_id' => $accountId,
                'region_id' => !empty($regionIds) ? fake()->randomElement($regionIds) : null,
                'classification_id' => !empty($classificationIds) ? fake()->randomElement($classificationIds) : null,
                'area_id' => !empty($areaIds) ? fake()->randomElement($areaIds) : null,
            ]);
        }
    }
}
