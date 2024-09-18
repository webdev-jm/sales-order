<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PafActivity;
use App\Models\Company;

class PafActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activity_arr = [
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'CONSUMER PROMO',
                'GlCode' => '600581',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'CONSUMER PROMO',
                'GlCode' => '600581',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'ADMIN FEES',
                'GlCode' => '600590',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'TMP',
                'components' => 'SALESMAN INCENTIVE',
                'activity' => '',
                'GlCode' => '600410',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'TMP',
                'components' => 'SALESMAN INCENTIVE',
                'activity' => '',
                'GlCode' => '600410',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'TMP',
                'components' => 'SUPPLY CHAIN',
                'activity' => '',
                'GlCode' => 'NULL',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'TMP',
                'components' => 'SUPPLY CHAIN',
                'activity' => '',
                'GlCode' => 'NULL',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'TMP',
                'components' => 'MARKETING MATERIAL',
                'activity' => '',
                'GlCode' => '600501',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'TMP',
                'components' => 'MANPOWER',
                'activity' => '',
                'GlCode' => '600301',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'TMP',
                'components' => 'MERCHANDISING',
                'activity' => '',
                'GlCode' => '600501',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'TMP',
                'components' => 'REBATES',
                'activity' => '',
                'GlCode' => '600120',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'TMP',
                'components' => 'PARTICIPATION FEE',
                'activity' => '',
                'GlCode' => '600110',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'TMP',
                'components' => 'MARKETING MATERIAL',
                'activity' => '',
                'GlCode' => '600501',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'TMP',
                'components' => 'MANPOWER',
                'activity' => '',
                'GlCode' => '600301',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'TMP',
                'components' => 'MERCHANDISING',
                'activity' => '',
                'GlCode' => '600501',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'TMP',
                'components' => 'REBATES',
                'activity' => '',
                'GlCode' => '600120',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'TMP',
                'components' => 'PARTICIPATION FEE',
                'activity' => '',
                'GlCode' => '600110',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'STORE OPENING SUPPORT',
                'GlCode' => '600010',
                'brand' => 0,
                'branch' => 1
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'ANNIVERSARY',
                'GlCode' => '600020',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'ANNUAL SUPPORT',
                'GlCode' => '600020',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'ADVERTISING SUPPORT',
                'GlCode' => '600030',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'SPONSORSHIP SUPPORT',
                'GlCode' => '600130',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'MEDIA TV ADS',
                'GlCode' => '600585',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'PRINT ADS AND OOH',
                'GlCode' => '600551',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'ADMIN FEES',
                'GlCode' => '600590',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'PRODUCT LIABILITY INSURANCE',
                'GlCode' => '600450',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'PORTAL CHARGES',
                'GlCode' => '600040',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'PRODUCT SAMPLES',
                'GlCode' => '600161',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'STORE OPENING SUPPORT',
                'GlCode' => '600010',
                'brand' => 0,
                'branch' => 1
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'ANNIVERSARY',
                'GlCode' => '600020',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'ANNUAL SUPPORT',
                'GlCode' => '600020',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'ADVERTISING SUPPORT',
                'GlCode' => '600030',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'SPONSORSHIP SUPPORT',
                'GlCode' => '600130',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'PRODUCT LIABILITY INSURANCE',
                'GlCode' => '600450',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'PORTAL CHARGES',
                'GlCode' => '600040',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ANNUAL SUPPORT',
                'components' => 'OTHER SUPPORT',
                'activity' => 'PRODUCT SAMPLES',
                'GlCode' => '600161',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'RPS CORRECTION',
                'components' => 'LISTING FEE',
                'activity' => '',
                'GlCode' => '600401',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'RPS CORRECTION',
                'components' => 'INTRO DISCOUNT',
                'activity' => '',
                'GlCode' => '600100',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'RPS CORRECTION',
                'components' => 'LISTING FEE',
                'activity' => '',
                'GlCode' => '600401',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'RPS CORRECTION',
                'components' => 'INTRO DISCOUNT',
                'activity' => '',
                'GlCode' => '600100',
                'brand' => 1,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ADDITIONAL DISPLAY',
                'components' => 'ADDITIONAL DISPLAY',
                'activity' => 'PRODUCT HIGHLIGHT',
                'GlCode' => '600151',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVI',
                'type' => 'ADDITIONAL DISPLAY',
                'components' => 'ADDITIONAL DISPLAY',
                'activity' => 'DISPLAY RENTAL ALLOWANCE',
                'GlCode' => '600201',
                'brand' => 0,
                'branch' => 0
            ],
            [
                'company' => 'BEVA',
                'type' => 'ADDITIONAL DISPLAY',
                'components' => 'ADDITIONAL DISPLAY',
                'activity' => 'PRODUCT HIGHLIGHT',
                'GlCode' => '600151',
                'brand' => 0,
                'branch' => 1
            ],
            [
                'company' => 'BEVA',
                'type' => 'ADDITIONAL DISPLAY',
                'components' => 'ADDITIONAL DISPLAY',
                'activity' => 'DISPLAY RENTAL ALLOWANCE',
                'GlCode' => '600201',
                'brand' => 0,
                'branch' => 1
            ]
        ];

        foreach($activity_arr as $activity) {
            $company = Company::where('name', $activity['company'])->first();
            if(!empty($company)) {
                $paf_activity = new PafActivity([
                    'company_id' => $company->id,
                    'type' => $activity['type'],
                    'components' => $activity['components'],
                    'activity' => $activity['activity'],
                    'GlCode' => $activity['GlCode'],
                    'tmg_approval' => $activity['branch'],
                    'brand_approval' => $activity['brand']
                ]);
                $paf_activity->save();
            }
        }

    }
}
