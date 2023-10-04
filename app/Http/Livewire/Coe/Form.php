<?php

namespace App\Http\Livewire\Coe;

use Livewire\Component;

use Illuminate\Support\Facades\Session;
use App\Helpers\BranchSalesHelper;

use App\Models\Paf;
use App\Models\PafDetail;
use App\Models\ChannelOperation;
use App\Models\ChannelOperationMerchUpdate;
use App\Models\ChannelOperationTradeDisplay;
use App\Models\ChannelOperationTradeMarketingActivity;
use App\Models\ChannelOperationTradeMarketingActivitySku;
use App\Models\ChannelOperationDisplayRental;
use App\Models\ChannelOperationExtraDisplay;
use App\Models\ChannelOperationCompetetiveReport;

class Form extends Component
{

    public $logged_branch;
    public $stage;
    public $average_sales;
    public $paf_number, $paf, $paf_skus;
    public $total_findings;
    public $status;

    public $coe_reports;
    public $merch_updates;
    public $trade_displays;
    public $trade_marketing_activities;
    public $display_rentals;
    public $extra_displays;
    public $competetive_reports;

    public $merch_status_arr = ['ON BOARD', 'VACANT'];
    public $planogram_arr = ['IMPLEMENTED', 'NOT IMPLEMENTED'];
    public $bevi_pricing_arr = ['FOLLOW SRP', 'NOT FOLLOW SRP'];
    public $dra_status_arr = ['IMPLEMENTED', 'NOT IMPLEMENTED', 'NONE'];
    public $pafs_data;

    public $coe_id;

    public function finalize() {
        $this->status = 'finalized';

        $channel_operation = ChannelOperation::where('branch_login_id', $this->logged_branch->id)
                ->first();

        $channel_operation->update([
            'status' => 'finalized'
        ]);

        $this->coe_id = $channel_operation->id;

        $this->emit('setSignout');
    }

    public function removeLine($key) {
        unset($this->competetive_reports[$key]);
    }

    public function addLine() {
        $this->competetive_reports[] = [
            'company_name' => '',
            'product_description' => '',
            'srp' => '',
            'type_of_promotion' => '',
            'impact_to_our_product' => '',
        ];

        $this->saveFormData();
    }

