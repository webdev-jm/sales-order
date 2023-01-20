<?php

namespace App\Http\Livewire\Schedules;

use Livewire\Component;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserBranchSchedule;

class ScheduleCalendar extends Component
{

    public $user_id, $branch_id;
    public $schedule_data;

    public function filter() {
        $this->dispatchBrowserEvent('renderCalendar');
    }

    public function render()
    {
        // FILTERS
            if(auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('sales')) {
                // user filter options
                $users = UserBranchSchedule::select('user_id')->distinct()
                ->get('user_id');

                $users_arr = [
                    '' => 'All'
                ];
                foreach($users as $user) {
                    $user_data = User::findOrFail($user->user_id);
                    $users_arr[$user_data->id] = $user_data->fullName();
                }
            } else {
                $users_arr = [
                    auth()->user()->id => auth()->user()->fullName()
                ];
            }

            $branches = UserBranchSchedule::select('branch_id')->distinct()
            ->whereNull('status')
            ->get('branch_id');

            $branches_arr = [
                '' => 'All'
            ];
            foreach($branches as $branch) {
                $branch_val = Branch::findOrFail($branch->branch_id);
                $branches_arr[$branch_val->id] = $branch_val->branch_code.' '.$branch_val->branch_name;
            }
        // 

        // DATA
        $user_id = trim($this->user_id);
        $branch_id = trim($this->branch_id);

        $schedule_color = '#25b8b5';
        $reschedule_color = '#f37206';

        $schedule_data = [];
        if(auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('sales')) {

            // check filter
            if(!empty($user_id) || !empty($branch_id)) {
                // Schedules
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->whereNull('status')
                ->get();
                
                foreach($schedules_date as $schedule) {
                    $schedules = UserBranchSchedule::whereNotNull('id')
                    ->whereNull('status')

                    ->where('date', $schedule->date);
                    if(!empty($user_id)) {
                        $schedules->where('user_id', $user_id);
                    }
                    if(!empty($branch_id)) {
                        $schedules->where('branch_id', $branch_id);
                    }
    
                    $schedules = $schedules->get();

                    if($schedules->count() > 0) {
                        $schedule_data[] = [
                            'title' => $schedules->count().($schedules->count() > 1 ? ' schedules' : ' schedule'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $schedule_color,
                            'borderColor' => $schedule_color,
                            'type' => 'schedule'
                        ];
                    }
                }

                // for reschedule
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->where('status', 'for reschedule')
                ->get();
                
                foreach($schedules_date as $schedule) {
                    $schedules = UserBranchSchedule::whereNotNull('id')
                    ->where('status', 'for reschedule')
                    ->where('date', $schedule->date);

                    if(!empty($user_id)) {
                        $schedules->where('user_id', $user_id);
                    }
                    if(!empty($branch_id)) {
                        $schedules->where('branch_id', $branch_id);
                    }
                    $schedules = $schedules->get();

                    if($schedules->count() > 0) {
                        $schedule_data[] = [
                            'title' => $schedules->count().($schedules->count() > 1 ? ' reschedule requests' : ' reschedule request'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $reschedule_color,
                            'borderColor' => $reschedule_color,
                            'type' => 'reschedule'
                        ];
                    }
                }

            } else { // no filter

                // Schedules
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->whereNull('status')
                ->get();

                foreach($schedules_date as $schedule) {
                    $schedule_count = UserBranchSchedule::where('date', $schedule->date)
                    ->whereNull('status')->count();

                    if($schedule_count > 0) {
                        $schedule_data[] = [
                            'title' => $schedule_count.($schedule_count > 1 ? ' schedules' : ' schedule'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $schedule_color,
                            'borderColor' => $schedule_color,
                            'type' => 'schedule'
                        ];
                    }
                }

                // for reschedule
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->where('status', 'for reschedule')
                ->get();

                foreach($schedules_date as $schedule) {
                    $schedule_count = UserBranchSchedule::where('date', $schedule->date)
                    ->where('status', 'for reschedule')->count();
                    
                    if($schedule_count > 0) {
                        $schedule_data[] = [
                            'title' => $schedule_count.($schedule_count > 1 ? ' reschedule requests' : ' reschedule request'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $reschedule_color,
                            'borderColor' => $reschedule_color,
                            'type' => 'reschedule'
                        ];
                    }
                }

            }

        } else {
            // check branch filter
            if(!empty($branch_id)) {
                // Schedules
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->whereNull('status')
                ->get();
                foreach($schedules_date as $schedule) {
                    $count = UserBranchSchedule::where('user_id', auth()->user()->id)
                    ->whereNull('status')
                    ->where('branch_id', $branch_id)
                    ->where('date', $schedule->date)
                    ->count();

                    if($count > 0) {
                        $schedule_data[] = [
                            'title' => $count.($count > 1 ? ' schedules' : ' schedule'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $schedule_color,
                            'borderColor' => $schedule_color,
                            'type' => 'schedule'
                        ];
                    }
                }

                // For Reschedule
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->where('status', 'for reschedule')
                ->get();

                foreach($schedules_date as $schedule) {
                    $count = UserBranchSchedule::where('user_id', auth()->user()->id)
                    ->where('status', 'for reschedule')
                    ->where('branch_id', $branch_id)
                    ->where('date', $schedule->date)
                    ->count();

                    if($count > 0) {
                        $schedule_data[] = [
                            'title' => $count.($count > 1 ? ' reschedule requests' : ' reschedule request'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $reschedule_color,
                            'borderColor' => $reschedule_color,
                            'type' => 'reschedule'
                        ];
                    }
                }

            } else { // no branch filter
                // Schedules
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->whereNull('status')
                ->get();
                foreach($schedules_date as $schedule) {
                    $count = UserBranchSchedule::where('user_id', auth()->user()->id)
                    ->whereNull('status')
                    ->where('date', $schedule->date)
                    ->count();

                    if($count > 0) {
                        $schedule_data[] = [
                            'title' => $count.($count > 1 ? ' schedules' : ' schedule'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $schedule_color,
                            'borderColor' => $schedule_color,
                            'type' => 'schedule'
                        ];
                    }
                }

                // For Reschedule
                $schedules_date = UserBranchSchedule::select('date')->distinct()
                ->where('status', 'for reschedule')
                ->get();

                foreach($schedules_date as $schedule) {
                    $count = UserBranchSchedule::where('user_id', auth()->user()->id)
                    ->where('status', 'for reschedule')
                    ->where('date', $schedule->date)
                    ->count();

                    if($count > 0) {
                        $schedule_data[] = [
                            'title' => $count.($count > 1 ? ' reschedule requests' : ' reschedule request'),
                            'start' => $schedule->date,
                            'allDay' => true,
                            'backgroundColor' => $reschedule_color,
                            'borderColor' => $reschedule_color,
                            'type' => 'reschedule'
                        ];
                    }
                }

            }
        }

        $this->schedule_data = $schedule_data;

        return view('livewire.schedules.schedule-calendar')->with([
            'users' => $users_arr,
            'branches' => $branches_arr,
        ]);
    }
}
