<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountLogin;
use App\Models\User;
use App\Models\Account;

class AccountLoginSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $accountIds = Account::pluck('id')->toArray();

        if (empty($userIds) || empty($accountIds)) {
            return;
        }

        foreach ($userIds as $userId) {
            AccountLogin::factory()->count(rand(3, 8))->create([
                'user_id' => $userId,
                'account_id' => fake()->randomElement($accountIds),
            ]);
        }
    }
}