    public function next() {
        
        // CHECK STAGE
        switch($this->stage) {
            case 1: // COE REPORTS
                $this->validate([
                    'coe_reports.store_in_charge' => 'required',
                    'coe_reports.position' => 'required',
                ]);

                $this->stage++;
                break;

            case 2: // MERCH UPDATE
                $this->validate([
                    'merch_updates.status' => 'required',
                ]);

                if($this->merch_updates['status'] == 'ON BOARD') {
                    $this->validate([
                        'merch_updates.status' => [
                            'required'
                        ],
                        'merch_updates.actual' => [
                            'required'
                        ],
                        'merch_updates.target' => [
                            'required'
                        ],
                        'merch_updates.remarks' => [
                            'required'
                        ],
                    ]);
                } else if($this->merch_updates['status'] == 'VACANT') {
                    $this->validate([
                        'merch_updates.status' => [
                            'required'
                        ],
                        'merch_updates.actual' => [
                            'required'
                        ],
                        'merch_updates.target' => [
                            'required'
                        ],
                        'merch_updates.days_of_gaps' => [
                            'required'
                        ],
                        'merch_updates.sales_opportunities' => [
                            'required'
                        ],
                        'merch_updates.remarks' => [
                            'required'
                        ],
                    ]);
                }

                $this->stage++;
                break;

            case 3: // TRADE DISPLAY
                $this->validate([
                    'trade_displays.planogram' => 'required',
                    'trade_displays.bevi_pricing' => 'required',
                    'trade_displays.osa_bath_actual' => 'required',
                    'trade_displays.osa_bath_target' => 'required',
                    'trade_displays.osa_face_actual' => 'required',
                    'trade_displays.osa_face_target' => 'required',
                    'trade_displays.osa_body_actual' => 'required',
                    'trade_displays.osa_body_target' => 'required',
                    'trade_displays.remarks' => 'required',
                ]);

                $this->stage++;
                break;

            case 4: // TRADE MARKETING ACTIVITIES
                if(!empty($pafs_data)) {
                    if((!empty($this->paf_number) && $this->paf_number == 'NONE') || (!empty($this->trade_marketing_activities['paf_number']) && $this->trade_marketing_activities['paf_number'] == 'NONE')) {
                        $this->validate([
                            'trade_marketing_activities.remarks' => 'required',
                        ]);
    
                        $this->paf_number = 'NONE';
                        $this->trade_marketing_activities['paf_number'] = 'NONE';
                    } else {
                        $this->validate([
                            'trade_marketing_activities.paf_number' => 'required',
                            'trade_marketing_activities.remarks' => 'required',
                            'trade_marketing_activities.skus.*.actual' => 'required',
                            'trade_marketing_activities.skus.*.target_maxcap' => 'required',
                        ]);
                    }
                }
                
                $this->stage++;
                break;

            case 5: // DISPLAY RENTALS
                $this->validate([
                    'display_rentals.status' => 'required',
                    'display_rentals.location' => 'required',
                    'display_rentals.stocks_displayed' => 'required',
                    'display_rentals.remarks' => 'required',
                ]);

                $this->stage++;
                break;
            
            case 6: // EXTRA DISPLAY
                $this->validate([
                    'extra_displays.location' => 'required',
                    'extra_displays.rate_per_month' => 'required',
                    'extra_displays.amount' => 'required',
                ]);

                if(empty($this->competetive_reports)) {
                    $this->competetive_reports[] = [
                        'company_name' => '',
                        'product_description' => '',
                        'srp' => '',
                        'type_of_promotion' => '',
                        'impact_to_our_product' => '',
                    ];
                }

                $this->stage++;
                break;

            case 7: // COMPETETIVE REPORTS
                $this->validate([
                    'competetive_reports.*.company_name' => 'required',
                    'competetive_reports.*.product_description' => 'required',
                    'competetive_reports.*.srp' => 'required',
                    'competetive_reports.*.type_of_promotion' => 'required',
                    'competetive_reports.*.impact_to_our_product' => 'required',
                    'total_findings' => 'required',
                ]);

                $this->save();
                $this->stage++;
                break;
        }

        $this->saveFormData();
    }

    public function prev() {
        $this->stage = $this->stage - 1;

        $this->saveFormData();
    }

