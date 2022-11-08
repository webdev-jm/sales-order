<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

class Header extends Component
{
    public $year, $month, $position;

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

        $organizations = auth()->user()->organizations;
        if(!empty($organizations)) {
            foreach($organizations as $organization) {
                $this->position[] = $organization->job_title->job_title;
            }
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
