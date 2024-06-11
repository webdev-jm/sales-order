<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\STTAccount;
use App\Models\STTArea;
use App\Models\STTRegion;
use App\Models\STTClassification;

use App\Models\Account;
use App\Models\Area;
use App\Models\Region;
use App\Models\Classification;

use Illuminate\Support\Facades\Session;

class UpdateBranches implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $errors;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->errors = array();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $db_sms = 'sms_db';
        $db_sto = 'sto_db';
            
        try {

            $accounts = STTAccount::get()->keyBy('id');
            $regions = STTRegion::get()->keyBy('id');
            $classifications = STTClassification::get()->keyBy('id');
            $areas = STTARea::get()->keyBy('id');

            do {
                // get branches to insert
                DB::table($db_sto . '.branches as sto_b')
                    ->leftJoin($db_sms.'.branches as sms_b', function ($join) {
                        $join->whereRaw('sto_b.branch_code = sms_b.branch_code COLLATE utf8_general_ci');
                    })
                    ->whereNull('sms_b.branch_code')
                    ->select('sms_b.branch_code', 'sto_b.*')
                    ->whereNotNull('sto_b.account_id')
                    ->whereNotNull('sto_b.area_id')
                    ->whereNotNull('sto_b.region_id')
                    ->whereNotNull('sto_b.classification_id')
                    ->orderBy('sto_b.branch_code', 'ASC')
                    ->chunk(200, function ($results) use($accounts, $regions, $classifications, $areas) {
                        if (!$results->isEmpty()) {

                            foreach ($results as $result) {
                                $err_arr = [];

                                // check branch data
                                // account
                                $account = $accounts->get($result->account_id);
                                if (!empty($account)) {
                                    // find account in SMS
                                    $sms_account = Account::where('account_code', $account->account_code)
                                        ->orWhere('short_name', $account->short_name)
                                        ->orWhere('account_name', $account->account_name)
                                        ->first();

                                    if (empty($sms_account)) {
                                        $err_arr['sms_account'] = $account->account_code;
                                    }
                                } else {
                                    $err_arr['sto_account'] = $result->account_id;
                                }

                                // region
                                $region = $regions->get($result->region_id);
                                if (!empty($region)) {
                                    $sms_region = Region::where('region_name', $region->region_name)
                                        ->first();
                                    if (empty($sms_region)) {
                                        $err_arr['sms_region'] = $region->region_name;
                                    }
                                } else {
                                    $err_arr['sto_region'] = $result->region_id;
                                }

                                // classification/channel
                                $classification = $classifications->get($result->classification_id);
                                if (!empty($classification)) {
                                    $sms_classification = Classification::where('classification_name', $classification->classification_name)
                                        ->orWhere('classification_name', $classification->new_name)
                                        ->orWhere('classification_code', $classification->classification_code)
                                        ->orWhere('classification_code', $classification->new_code)
                                        ->first();
                                    if (empty($sms_classification)) {
                                        $err_arr['sms_classification'] = $classification->classification_code;
                                    }
                                } else {
                                    $err_arr['sto_classification'] = $result->classification_id;
                                }

                                // area
                                $area = $areas->get($result->area_id);
                                if (!empty($area)) {
                                    $sms_area = Area::where('area_name', $area->area_name)
                                        ->orWhere('area_code', $area->area_code)
                                        ->first();
                                    if (empty($sms_area)) {
                                        $err_arr['sms_area'] = $area->area_code;
                                    }
                                } else {
                                    $err_arr['sto_area'] = $result->area_id;
                                }

                                if(empty($err_arr)) {
                                    // create new branch
                                    $branch = new Branch([
                                        'account_id' => $sms_account->id,
                                        'region_id' => $sms_region->id,
                                        'classification_id' => $sms_classification->id,
                                        'area_id' => $sms_area->id,
                                        'branch_code' => $result->branch_code,
                                        'branch_name' => $result->branch_name,
                                    ]);
                                    $branch->save();
                                } else {
                                    $this->errors['errors'][] = $err_arr;
                                }
                            }
                        }
                    });

            } while (
                DB::table($db_sto . '.branches as sto_b')
                    ->leftJoin($db_sms.'.branches as sms_b', function ($join) {
                        $join->whereRaw('sto_b.branch_code = sms_b.branch_code COLLATE utf8_general_ci');
                    })
                    ->whereNull('sms_b.branch_code')
                    ->select('sms_b.branch_code', 'sto_b.*')
                    ->whereNotNull('sto_b.account_id')
                    ->whereNotNull('sto_b.area_id')
                    ->whereNotNull('sto_b.region_id')
                    ->whereNotNull('sto_b.classification_id')
                    ->orderBy('sto_b.branch_code', 'ASC')
                    ->count()
            );
        } catch(\Exception $e) {
            \Log::error($e);
        }
        
    }
}
