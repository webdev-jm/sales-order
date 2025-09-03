<?php

namespace App\Http\Livewire\ActivityPlan;

use Livewire\Component;

use App\Models\Account;
use App\Models\Branch;
use App\Models\ActivityPlanDetail;
use App\Models\ActivityPlanDetailTrip;
use App\Models\ActivityPlanDetailTripDestination;

use Illuminate\Support\Facades\Session;

class Detail2 extends Component
{
    // Make properties public for Livewire to track
    public $year;
    public $month;
    public $last_day;
    public $month_days = [];
    public $expand_dates = [];
    public $account_query = [];
    public $branch_query = [];

    // These properties are for internal component logic and don't need to be tracked by Livewire
    protected $searchAccountQuery;
    protected $searchBranchQuery;
    protected $account_id;

    protected $listeners = [
        'setDate' => 'setDate',
        'saveTrip' => 'saveTrip',
    ];

    public function mount($year = null, $month = null)
    {
        // 1. Load initial data from session or set defaults.
        $activity_plan_data = Session::get('activity_plan_data');

        if (!empty($activity_plan_data)) {
            $this->year = array_key_first($activity_plan_data);
            $this->month = $activity_plan_data[$this->year]['month'];
        } else {
            $this->year = $year ?? date('Y');
            $this->month = $month ?? date('m');
        }

        // 2. Normalize the month and year
        $this->month = $this->month < 10 ? '0' . (int)$this->month : (string)$this->month;
        $this->year = (string)$this->year;

        // 3. Set the initial month days array
        $this->setMonthDays();
    }

