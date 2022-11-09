<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use Illuminate\Support\Facades\Session;

class Header extends Component
{
    public $year, $month, $objectives;

    public function updatedObjectives() {
        $activity_plan_data = Session::get('activity_plan_data');
        if(empty($activity_plan_data[$this->year][$this->month])) {
            // initialize
            $data[$this->year][$this->month] = [
                'year' => $this->year,
                'month' => $this->month,
                'objectives' => $this->objectives,
                'details' => []
            ];
            Session::put('activity_plan_data', $data);
        } else {
            // update
            $activity_plan_data[$this->year][$this->month]['objectives'] = $this->objectives;
            Session::put('activity_plan_data', $activity_plan_data);
        }
    }

    public function change_date() {
        $this->emit('setDate', $this->year, $this->month);
    }

    public function mount() {
        if(empty($this->year)) {
            $this->year = date('Y');
        }

        if(empty($this->month)) {
            $this->month = date('m');
        }

        $activity_plan_data = Session::get('activity_plan_data');
        if(!empty($activity_plan_data[$this->year][$this->month])) {
            $this->objectives = $activity_plan_data[$this->year][$this->month]['objectives'];
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
