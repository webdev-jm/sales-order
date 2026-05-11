<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityPlan;
use App\Models\User;

class ActivityPlanSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();

        if (empty($userIds)) {
            return;
        }

        ActivityPlan::factory()->count(20)->create([
            'user_id' => fake()->randomElement($userIds),
        ]);
    }
}
