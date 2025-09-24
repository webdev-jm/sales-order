<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CreditMemoReason;


class CreditMemoReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reasons_arr = [
            'RCC001' => 'CANCELLED BY CUSTOMER',
            'RCC002' => 'UNECONOMICAL TRIP',
            'RCC003' => 'FG AVAILABILITY',
            'RCC004' => 'SYSTEM SETUP',
            'RCC005' => 'EXCEEDED SO LINE COUNT',
            'RCC006' => 'COMPLIANCE ISSUE',
            'RCC007' => 'TRIP OPTIMIZATION',
            'RCC008' => 'OVER CREDIT LIMIT',
            'RCC009' => 'RESERVATION SO',
            'RCC010' => 'NO ACTUAL BO PULL OUT',
            'RCC011' => 'CHANGE IN SERVICING WAREHOUSE',
            'RCC012' => 'SYSTEM CUTOFF',
            'RCM001' => 'BAD STOCKS',
            'RCM002' => 'PRINTING ISSUES-3PL',
            'RCM003' => 'DISENGAGED ACCOUNT',
            'RCM004' => 'CHANGE OF ADDRESS',
            'RCM005' => 'ANNUAL INVENTORY',
            'RCM006' => 'WRONG/ SHORT SERVING',
            'RCM007' => 'OUT OF STOCKS',
            'RCM008' => 'LATE DELIVERY',
            'RCM009' => 'MANUFACTURING DEFECT',
            'RCM010' => 'EXPIRED/ NEAR EX FG',
            'RCM011' => 'DAMAGED BY TRUCKER',
            'RCM012' => 'UNREADABLE BARCODE',
            'RCM013' => 'WRONG ENCODING',
            'RCM014' => 'DOUBLE ENCODING',
            'RCM015' => 'EXPIRED PO',
            'RCM016' => 'PAYMENT NOT AVAILABLE',
            'RCM017' => 'WITHDRAWAL OF INITIAL STOCKING',
            'RCM018' => 'PRICE DIFFERENCE',
            'RCM019' => 'OVERSTOCKED',
            'RCM020' => 'RE-SO',
            'RCM021' => 'NO AVAILABLE FLEET',
            'RCM022' => 'CHANGE IN TERMS',
            'RCM023' => 'UNECONOMICAL',
            'RCM024' => 'FG FRESHNESS',
            'RCM025' => 'SYSTEM INTERFACED',
            'RCM026' => 'WRONG WAREHOUSE CODE',
            'RCM027' => 'DAMAGED IN TRANSIT',
            'RCM028' => 'WRONG DISCOUNT',
            'RCM029' => 'TPL DECLINED',
            'RCM030' => 'MISSING INVENTORY',
            'RCM031' => 'CUSTOMER ERROR',
        ];

        foreach($reasons_arr as $code => $description) {
            $reason = new CreditMemoReason([
                'reason_code' => $code,
                'reason_description' => $description
            ]);
            $reason->save();
        }
    }
}
