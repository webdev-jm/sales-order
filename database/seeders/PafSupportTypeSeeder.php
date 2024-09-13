<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PafSupportType;

class PafSupportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $support_arr = [
            'RPS CORRECTION',
            'NON TMP',
            'TMP',
            'ADDITIONAL DISPLAY',
        ];

        foreach($support_arr as $support) {
            $support = new PafSupportType([
                'support' => $support
            ]);
            $support->save();
        }
    }
}
