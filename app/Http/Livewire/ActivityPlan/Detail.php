<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\User;
use App\Models\Branch;
use App\Models\Account;
use App\Models\ActivityPlanDetail;

use Illuminate\Support\Facades\Session;

class Detail extends Component
{
    public $year, $month, $last_day, $lines;
    public $account_id;
    public $branch_query, $searchQuery;
    public $account_query, $searchAccountQuery;

    protected $listeners = [
        'setDate' => 'setDate'
    ];

    public function clearBranch($date, $key) {
        $this->lines[$this->month][$date]['lines'][$key]['branch_id'] = '';
        $this->lines[$this->month][$date]['lines'][$key]['branch_name'] = '';

        $this->setSession();
    }

    public function clearAccount($date, $key) {
        $this->lines[$this->month][$date]['lines'][$key]['account_id'] = '';
        $this->lines[$this->month][$date]['lines'][$key]['account_name'] = '';

        $this->setSession();
    }

    public function updatedLines() {
        $this->setSession();
    }

    public function selectBranch($date, $key, $branch_id, $branch_name) {
        $this->lines[$this->month][$date]['lines'][$key]['branch_id'] = $branch_id;
        $this->lines[$this->month][$date]['lines'][$key]['branch_name'] = $branch_name;

        // get branch account
        if(empty($this->lines[$this->month][$date]['lines'][$key]['account_id'])) {
            $branch = Branch::find($branch_id);
            $account_id = $branch->account_id;
            $this->lines[$this->month][$date]['lines'][$key]['account_id'] = $account_id;
            $this->lines[$this->month][$date]['lines'][$key]['account_name'] = $branch->account->account_code.' '.$branch->account->short_name;
        }

        // get previous record of location
        $detail = ActivityPlanDetail::orderBy('id', 'DESC')
        ->where('branch_id', $branch_id)
        ->where('exact_location', '<>', '')
        ->first();

        if(!empty($detail)) {
            $this->lines[$this->month][$date]['lines'][$key]['location'] = $detail->exact_location;
        }

        $this->resetQuery();
        $this->setSession();
    }

    public function selectAccount($date, $key, $account_id, $account_name) {
        if(!empty($this->lines[$this->month][$date]['lines'][$key]['account_id']) &&
            $this->lines[$this->month][$date]['lines'][$key]['account_id'] != $account_id
        ) {
            $this->lines[$this->month][$date]['lines'][$key]['branch_id'] = '';
            $this->lines[$this->month][$date]['lines'][$key]['branch_name'] = '';
        }

        $this->lines[$this->month][$date]['lines'][$key]['account_id'] = $account_id;
        $this->lines[$this->month][$date]['lines'][$key]['account_name'] = $account_name;

        $this->resetAccountQuery();
        $this->setSession();
    }

    public function removeLine($month, $date, $key) {
        // check if more than 1 lines
        if(count($this->lines[$month][$date]['lines']) > 1) {
            unset($this->lines[$month][$date]['lines'][$key]);
        }

        $this->setSession();
    }

    public function setQuery($date, $key) {
        $query = $this->branch_query[$date][$key] ?? '';
        // remove other query
        $this->resetQuery();
        $this->branch_query[$date][$key] = $query;

        $this->account_id = $this->lines[$this->month][$date]['lines'][$key]['account_id'];

        $this->searchQuery = $query;
    }

    public function setAccountQuery($date, $key) {
        $query = $this->account_query[$date][$key];
        // remove other query
        $this->resetAccountQuery();
        $this->account_query[$date][$key] = $query;

        $this->account_id = $this->lines[$this->month][$date]['lines'][$key]['account_id'];

        $this->searchAccountQuery = $query;
    }

    public function setAccount($date, $key) {
        $this->account_id = $this->lines[$this->month][$date]['lines'][$key]['account_id'];
    }

    public function resetQuery() {
        $this->reset([
            'branch_query',
            'searchQuery',
            'account_id'
        ]);
    }

    public function resetAccountQuery() {
        $this->reset([
            'account_query',
            'searchAccountQuery',
            'account_id',
        ]);
    }

    public function addLine($date) {
        $this->lines[$this->month][$date]['lines'][] = [
            'location' => '',
            'account_id' => '',
            'account_name' => '',
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

            if(isset($activity_plan_data[$this->year]['details'][$this->month][$date]['lines']) && !empty($activity_plan_data[$this->year]['details'][$this->month][$date]['lines'])) {
                $data = $activity_plan_data[$this->year]['details'][$this->month][$date]['lines'];
            } else {
                $data = [
                    [
                        'location' => '',
                        'account_id' => '',
                        'account_name' => '',
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

        $this->lines[$this->month] = $lines;
    }

    public function setSession() {
        $activity_plan_data = Session::get('activity_plan_data');
        if(empty($activity_plan_data)) { // no session
            $plan_data[$this->year] = [
                'year' => $this->year,
                'month' => $this->month,
                'objectives' => '',
                'details' => $this->lines
            ];
            // initialize data
            Session::put('activity_plan_data', $plan_data);
        } else { // with session
            $activity_plan_data[$this->year]['details'] = $this->lines;
            // replace details
            Session::put('activity_plan_data', $activity_plan_data);
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
            ->whereHas('account', function($query) {
                if(!empty($this->account_id)) {
                    $query->where('id', $this->account_id);
                } else {
                    $query->whereHas('users', function($qry) {
                        $qry->where('id', auth()->user()->id);
                    });
                }
            })
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
                if(!empty($this->account_id)) {
                    $query->where('id', $this->account_id);
                } else {
                    $query->whereHas('users', function($qry) {
                        $qry->where('id', auth()->user()->id);
                    });
                }
            })
            ->limit(10)->get();
        }
        
        $users = User::orderBy('firstname', 'ASC')
        ->where('group_code', '<>', 'CMD')
        ->where('group_code', '<>', NULL)
        ->get();

        if(!empty($this->searchAccountQuery)) {
            $accounts = Account::whereHas('users', function($query) {
                $query->where('id', auth()->user()->id);
            })
            ->where(function($query) {
                $query->where('account_code', 'like', '%'.$this->searchAccountQuery.'%')
                ->orWhere('short_name', 'like', '%'.$this->searchAccountQuery.'%');
            })
            ->limit(10)
            ->get();
        } else {
            $accounts = Account::whereHas('users', function($query) {
                $query->where('id', auth()->user()->id);
            })
            ->limit(10)
            ->get();
        }

        return view('livewire.activity-plan.detail')->with([
            'branches' => $branches,
            'users' => $users,
            'accounts' => $accounts
        ]);
    }
}
