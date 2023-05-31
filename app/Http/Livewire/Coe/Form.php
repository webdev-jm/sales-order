<?php

namespace App\Http\Livewire\Coe;

use Livewire\Component;

use Illuminate\Support\Facades\Session;

class Form extends Component
{
    public $logged_branch;
    public $stage;

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
                    'coe_reports.coe_date' => 'required',
                ]);

                $this->stage++;
                break;

            case 2: // MERCH UPDATE
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

                $this->stage++;
                break;

            case 3: // TRADE MARKETING ACTIVITIES
                $this->validate([
                    'trade_displays.planogram' => 'required',
                    'trade_displays.bevi_pricing' => 'required',
                    'trade_displays.osa_bath_actual' => 'required',
                    'trade_displays.osa_bath_total' => 'required',
                    'trade_displays.osa_face_actual' => 'required',
                    'trade_displays.osa_face_total' => 'required',
                    'trade_displays.osa_body_actual' => 'required',
                    'trade_displays.osa_body_total' => 'required',
                    'trade_displays.remarks' => 'required',
                ]);

                $this->stage++;
                break;

            case 4: // TRADE MARKETING ACTIVITIES
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
                ]);


                break;
        }

        $this->saveFormData();
    }

    public function prev() {
        $this->stage = $this->stage - 1;

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
        } else {
            $this->coe_reports = [
                'date' => date('Y-m-d', strtotime($this->logged_branch->time_in)),
                'name' => strtoupper($this->logged_branch->user->fullName()),
                'account_name' => $this->logged_branch->branch->account->short_name,
                'branch_code' => $this->logged_branch->branch->branch_code,
                'branch_name' => $this->logged_branch->branch->branch_name,
            ];
        }
    }

    public function mount($logged_branch) {
        $this->logged_branch = $logged_branch;
        $this->stage = 1;
        $this->loadFormData();
    }

    public function render()
    {
        return view('livewire.coe.form');
    }
}