    public function save() {
        // COE REPORTS
            // check if already exist
            $channel_operation = ChannelOperation::where('branch_login_id', $this->logged_branch->id)
                ->first();
            if(empty($channel_operation)) {
                $channel_operation = new ChannelOperation([
                    'branch_login_id' => $this->logged_branch->id,
                    'date' => date('Y-m-d', strtotime($this->logged_branch->time_in)),
                    'store_in_charge' => $this->coe_reports['store_in_charge'],
                    'position' => $this->coe_reports['position'],
                    'total_findings' => $this->total_findings,
                    'status' => 'draft',
                ]);
                $channel_operation->save();
            } else { // update
                $channel_operation->update([
                    'date' => date('Y-m-d', strtotime($this->logged_branch->time_in)),
                    'store_in_charge' => $this->coe_reports['store_in_charge'],
                    'position' => $this->coe_reports['position'],
                    'total_findings' => $this->total_findings,
                    'status' => 'draft',
                ]);
            }
        // 

        // MERCH UPDATES
            $merch_updates = ChannelOperationMerchUpdate::where('channel_operation_id', $channel_operation->id)
                ->first();
            if(empty($merch_updates)) {
                $merch_updates = new ChannelOperationMerchUpdate([
                    'channel_operation_id' => $channel_operation->id,
                    'status' => $this->merch_updates['status'],
                    'actual' => $this->merch_updates['actual'],
                    'target' => $this->merch_updates['target'],
                    'days_of_gaps' => $this->merch_updates['days_of_gaps'] ?? 0,
                    'sales_opportunities' => str_replace(',', '', $this->merch_updates['sales_opportunities'] ?? 0),
                    'remarks' => $this->merch_updates['remarks'],
                ]);
                $merch_updates->save();
            } else { // update
                $merch_updates->update([
                    'status' => $this->merch_updates['status'],
                    'actual' => $this->merch_updates['actual'],
                    'target' => $this->merch_updates['target'],
                    'days_of_gaps' => $this->merch_updates['days_of_gaps'] ?? 0,
                    'sales_opportunities' => str_replace(',', '', $this->merch_updates['sales_opportunities'] ?? 0),
                    'remarks' => $this->merch_updates['remarks'],
                ]);
            }
        // 

        // TRADE DISPLAY
            $trade_display = ChannelOperationTradeDisplay::where('channel_operation_id', $channel_operation->id)
                ->first();
            if(empty($trade_display)) {
                $trade_display = new ChannelOperationTradeDisplay([
                    'channel_operation_id' => $channel_operation->id,
                    'planogram' => $this->trade_displays['planogram'],
                    'bevi_pricing' => $this->trade_displays['bevi_pricing'],
                    'osa_bath_actual' => $this->trade_displays['osa_bath_actual'],
                    'osa_bath_target' => $this->trade_displays['osa_bath_target'],
                    'osa_face_actual' => $this->trade_displays['osa_face_actual'],
                    'osa_face_target' => $this->trade_displays['osa_face_target'],
                    'osa_body_actual' => $this->trade_displays['osa_body_actual'],
                    'osa_body_target' => $this->trade_displays['osa_body_target'],
                    'remarks' => $this->trade_displays['remarks'],
                ]);
                $trade_display->save();
            } else { // update
                $trade_display->update([
                    'planogram' => $this->trade_displays['planogram'],
                    'bevi_pricing' => $this->trade_displays['bevi_pricing'],
                    'osa_bath_actual' => $this->trade_displays['osa_bath_actual'],
                    'osa_bath_target' => $this->trade_displays['osa_bath_target'],
                    'osa_face_actual' => $this->trade_displays['osa_face_actual'],
                    'osa_face_target' => $this->trade_displays['osa_face_target'],
                    'osa_body_actual' => $this->trade_displays['osa_body_actual'],
                    'osa_body_target' => $this->trade_displays['osa_body_target'],
                    'remarks' => $this->trade_displays['remarks'],
                ]);
            }
        //

        // TRADE MARKETING ACTIVITIES
            if(!empty($this->pafs_data->count())) {
                $trade_marketing_activity = ChannelOperationTradeMarketingActivity::where('channel_operation_id', $channel_operation->id)
                    ->first();
                if(empty($trade_marketing_activity)) {
                    $trade_marketing_activity = new ChannelOperationTradeMarketingActivity([
                        'channel_operation_id' => $channel_operation->id,
                        'paf_number' => $this->trade_marketing_activities['paf_number'] ?? '',
                        'remarks' => $this->trade_marketing_activities['remarks'],
                    ]);
                    $trade_marketing_activity->save();
                } else { // update
                    $trade_marketing_activity->update([
                        'paf_number' => $this->trade_marketing_activities['paf_number'] ?? '',
                        'remarks' => $this->trade_marketing_activities['remarks'],
                    ]);
                }
    
                // SKUs
                $trade_marketing_activity->skus()->forceDelete();
                if(!empty($this->trade_marketing_activities['skus']) && $this->trade_marketing_activities['paf_number'] != 'NONE') {
                    foreach($this->trade_marketing_activities['skus'] as $sku => $sku_data) {
                        $sku = PafDetail::find($sku);
    
                        $trade_marketing_activity_sku = new ChannelOperationTradeMarketingActivitySku([
                            'channel_operation_trade_marketing_activity_id' => $trade_marketing_activity->id,
                            'paf_detail_id' => $sku->id,
                            'sku_code' => $sku->sku_code,
                            'sku_description' => $sku->sku_description,
                            'brand' => $sku->brand,
                            'actual' => $sku_data['actual'] ?: 0,
                            'target_maxcap' => $sku_data['target_maxcap'] ?: 0,
                        ]);
                        $trade_marketing_activity_sku->save();
                    }
                }
            }
        //

        // DISPLAY RENTALS
            $display_rental = ChannelOperationDisplayRental::where('channel_operation_id', $channel_operation->id)
                ->first();
            if(empty($display_rental)) {
                $display_rental = new ChannelOperationDisplayRental([
                    'channel_operation_id' => $channel_operation->id,
                    'status' => $this->display_rentals['status'],
                    'location' => $this->display_rentals['location'],
                    'stocks_displayed' => $this->display_rentals['stocks_displayed'],
                    'remarks' => $this->display_rentals['remarks'],
                ]);
                $display_rental->save();
            } else { // update
                $display_rental->update([
                    'status' => $this->display_rentals['status'],
                    'location' => $this->display_rentals['location'],
                    'stocks_displayed' => $this->display_rentals['stocks_displayed'],
                    'remarks' => $this->display_rentals['remarks'],
                ]);
            }
        // 

        // EXTRA DISPLAYS
            $extra_display = ChannelOperationExtraDisplay::where('channel_operation_id', $channel_operation->id)
                ->first();
            if(empty($extra_display)) {
                $extra_display = new ChannelOperationExtraDisplay([
                    'channel_operation_id' => $channel_operation->id,
                    'location' => $this->extra_displays['location'],
                    'rate_per_month' => $this->extra_displays['rate_per_month'],
                    'amount' => $this->extra_displays['amount'],
                ]);
                $extra_display->save();
            } else { // update
                $extra_display->update([
                    'location' => $this->extra_displays['location'],
                    'rate_per_month' => $this->extra_displays['rate_per_month'],
                    'amount' => $this->extra_displays['amount']
                ]);
            }
        // 

        // COMPETETIVE REPORTS
            $channel_operation->competetive_reports()->forceDelete();
            foreach($this->competetive_reports as $report) {
                $competetive_report = new ChannelOperationCompetetiveReport([
                    'channel_operation_id' => $channel_operation->id,
                    'company_name' => $report['company_name'],
                    'product_description' => $report['product_description'],
                    'srp' => $report['srp'],
                    'type_of_promotion' => $report['type_of_promotion'],
                    'impact_to_our_product' => $report['impact_to_our_product'],
                ]);
                $competetive_report->save();
            }
        // 
    }

