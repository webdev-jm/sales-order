<?php

namespace App\Http\Livewire\War;

use Livewire\Component;

use App\Models\User;
use App\Models\Area;
use App\Models\BranchLogin;
use App\Models\WeeklyActivityReport;

class WarForm extends Component
{
    public $areas, $user, $weekly_activity_report, $area_lines;
    public $date_from, $date_to;

    public function changeDate() {
        $this->reset('area_lines');
        
        $from = new \DateTime($this->date_from);
        $to = new \DateTime($this->date_to);
        $interval = $from->diff($to);

        $days = $interval->d;
        
        $start_date = $this->date_from;
        for($i = 0; $i <= $days; $i++) {

            // get areas
            $branch_logins = BranchLogin::where('user_id', $this->user->id)
            ->where('time_in', 'like', $start_date.'%')
            ->get();
            
            $area_arr = [];
            foreach($branch_logins as $login) {
                $area_arr[] = $login->branch->area->area_name ?? '';
            }
            // clean array
            $area_arr = array_unique(array_filter($area_arr));

            $this->area_lines[] = [
                'date' => $start_date,
                'day' => date('l', strtotime($start_date)),
                'area' => implode(', ', $area_arr),
            ];

            $start_date = date('Y-m-d', strtotime($start_date.' + 1 days'));
        }
    }

    public function mount($user_id) {
        // area options
        $areas = Area::orderBy('area_code', 'ASC')
        ->get();

        $areas_arr = [
            '' => ''
        ];
        foreach($areas as $area) {
            $areas_arr[$area->id] = '['.$area->area_code.'] '.$area->area_name;
        }
        $this->areas = $areas_arr;

        if(empty($this->date_from)) {
            $this->date_from = date('Y-m-d');
        }
        if(empty($this->date_to)) {
            $this->date_to = date('Y-m-d');
        }

        $this->user = User::find($user_id);
    }

    public function render()
    {
        return view('livewire.war.war-form');
    }
}
