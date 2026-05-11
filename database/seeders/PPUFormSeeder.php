<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PPUForm;
use App\Models\PPUFormItem;
use App\Models\AccountLogin;

class PPUFormSeeder extends Seeder
{
    public function run(): void
    {
        $accountLoginIds = AccountLogin::pluck('id')->toArray();

        if (empty($accountLoginIds)) {
            return;
        }

        PPUForm::factory()->count(10)->create([
            'account_login_id' => fake()->randomElement($accountLoginIds),
        ])->each(function (PPUForm $form) {
            PPUFormItem::factory()->count(rand(1, 5))->create([
                'ppuform_id' => $form->id,
            ]);
        });
    }
}
