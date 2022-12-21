<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\User;
use App\Models\Branch;
use App\Models\ActivityPlanDetail;

use Illuminate\Support\Facades\Session;

class Detail extends Component
{
    public $year, $month, $last_day, $lines;

    public $branch_query, $searchQuery;

    protected $listeners = [
        'setDate' => 'setDate'
    ];

    public function clearBranch($date, $key) {
        $this->lines[$date]['lines'][$key]['branch_id'] = '';
        $this->lines[$date]['lines'][$key]['branch_name'] = '';

        $this->setSession();
    }

    public function updatedLines() {
        $this->setSession();
    }

    public function selectBranch($date, $key, $branch_id, $branch_name) {
        $this->lines[$date]['lines'][$key]['branch_id'] = $branch_id;
        $this->lines[$date]['lines'][$key]['branch_name'] = $branch_name;

        // get previous record of location
        $detail = ActivityPlanDetail::orderBy('id', 'DESC')
        ->where('branch_id', $branch_id)
        ->where('exact_location', '<>', '')
        ->first();

        if(!empty($detail)) {
            $this->lines[$date]['lines'][$key]['location'] = $detail->exact_location;
        }

        $this->resetQuery();

        $this->setSession();
    }

    public function setQuery($date, $key) {
        $query = $this->branch_query[$date][$key];
        // remove other query
        $this->resetQuery();
        $this->branch_query[$date][$key] = $query;

        $this->searchQuery = $query;
    }

    public function resetQuery() {
        $this->reset([
            'branch_query',
            'searchQuery'
        ]);
    }

    public function addLine($date) {
        $this->lines[$date]['lines'][] = [
            'location' => '',
            'branch_id' => '',
            'branch_name' => '',
            'purpose' => '',
            'user_id' => ''
        ];

        $this->setSession();
    }

    public function setDate($year, $month) {
        $this->year = $year;
        $this->month = $month < 10 ? '0'.(int)$month : $month;

        $this->last_day = date('t', strtotime($this->year.'-'.$this->month.'-01'));

        $this->setLine();
    }

    public function setLine() {

        $activity_plan_data = Session::get('activity_plan_data');

        $lines = [];
        for($i = 1; $i <= (int)$this->last_day; $i++) {
            $date = $this->year.'-'.$this->month.'-'.($i < 10 ? '0'.$i : $i);
            $day = date('D', strtotime($date));
            $class = '';
            if($day == 'Sun') {
                $class = 'bg-navy';
            } else if($day == 'Sat') {
                $class = 'bg-secondary';
            }

            if(isset($activity_plan_data[$this->year][$this->month]['details'][$date]['lines']) && !empty($activity_plan_data[$this->year][$this->month]['details'][$date]['lines'])) {
                $data = $activity_plan_data[$this->year][$this->month]['details'][$date]['lines'];
            } else {
                $data = [
                    [
                        'location' => '',
                        'branch_id' => '',
                        'branch_name' => '',
                        'purpose' => '',
                        'user_id' => ''
                    ]
                ];
            }
            
            $lines[$date] = [
                'day' => $day,
                'date' => date('M', strtotime($this->year.'-'.$this->month.'-01')).'. '.($i < 10 ? '0'.$i : $i),
                'class' => $class,
                'lines' => $data
            ];
        }

        $this->lines = $lines;
    }

    public function setSession() {
        $activity_plan_data = Session::get('activity_plan_data');
        if(empty($activity_plan_data)) { // no session
            $plan_data[$this->year][$this->month] = [
                'year' => $this->year,
                'month' => $this->month,
                'objectives' => '',
                'details' => $this->lines
            ];
            // initialize data
            Session::put('activity_plan_data', $plan_data);
        } else { // with session
            $activity_plan_data[$this->year][$this->month]['details'] = $this->lines;
            // replace details
            Session::put('activity_plan_data', $activity_plan_data);
        }
    }

    public function mount() {
        $activity_plan_data = Session::get('activity_plan_data');
        if(!empty($activity_plan_data)) {
            foreach($activity_plan_data as $year => $months) {
                $this->year = $year;
                foreach($months as $month => $data) {
                    $this->month = $month < 10 ? '0'.(int)$month : $month;
                }
            }

        } else {
            if(empty($this->year)) {
                $this->year = date('Y');
            }
            if(empty($this->month)) {
                $month = date('m');
                if($month == 12) {
                    $this->month = '01';
                    $this->year = date('Y') + 1;
                } else {
                    $this->month = ($month + 1) < 10 ? '0'.($month + 1) : ($month + 1);
                }
            }
        }

        $this->last_day = date('t', strtotime($this->year.'-'.$this->month.'-01'));

        $this->setLine();
        
    }

    public function render()
    {
        if(!empty($this->searchQuery)) {
            $branches = Branch::orderBy('branch_name')
            // ->whereHas('account', function($query) {
            //     $query->whereHas('users', function($qry) {
            //         $qry->where('id', auth()->user()->id);
            //     });
            // })
            ->where(function($query) {
                $query->where('branch_code', 'like', '%'.$this->searchQuery.'%')
                ->orWhere('branch_name', 'like', '%'.$this->searchQuery.'%')
                ->orWhereHas('account', function($qry) {
                    $qry->where('short_name', 'like', '%'.$this->searchQuery.'%');
                });
            })
            ->limit(10)->get();
        } else {
            $branches = Branch::orderBy('branch_code')
            ->whereHas('account', function($query) {
                $query->whereHas('users', function($qry) {
                    $qry->where('id', auth()->user()->id);
                });
            })
            ->limit(10)->get();
        }
        
        $users = User::orderBy('firstname', 'ASC')
        ->get();

        return view('livewire.activity-plan.detail')->with([
            'branches' => $branches,
            'users' => $users,
        ]);
    }
}