    public function render()
    {
        $accounts = Account::whereHas('users', function ($query) {
            $query->where('id', auth()->user()->id);
        })
            ->when(!empty($this->searchAccountQuery), function ($query) {
                $query->where(function ($qry) {
                    $qry->where('account_code', 'like', '%' . $this->searchAccountQuery . '%')
                        ->orWhere('short_name', 'like', '%' . $this->searchAccountQuery . '%');
                });
            })
            ->limit(10)
            ->get();

        $branches = Branch::orderBy('branch_name')
            ->whereHas('account', function ($query) {
                $query->when(!empty($this->account_id), function ($qry) {
                    $qry->where('id', $this->account_id);
                })
                    ->whereHas('users', function ($qry) {
                        $qry->where('id', auth()->user()->id);
                    });
            })
            ->when(!empty($this->searchBranchQuery), function ($query) {
                $query->where(function ($query) {
                    $query->where('branch_code', 'like', '%' . $this->searchBranchQuery . '%')
                        ->orWhere('branch_name', 'like', '%' . $this->searchBranchQuery . '%')
                        ->when(empty($this->account_id), function ($qry) {
                            $qry->orWhereHas('account', function ($qry1) {
                                $qry1->where('short_name', 'like', '%' . $this->searchBranchQuery . '%');
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

    // ---
    // PUBLIC METHODS
    // ---

    // Sets the component's state based on a month change event.
    public function setDate($year, $month)
    {
        $this->year = (string)$year;
        $this->month = $month < 10 ? '0' . (int)$month : (string)$month;
        
        // This is the key: Reset and re-populate all properties that are
        // dependent on the month to ensure Livewire's state is clean.
        $this->reset(['month_days', 'expand_dates', 'account_query', 'branch_query']);
        $this->setMonthDays();
        $this->setSession();
    }

    public function updatedMonthDays()
    {
        $this->setSession();
    }

    public function saveTrip($year, $month, $date, $key, $trip_data)
    {
        // Use a consistent way to access the array
        $this->month_days[$this->month][$date]['lines'][$key]['trip'] = $trip_data;
        $this->setSession();
    }

    // ---
    // SEARCH METHODS
    // ---

    public function setAccountQuery($date, $key)
    {
        $query = $this->account_query[$date][$key] ?? '';
        $this->resetAccountQuery();
        $this->account_query[$date][$key] = $query;
        $this->account_id = $this->month_days[$this->month][$date]['lines'][$key]['account_id'];
        $this->searchAccountQuery = $query;
    }

    public function selectAccount($date, $key, $account_id)
    {
        $account = Account::find($account_id);

        if ($this->month_days[$this->month][$date]['lines'][$key]['account_id'] != $account->id) {
            $this->month_days[$this->month][$date]['lines'][$key]['branch_id'] = '';
            $this->month_days[$this->month][$date]['lines'][$key]['branch_name'] = '';
        }

        $this->month_days[$this->month][$date]['lines'][$key]['account_id'] = $account->id;
        $this->month_days[$this->month][$date]['lines'][$key]['account_name'] = $account->account_name;

        $this->resetAccountQuery();
        $this->setSession();
    }

    public function resetAccountQuery()
    {
        $this->reset(['account_query']);
        $this->searchAccountQuery = null;
        $this->account_id = null;
    }

    public function setBranchQuery($date, $key)
    {
        $query = $this->branch_query[$date][$key] ?? '';
        $this->resetBranchQuery();
        $this->branch_query[$date][$key] = $query;
        $this->account_id = $this->month_days[$this->month][$date]['lines'][$key]['account_id'];
        $this->searchBranchQuery = $query;
    }

    public function selectBranch($date, $key, $branch_id)
    {
        $branch = Branch::find($branch_id);

        $this->month_days[$this->month][$date]['lines'][$key]['branch_id'] = $branch_id;
        $this->month_days[$this->month][$date]['lines'][$key]['branch_name'] = '[' . $branch->account->account_code . ' ' . $branch->account->short_name . '] ' . $branch->branch_code . ' ' . $branch->branch_name;

        if (empty($this->month_days[$this->month][$date]['lines'][$key]['account_id'])) {
            $this->month_days[$this->month][$date]['lines'][$key]['account_id'] = $branch->account_id;
            $this->month_days[$this->month][$date]['lines'][$key]['account_name'] = $branch->account->account_code . ' ' . $branch->account->short_name;
        }

        $detail = ActivityPlanDetail::orderBy('id', 'DESC')
            ->where('branch_id', $branch_id)
            ->where('exact_location', '<>', '')
            ->first();

        if (!empty($detail)) {
            $this->month_days[$this->month][$date]['lines'][$key]['location'] = $detail->exact_location;
        }

        $this->resetBranchQuery();
        $this->setSession();
    }

    public function resetBranchQuery()
    {
        $this->reset(['branch_query']);
        $this->searchBranchQuery = null;
        $this->account_id = null;
    }

    // ---
    // SCHEDULE METHODS
    // ---

    public function addSheduleLine($date)
    {
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

        $this->setSession();
    }

    public function removeLine($date, $key)
    {
        $line = $this->month_days[$this->month][$date]['lines'][$key];
        
        if (!empty($line['id'])) {
            $this->month_days[$this->month][$date]['lines'][$key]['deleted'] = true;
            
            // Unlink trip if applicable
            if (isset($line['trip']) && !empty($line['trip'])) {
                $trip_data = $line['trip'];
                if (isset($trip_data['selected_trip']) && !empty($trip_data['selected_trip'])) {
                    if ($trip_data['source'] == 'trips') {
                        $activity_plan_trip = ActivityPlanDetailTrip::find($trip_data['selected_trip']);
                        $activity_plan_trip?->update(['activity_plan_detail_id' => NULL]);
                    } else {
                        $destination = ActivityPlanDetailTripDestination::where('id', $trip_data['selected_trip'])->first();
                        $destination?->update(['activity_plan_detail_id' => NULL]);
                    }
                }
            }
        } else {
            unset($this->month_days[$this->month][$date]['lines'][$key]);
        }

        // Re-index the array after unsetting an element to maintain consistency
        $this->month_days[$this->month][$date]['lines'] = array_values($this->month_days[$this->month][$date]['lines']);

        $this->setSession();
    }

    public function expandDate($date)
    {
        $this->expand_dates[$date] = !$this->expand_dates[$date];
    }

    public function minimizeAll()
    {
        foreach ($this->expand_dates as $date => $val) {
            $this->expand_dates[$date] = false;
        }
    }

    public function expandAll()
    {
        foreach ($this->expand_dates as $date => $val) {
            $this->expand_dates[$date] = true;
        }
    }

    // ---
    // PRIVATE METHODS
    // ---

    // This method is the single source of truth for generating the monthly data structure.
    protected function setMonthDays()
    {
        $this->last_day = date('t', strtotime($this->year . '-' . $this->month . '-01'));
        $days = [];
        $expand_dates = [];
        
        $activity_plan_data = Session::get('activity_plan_data');
        $session_details = $activity_plan_data[$this->year]['details'] ?? [];

        for ($i = 1; $i <= (int)$this->last_day; $i++) {
            $date = $this->year . '-' . $this->month . '-' . ($i < 10 ? '0' . $i : $i);
            $day_of_week = date('D', strtotime($date));
            
            $class = 'bg-light';
            if ($day_of_week === 'Sun') {
                $class = 'bg-navy';
            } elseif ($day_of_week === 'Sat') {
                $class = 'bg-secondary';
            }
            
            // Always initialize with an empty lines array to ensure consistency
            $lines = $session_details[$this->month][$date]['lines'] ?? [];
            
            $days[$date] = [
                'day' => $day_of_week,
                'date' => date('M', strtotime($this->year . '-' . $this->month . '-01')) . '. ' . ($i < 10 ? '0' . $i : $i),
                'class' => $class,
                'lines' => $lines,
            ];

            // Initialize expand state, default to false.
            $expand_dates[$date] = false;
        }
        
        // Use a consistent key for the month, and assign directly
        $this->month_days[$this->month] = $days;
        $this->expand_dates = $expand_dates;
    }

    protected function setSession()
    {
        $activity_plan_data = Session::get('activity_plan_data', []);
        
        // Overwrite the specific year's data to ensure the session is always in sync with the component's state
        $activity_plan_data[$this->year] = [
            'year' => $this->year,
            'month' => $this->month,
            // Assuming 'objectives' is handled elsewhere, if not, it should be part of this state
            'objectives' => $activity_plan_data[$this->year]['objectives'] ?? '',
            'details' => $this->month_days,
        ];

        Session::put('activity_plan_data', $activity_plan_data);
    }
}