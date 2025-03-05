<?php

namespace App\Http\Livewire\War;

use Livewire\Component;

use App\Models\User;
use App\Models\Area;
use App\Models\BranchLogin;
use App\Models\WeeklyActivityReport;
use App\Models\WeeklyActivityReportBranch;
use App\Models\UserBranchSchedule;

class WarForm extends Component
{
    public $areas, $user, $weekly_activity_report, $area_lines;
    public $date_from, $date_to;
    public $accounts_visited, $highlights;
    public $type = 'add_war';
    public $war;
    public $attachment_view;
    public $accounts_arr = [];

    public function showAttachments($model_id, $type) {
        if(isset($this->attachment_view[$type][$model_id]) && $this->attachment_view[$type][$model_id] == 1) {
            $this->attachment_view[$type][$model_id] = 0;
        } else {
            $this->attachment_view[$type][$model_id] = 1;
        }
    }

    public function showDetail($login_id) {
        $this->emit('showDetail', $login_id);
        $this->dispatchBrowserEvent('showDetail');
    }

    public function changeDate() {
        $this->reset('area_lines');
        
        $from = new \DateTime($this->date_from);
        $to = new \DateTime($this->date_to);
        $interval = $from->diff($to);

        $days = $interval->d;

        $start_date = $this->date_from;
        for($i = 0; $i <= $days; $i++) {

            // get schedules
            $schedules = UserBranchSchedule::with('branch')
                ->where('date', $start_date)
                ->where('user_id', $this->user->id)
                ->get();

            // get actual logins
            $branch_logins = BranchLogin::with('branch')
                ->where('user_id', $this->user->id)
                ->where('time_in', 'like', $start_date.'%')
                ->get();
            
            $area_arr = [];
            $deviations = [];
            foreach($branch_logins as $login) {
                $area_arr[] = $login->branch->area->area_name ?? '';
                $schedule = $schedules->where('branch_id', $login->branch_id);
                if(empty($schedule->count())) {
                    $deviations[] = $login;
                }

                $this->accounts_arr[$login->branch->account_id] = $login->branch->account->short_name;
            }

            $schedules_data = [];
            $schedules_visited = [];
            foreach($schedules as $schedule) {
                $visited = $branch_logins->where('branch_id', $schedule->branch_id)->first();
                if(!empty($visited)) {
                    $schedules_data[] = $schedule;
                    $schedules_visited[$schedule->id] = $visited;
                } else {
                    $schedules_data[] = $schedule;
                    $schedules_visited[$schedule->id] = null;
                }

                $this->accounts_arr[$schedule->branch->account->id] = $schedule->branch->account->short_name;
            }

            $action_points_arr = [];
            $attachments_arr = [];
            $activities = '';
            if(!empty($this->war)) {
                $area = $this->war->areas()->where('date', $start_date)->first();
                if(!empty($area)) {
                    $activities = $area->remarks ?? '';
                    $war_branches = $area->war_branches;
                    $action_points_arr[$start_date][] = $war_branches ?? NULL;

                    if(empty($war_branches->count())) {

                    }

                    if(!empty($war_branches)) {
                        foreach($war_branches as $war_branch) {
                            $attachments = $war_branch->attachments;
                            $attachments_arr[$start_date][$war_branch->branch_id][] = $attachments ?? NULL;
                        }
                    }
                }
            }

            // clean array
            $area_arr = array_unique(array_filter($area_arr));

            $this->area_lines[] = [
                'date' => $start_date,
                'day' => date('l', strtotime($start_date)),
                'area' => implode(', ', $area_arr),
                'activities' => $activities,
                'schedules' => $schedules_data,
                'schedules_visited' => $schedules_visited,
                'deviations' => $deviations,
                'action_points_arr' => $action_points_arr,
                'attachments_arr' => $attachments_arr,
            ];

            $start_date = date('Y-m-d', strtotime($start_date.' + 1 days'));
        }
    }

    public function mount($user_id, $war) {
        $this->war = $war;
        if(!empty($war)) {
            $this->date_from = $war->date_from;
            $this->date_to = $war->date_to;
            $this->accounts_visited = $war->accounts_visited;
            $this->type = 'update_war';
            $this->highlights = $war->highlights;
        }

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

        $this->changeDate();
    }

    public function render()
    {
        return view('livewire.war.war-form');
    }
}
