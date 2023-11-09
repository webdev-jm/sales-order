<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\Account;
use App\Models\Branch;
use App\Models\ActivityPlanDetail;

use Illuminate\Support\Facades\Session;

class Detail2 extends Component
{
    public $last_day;
    public $month_days;
    public $year;
    public $month;
    public $expand_dates;

    protected $listeners = [
        'setDate' => 'setDate',
        'saveTrip' => 'saveTrip',
    ];

    // load trip data after saving
    public function saveTrip($year, $month, $date, $key, $trip_data) {
        $this->month_days[$month][$date]['lines'][$key]['trip'] = $trip_data;
        $this->setSession();
    }

    // set date when header updates
    public function setDate($year, $month) {
        $this->year = $year;
        $this->month = $month < 10 ? '0'.(int)$month : $month;

        $this->last_day = date('t', strtotime($this->year.'-'.$this->month.'-01'));

        $this->setMonthDays();
    }

    // save to session when data changes
    public function updatedMonthDays() {
        $this->setSession();
    }

    // account search
    public $account_query, $searchAccountQuery, $account_id;

    // clear selected account
    public function clearAccount($date, $key) {
        $this->month_days[$this->month][$date]['lines'][$key]['account_id'] = '';
        $this->month_days[$this->month][$date]['lines'][$key]['account_name'] = '';

        $this->setSession();
    }

    // select account from search results
    public function selectAccount($date, $key, $account_id, $account_name) {
        if(!empty($this->month_days[$this->month][$date]['lines'][$key]['account_id']) &&
            $this->month_days[$this->month][$date]['lines'][$key]['account_id'] != $account_id
        ) {
            $this->month_days[$this->month][$date]['lines'][$key]['branch_id'] = '';
            $this->month_days[$this->month][$date]['lines'][$key]['branch_name'] = '';
        }

        $this->month_days[$this->month][$date]['lines'][$key]['account_id'] = $account_id;
        $this->month_days[$this->month][$date]['lines'][$key]['account_name'] = $account_name;

        $this->resetAccountQuery();
        $this->setSession();
    }
    
    // set search query
    public function setAccountQuery($date, $key) {
        $query = $this->account_query[$date][$key] ?? '';
        // remove other query
        $this->resetAccountQuery();
        $this->account_query[$date][$key] = $query;

        $this->account_id = $this->month_days[$this->month][$date]['lines'][$key]['account_id'];

        $this->searchAccountQuery = $query;
    }

    // reset account search
    public function resetAccountQuery() {
        $this->reset([
            'account_query',
            'searchAccountQuery',
            'account_id',
        ]);
    }


    // branch search
    public $branch_query, $searchBranchQuery;

    // clear selected branch
    public function clearBranch($date, $key) {
        $this->month_days[$this->month][$date]['lines'][$key]['branch_id'] = '';
        $this->month_days[$this->month][$date]['lines'][$key]['branch_name'] = '';

        $this->setSession();
    }

    // select branch from search results
    public function selectBranch($date, $key, $branch_id, $branch_name) {
        $this->month_days[$this->month][$date]['lines'][$key]['branch_id'] = $branch_id;
        $this->month_days[$this->month][$date]['lines'][$key]['branch_name'] = $branch_name;

        // get branch account
        if(empty($this->month_days[$this->month][$date]['lines'][$key]['account_id'])) {
            $branch = Branch::find($branch_id);
            $account_id = $branch->account_id;
            $this->month_days[$this->month][$date]['lines'][$key]['account_id'] = $account_id;
            $this->month_days[$this->month][$date]['lines'][$key]['account_name'] = $branch->account->account_code.' '.$branch->account->short_name;
        }

        // get previous record of location
        $detail = ActivityPlanDetail::orderBy('id', 'DESC')
            ->where('branch_id', $branch_id)
            ->where('exact_location', '<>', '')
            ->first();

        if(!empty($detail)) {
            $this->month_days[$this->month][$date]['lines'][$key]['location'] = $detail->exact_location;
        }

        $this->resetBranchQuery();
        $this->setSession();
    }

    // set branch search query
    public function setBranchQuery($date, $key) {
        $query = $this->branch_query[$date][$key] ?? '';
        // remove other query
        $this->resetBranchQuery();
        $this->branch_query[$date][$key] = $query;

        $this->account_id = $this->month_days[$this->month][$date]['lines'][$key]['account_id'];

        $this->searchBranchQuery = $query;
    }

    // reset branch query
    public function resetBranchQuery() {
        $this->reset([
            'branch_query',
            'searchBranchQuery',
            'account_id'
        ]);
    }

