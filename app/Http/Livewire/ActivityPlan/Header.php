<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\GlobalTrait;

class Header extends Component
{
    use GlobalTrait;
    
    public $year, $month, $objectives;
    public $deadline_message;

    public function updatedObjectives() {
        $activity_plan_data = Session::get('activity_plan_data');
        if(empty($activity_plan_data[$this->year])) {
            // initialize
            $data[$this->year] = [
                'year' => $this->year,
                'month' => $this->month,
                'objectives' => $this->objectives,
                'details' => [
                    $this->month => []
                ]
            ];
            Session::put('activity_plan_data', $data);
        } else {
            // update
            $activity_plan_data[$this->year]['objectives'] = $this->objectives;
            Session::put('activity_plan_data', $activity_plan_data);
        }
    }

    public function change_date() {
        $activity_plan_data = Session::get('activity_plan_data');
        if(empty($activity_plan_data[$this->year])) {
            // initialize
            $data[$this->year] = [
                'year' => $this->year,
                'month' => $this->month,
                'objectives' => $this->objectives,
                'details' => [
                    $this->month => []
                ]
            ];
            Session::put('activity_plan_data', $data);
        } else {
            // update
            $activity_plan_data[$this->year]['objectives'] = $this->objectives;
            $activity_plan_data[$this->year]['year'] = $this->year;
            $activity_plan_data[$this->year]['month'] = $this->month;
            Session::put('activity_plan_data', $activity_plan_data);
        }

        $this->emit('setDate', $this->year, $this->month);

        // get deadline from settings
        $settings = $this->getSettings();
        
        // check if date is past deadline
        $current_date = [
            'year' => date('Y'),
            'month' => (int)date('m') + 1,
            'day' => date('d')
        ];
        $deadline = $settings->mcp_deadline;
        // check if date was already pass deadline
        if($this->year <= $current_date['year'] && $this->month < $current_date['month']) {
            $this->deadline_message = 'This date was already passed deadline.';
        } else {
            $this->reset('deadline_message');
        }
    }

    public function mount() {
        $activity_plan_data = Session::get('activity_plan_data');
        if(!empty($activity_plan_data)) {
            foreach($activity_plan_data as $year => $data) {
                $this->year = $year;
                $this->month = $data['month'] < 10 ? '0'.(int)$data['month'] : $data['month'];
            }

        } else {
            if(empty($this->year)) {
                $this->year = date('Y');
            }
    
            if(empty($this->month)) {
                $month = (int)date('m');
                if($month == 12) {
                    $month = 1;
                    $this->year = $this->year + 1;
                } else {
                    $month = ($month + 1) < 10 ? '0'.($month + 1) : ($month + 1);
                }

                $this->month = $month < 10 ? '0'.$month : $month;
            }
        }

        if(!empty($activity_plan_data[$this->year])) {
            $this->objectives = $activity_plan_data[$this->year]['objectives'] ?? '';
        }
    }

    public function render()
    {
        $months_arr = [
            '01' => 'January',
            '02' => 'february',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December'
        ];

        return view('livewire.activity-plan.header')->with([
            'months_arr' => $months_arr
        ]);
    }
}
