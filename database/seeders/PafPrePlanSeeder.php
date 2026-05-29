<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PafPrePlan;
use App\Models\PafPrePlanDetail;
use App\Models\Account;
use App\Models\PafSupportType;
use App\Models\PafActivity;

class PafPrePlanSeeder extends Seeder
{
    public function run(): void
    {
        $accountIds = Account::pluck('id')->toArray();
        $supportTypeIds = PafSupportType::pluck('id')->toArray();
        $activityIds = PafActivity::pluck('id')->toArray();

        if (empty($accountIds) || empty($supportTypeIds) || empty($activityIds)) {
            return;
        }

        $prePlans = [
            [
                'pre_plan_number' => 'PP-2024-001',
                'year' => 2024,
                'start_date' => '2024-01-01',
                'end_date' => '2024-03-31',
                'title' => 'Q1 Consumer Promo Plan',
                'concept' => 'Drive sales volume through in-store promotions and consumer engagement.',
            ],
            [
                'pre_plan_number' => 'PP-2024-002',
                'year' => 2024,
                'start_date' => '2024-04-01',
                'end_date' => '2024-06-30',
                'title' => 'Q2 Brand Activation Plan',
                'concept' => 'Increase brand visibility via display and merchandising activities.',
            ],
            [
                'pre_plan_number' => 'PP-2024-003',
                'year' => 2024,
                'start_date' => '2024-07-01',
                'end_date' => '2024-09-30',
                'title' => 'Q3 Anniversary Promo Support',
                'concept' => 'Support key accounts during their anniversary sale period.',
            ],
        ];

        foreach ($prePlans as $planData) {
            $prePlan = new PafPrePlan([
                'account_id' => fake()->randomElement($accountIds),
                'paf_support_type_id' => fake()->randomElement($supportTypeIds),
                'paf_activity_id' => fake()->randomElement($activityIds),
                'pre_plan_number' => $planData['pre_plan_number'],
                'year' => $planData['year'],
                'start_date' => $planData['start_date'],
                'end_date' => $planData['end_date'],
                'title' => $planData['title'],
                'concept' => $planData['concept'],
            ]);
            $prePlan->save();

            foreach (range(1, rand(2, 4)) as $i) {
                $detail = new PafPrePlanDetail([
                    'paf_pre_plan_id' => $prePlan->id,
                    'type' => fake()->randomElement(['TMP', 'ANNUAL SUPPORT', 'ADDITIONAL DISPLAY']),
                    'components' => fake()->randomElement(['MANPOWER', 'MARKETING MATERIAL', 'REBATES', 'OTHER SUPPORT']),
                    'branch' => fake()->randomElement(['MANILA', 'CEBU', 'DAVAO']),
                    'stock_code' => strtoupper(fake()->bothify('??###')),
                    'description' => fake()->words(3, true),
                    'price_code' => strtoupper(fake()->bothify('PC-##')),
                    'brand' => fake()->randomElement(['MAGNOLIA', 'PEPSI', 'SEVEN-UP']),
                    'quantity' => fake()->numberBetween(10, 500),
                    'GlCode' => fake()->randomElement(['600120', '600301', '600501', '600581']),
                    'IO' => fake()->bothify('IO-####'),
                    'amount' => fake()->randomFloat(2, 1000, 100000),
                ]);
                $detail->save();
            }
        }
    }
}