    public function updatedPafNumber() {
        if(!empty($this->paf_number)) {
            $this->reset([
                'paf',
                'paf_skus',
            ]);

            unset($this->trade_marketing_activities['skus']);

            $this->paf = Paf::where('PAFNo', $this->paf_number)->first();
            if(!empty($this->paf)) {
                $this->paf_skus = PafDetail::where('PAFNo', $this->paf->PAFNo)->get();

                $this->trade_marketing_activities['paf_number'] = $this->paf_number;
                $this->trade_marketing_activities['title'] = $this->paf->title;
                $this->trade_marketing_activities['start_date'] = $this->paf->start_date;
                $this->trade_marketing_activities['end_date'] = $this->paf->end_date;
                $this->trade_marketing_activities['type'] = $this->paf->support_type;

                foreach($this->paf_skus as $sku) {
                    $this->trade_marketing_activities['skus'][$sku->id] = [
                        'sku_code' => $sku->sku_code,
                        'sku_description' => $sku->sku_description,
                        'brand' => $sku->brand,
                        'actual' => '',
                        'target_maxcap' => '',
                    ];
                }
            }
        }
    }

    public function computeSales() {
        $avg = 0;
        if(!empty($this->average_sales) && !empty($this->merch_updates['days_of_gaps'])) {
            $avg = ($this->average_sales / 30) * $this->merch_updates['days_of_gaps'];
        }
        $this->merch_updates['sales_opportunities'] = number_format($avg, 2);

        $this->saveFormData();
    }

