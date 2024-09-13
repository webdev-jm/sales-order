<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PafExpenseType;

class PafExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $expense_arr = [
            'BUDGETED',
            'NON-BUDGETED',
            'ADJUSTED',
        ];

        foreach($expense_arr as $expense) {
            $expense = new PafExpenseType([
                'expense' => $expense
            ]);
            $expense->save();
        }
    }
}