    // save data to session
    public function setSession() {
        $activity_plan_data = Session::get('activity_plan_data');
        if(empty($activity_plan_data)) { // no session
            $plan_data[$this->year] = [
                'year' => $this->year,
                'month' => $this->month,
                'objectives' => '',
                'details' => $this->month_days
            ];
            // initialize data
            Session::put('activity_plan_data', $plan_data);
        } else { // with session
            $activity_plan_data[$this->year]['details'] = $this->month_days;
            // replace details
            Session::put('activity_plan_data', $activity_plan_data);
        }
    }

    // add schedule line
    public function addSheduleLine($date) {
        $this->month_days[$this->month][$date]['lines'][] = [
            'location' => '',
            'account_id' => '',
            'account_name' => '',
            'branch_id' => '',
            'branch_name' => '',
            'purpose' => '',
            'user_id' => '',
            'work_with' => '',
        ];
    }

    // remove schedule line
    public function removeLine($date, $key) {
        if(!empty($this->month_days[$this->month][$date]['lines'][$key]['id'])) {
            $this->month_days[$this->month][$date]['lines'][$key]['deleted'] = true;
        } else {
            unset($this->month_days[$this->month][$date]['lines'][$key]);
        }

        $this->setSession();
    }

    // expand and minimize days
    public function expandDate($date) {
        // check value
        if($this->expand_dates[$date]) {
            $this->expand_dates[$date] = false;
        } else {
            $this->expand_dates[$date] = true;
        }
    }

    // minimize all rows
    public function minimizeAll() {
        foreach($this->expand_dates as $date => $val) {
            $this->expand_dates[$date] = false;
        }
    }

    // expand all rows
    public function expandAll() {
        foreach($this->expand_dates as $date => $val) {
            $this->expand_dates[$date] = true;
        }
    }

    // initialize month days
    public function setMonthDays() {
        $activity_plan_data = Session::get('activity_plan_data');
        
        $days = array();
        for($i = 1; $i <= (int)$this->last_day; $i++) {
            $date = $this->year.'-'.$this->month.'-'.($i < 10 ? '0'.$i : $i);
            $day = date('D', strtotime($date)); // get day of the week
            // change class for sat and sun
            $class = '';
            if($day == 'Sun') {
                $class = 'bg-navy';
            } else if($day == 'Sat') {
                $class = 'bg-secondary';
            } else {
                $class = "bg-light";
            }

            if(isset($activity_plan_data[$this->year]['details'][$this->month][$date]['lines']) && !empty($activity_plan_data[$this->year]['details'][$this->month][$date]['lines'])) {
                $data = $activity_plan_data[$this->year]['details'][$this->month][$date]['lines'];
            } else {
                $data = [];
            }

            $days[$date] = [
                'day' => $day,
                'date' => date('M', strtotime($this->year.'-'.$this->month.'-01')).'. '.($i < 10 ? '0'.$i : $i),
                'class' => $class,
                'lines' => $data
            ];

            $this->expand_dates[$date] = false;
        }

        $this->month_days[$this->month] = $days;
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
                $month = date('m');
                if($month == 12) {
                    $this->month = '01';
                    $this->year = date('Y') + 1;
                } else {
                    $this->month = ($month + 1) < 10 ? '0'.($month + 1) : ($month + 1);
                }
            }
        }


        // get last day of the month
        $this->last_day = date('t', strtotime($this->year.'-'.$this->month.'-01'));

        $this->setMonthDays();
    }

    public function render()
    {

        // account search query
        $accounts = Account::whereHas('users', function($query) {
            $query->where('id', auth()->user()->id);
        })
        ->when(!empty($this->searchAccountQuery), function($query) {
            $query->where(function($qry) {
                $qry->where('account_code', 'like', '%'.$this->searchAccountQuery.'%')
                    ->orWhere('short_name', 'like', '%'.$this->searchAccountQuery.'%');
            });
        })
        ->limit(10)
        ->get();

        // branch search query
        $branches = Branch::orderBy('branch_name')
            ->whereHas('account', function($query) {
                $query->when(!empty($this->account_id), function($qry) {
                    $qry->where('id', $this->account_id);
                })
                ->whereHas('users', function($qry) {
                    $qry->where('id', auth()->user()->id);
                });
            })
            ->when(!empty($this->searchBranchQuery), function($query) {
                $query->where(function($query) {
                    $query->where('branch_code', 'like', '%'.$this->searchBranchQuery.'%')
                        ->orWhere('branch_name', 'like', '%'.$this->searchBranchQuery.'%')
                        ->when(empty($this->account_id), function($qry) {
                            $qry->orWhereHas('account', function($qry1) {
                                $qry1->where('short_name', 'like', '%'.$this->searchBranchQuery.'%');
                            });
                        });
                });
            })
            ->limit(10)
            ->get();

        return view('livewire.activity-plan.detail2')->with([
            'accounts' => $accounts,
            'branches' => $branches
        ]);
    }
}