    private function saveFormData() {
        $coe_form_data[$this->logged_branch->id] = [
            'stage' => $this->stage,
            'logged_branch' => $this->logged_branch,
            'coe_reports' => $this->coe_reports,
            'merch_updates' => $this->merch_updates,
            'trade_displays' => $this->trade_displays,
            'trade_marketing_activities' => $this->trade_marketing_activities,
            'display_rentals' => $this->display_rentals,
            'extra_displays' => $this->extra_displays,
            'competetive_reports' => $this->competetive_reports,
            'total_findings' => $this->total_findings,
            'status' => $this->status,
            'coe_id' => $this->coe_id,
        ];

        Session::put('coe_form_data', $coe_form_data);
    }

    private function loadFormData()
    {
        $coe_form_data = Session::get('coe_form_data');
        if (!empty($coe_form_data) && isset($coe_form_data[$this->logged_branch->id])) {
            $data = $coe_form_data[$this->logged_branch->id];
            $this->stage = $data['stage'];
            $this->coe_reports = $data['coe_reports'];
            $this->merch_updates = $data['merch_updates'];
            $this->trade_displays = $data['trade_displays'];
            $this->trade_marketing_activities = $data['trade_marketing_activities'];
            $this->display_rentals = $data['display_rentals'];
            $this->extra_displays = $data['extra_displays'];
            $this->competetive_reports = $data['competetive_reports'];
            $this->total_findings = $data['total_findings'];
            $this->status = $data['status'] ?? 'draft';
            $this->coe_id = $data['coe_id'] ?? NULL;

            if(empty($this->coe_id)) {
                $channel_operation = ChannelOperation::where('branch_login_id', $this->logged_branch->id)
                ->first();
                if(!empty($channel_operation)) {
                    $this->coe_id = $channel_operation->id;
                }
            }
            
            if(!empty($this->trade_marketing_activities['paf_number']) && $this->trade_marketing_activities['paf_number'] != 'NONE') {
                $this->paf = Paf::where('PAFNo', $this->trade_marketing_activities['paf_number'])
                    ->first();
                if(!empty($this->paf)) {
                    $this->paf_skus = PafDetail::where('PAFNo', $this->paf->PAFNo)
                        ->get();
                }
    
                $this->paf_number = $this->trade_marketing_activities['paf_number'];
            } else {
                $this->paf_number = 'NONE';
                $this->trade_marketing_activities['paf_number'] = 'NONE';
            }
        } else {
            $this->coe_reports = [
                'date' => date('Y-m-d', strtotime($this->logged_branch->time_in)),
                'name' => strtoupper($this->logged_branch->user->fullName()),
                'account_name' => $this->logged_branch->branch->account->short_name,
                'branch_code' => $this->logged_branch->branch->branch_code,
                'branch_name' => $this->logged_branch->branch->branch_name,
            ];

            $channel_operation = ChannelOperation::where('branch_login_id', $this->logged_branch->id)
                ->first();
            if(!empty($channel_operation)) {
                $this->coe_reports['store_in_charge'] = $channel_operation->store_in_charge;
                $this->coe_reports['position'] = $channel_operation->position;
                $this->total_findings = $channel_operation->total_findings;
                $this->status = $channel_operation->status;
                $this->coe_id = $channel_operation->id;

                // MERCH UPDATES
                $merch_update = $channel_operation->merch_updates()->first();
                if(!empty($merch_update)) {
                    $this->merch_updates = [
                        'status' => $merch_update->status,
                        'actual' => $merch_update->actual,
                        'target' => $merch_update->target,
                        'days_of_gaps' => $merch_update->days_of_gaps,
                        'sales_opportunities' => $merch_update->sales_opportunities,
                        'remarks' => $merch_update->remarks,
                    ];
                }

                // TRADE DISPLAYS
                $trade_display = $channel_operation->trade_displays()->first();
                if(!empty($trade_display)) {
                    $this->trade_displays = [
                        'planogram' => $trade_display->planogram,
                        'bevi_pricing' => $trade_display->bevi_pricing,
                        'osa_bath_actual' => $trade_display->osa_bath_actual,
                        'osa_bath_target' => $trade_display->osa_bath_target,
                        'osa_face_actual' => $trade_display->osa_bath_actual,
                        'osa_face_target' => $trade_display->osa_bath_target,
                        'osa_body_actual' => $trade_display->osa_bath_actual,
                        'osa_body_target' => $trade_display->osa_bath_target,
                        'remarks' => $trade_display->remarks,
                    ];
                }

                // TRADE MARKETING ACTIVITIES
                $trade_marketing_activity = $channel_operation->trade_marketing_activities()->first();
                if(!empty($trade_marketing_activity) && $trade_marketing_activity->paf_number != 'NONE') {

                    $this->paf = Paf::where('PAFNo', $trade_marketing_activity->paf_number)
                        ->first();
                    $this->paf_skus = PafDetail::where('PAFNo', $this->paf->PAFNo)
                        ->get();

                    $this->paf_number = $trade_marketing_activity->paf_number;

                    $this->trade_marketing_activities = [
                        'paf_number' => $trade_marketing_activity->paf_number,
                        'remarks' => $trade_marketing_activity->remarks,
                    ];

                    // SKU
                    $sku_data = [];
                    foreach($trade_marketing_activity->skus as $sku) {
                        $sku_data[$sku->paf_detail_id] = [
                            'sku_code' => $sku['sku_code'],
                            'sku_description' => $sku['sku_description'],
                            'brand' => $sku['brand'],
                            'actual' => $sku->actual,
                            'target_maxcap' => $sku->target_maxcap,
                        ];
                    }

                    $this->trade_marketing_activities['skus'] = $sku_data;
                }

                // DISPLAY RENTALS
                $display_rental = $channel_operation->display_rentals()->first();
                if(!empty($display_rental)) {
                    $this->display_rentals = [
                        'status' => $display_rental->status,
                        'location' => $display_rental->location,
                        'stocks_displayed' => $display_rental->stocks_displayed,
                        'remarks' => $display_rental->remarks,
                    ];
                }

                // EXTRA DISPLAYS
                $extra_display = $channel_operation->extra_displays()->first();
                if(!empty($extra_display)) {
                    $this->extra_displays = [
                        'location' => $extra_display->location,
                        'rate_per_month' => $extra_display->rate_per_month,
                        'amount' => $extra_display->amount,
                    ];
                }

                // COMPETETIVE REPORTS
                $competetive_reports = $channel_operation->competetive_reports;
                foreach($competetive_reports as $competetive_report) {
                    $this->competetive_reports[] = [
                        'company_name' => $competetive_report->company_name,
                        'product_description' => $competetive_report->product_description,
                        'srp' => $competetive_report->srp,
                        'type_of_promotion' => $competetive_report->type_of_promotion,
                        'impact_to_our_product' => $competetive_report->impact_to_our_product,
                    ];
                }
                
            }
        }
    }

    public function mount($logged_branch) {
        $this->logged_branch = $logged_branch;
        $this->stage = 1;

        $this->average_sales = BranchSalesHelper::getAverageSales($this->logged_branch->branch->branch_code, date('Y'));

        $date = date('Y-m-d', strtotime($this->logged_branch->time_in));

        $this->pafs_data = Paf::where('account_code', $this->logged_branch->branch->account->account_code)
            ->where(function($query) use($date) {
                $query->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date);
            })
            ->get();
        
        $this->loadFormData();
    }

    public function render()
    {
        // Session::forget('coe_form_data');
        return view('livewire.coe.form');
    }
}
