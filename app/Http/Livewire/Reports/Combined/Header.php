<?php

namespace App\Http\Livewire\Reports\Combined;

use Livewire\Component;

use App\Models\User;

use Illuminate\Support\Facades\DB;

class Header extends Component
{
    public $user_options, $user_id, $year, $month, $selected_year;
    public $prev_year, $next_year;

    public function selectDate($month, $year) {
        $this->month = $month;
        $this->selected_year = $year;

        $this->emit('setActivityDate', $this->month, $this->selected_year, $this->user_id);
        $this->emit('setWarDate', $this->month, $this->selected_year, $this->user_id);
        $this->emit('setDeviationDate', $this->month, $this->selected_year, $this->user_id);
    }

    public function updatedUserId() {
        $this->emit('setActivityDate', $this->month, $this->selected_year, $this->user_id);
        $this->emit('setWarDate', $this->month, $this->selected_year, $this->user_id);
        $this->emit('setDeviationDate', $this->month, $this->selected_year, $this->user_id);
    }

    public function selectYear($year) {
        $this->year = $year;

        $this->prev_year = $this->year - 1;
        $this->next_year = $this->year + 1;
    }

    public function mount() {
        // set default date
        if(empty($this->year)) {
            $this->year = date('Y');
            $this->selected_year = $this->year;
        }
        if(empty($this->month)) {
            $this->month = date('m');
        }

        $this->prev_year = $this->year - 1;
        $this->next_year = $this->year + 1;
    }

    public function render()
    {
        // set months data
        $months_arr = [];
        for($i = 1; $i <= 12; $i++) {
            $key = $i < 10 ? '0'.$i : $i;
            $months_arr[$key] = date('M', strtotime(date('Y').'-'.$key.'-01'));
        }

        $user_options = [];
        if(auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('finance')) {
            $users = User::whereHas('activity_plans', function($query) {
                    $query->where('year', $this->year)
                        ->where('month', $this->month);
                })
                ->orWhereHas('deviations', function($query) {
                    $query->where(DB::raw('YEAR(date)'), $this->year)
                        ->where(DB::raw('MONTH(date)'), $this->month);
                })
                ->orWhereHas('weekly_activity_reports', function($query) {
                    $query->where(DB::raw('YEAR(date_submitted)'), $this->year)
                        ->where(DB::raw('MONTH(date_submitted)'), $this->month);
                })
                ->orderBy('firstname', 'ASC')->get();
            foreach($users as $user) {
                $user_options[$user->id] = $user->fullName();
            }
        } else {
            // get user options
            $subordinate_ids = auth()->user()->getSubordinateIds();
            foreach($subordinate_ids as $level => $ids) {
                foreach($ids as $id) {
                    $user = User::find($id);
                    if(!empty($user)) {
                        $user_options[$user->id] = $user->fullName();
                    }
                }
            }
        }

        $this->user_options = $user_options;

        return view('livewire.reports.combined.header')->with([
            'months' => $months_arr,
        ]);
    }
}
