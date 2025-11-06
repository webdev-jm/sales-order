<?php

namespace App\Http\Livewire\Accounts;

use Livewire\Component;
use App\Models\Account;
use App\Models\Branch;
use App\Models\BranchLogin;
use App\Models\Region;
use App\Models\Classification;
use App\Models\Area;
use App\Models\BranchAddress;

use Illuminate\Support\Facades\Http;

use AccountLoginModel;

use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

use Livewire\WithPagination;

class AccountBranchLogin extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $scheduled;

    public $account, $branch_accuracy, $branch_longitude, $branch_latitude;
    public $branch, $accuracy, $longitude, $latitude;
    public $search;

    public $branch_form = false;
    public $regions, $classifications, $areas;
    public $branch_code, $branch_name, $region, $classification, $area;

    protected $listeners = ['setAccount' => 'setAccount'];

    public function updatingSearch()
    {
        $this->resetPage('branchPage');
    }

    public function showScheduled() {
        if(!empty($this->scheduled)) {
            $this->reset('scheduled');
        } else {
            $curr_date = date('Y-m-d',time());
            $this->scheduled = $curr_date;
        }

        $this->resetPage('branchPage');
    }

    public function submitAddBranch() {
        $this->validate([
            'branch_code' => [
                'max:255', 'nullable', Rule::unique((new Branch)->getTable())
            ],
            'branch_name' => [
                'required'
            ],
            'region' => [
                'required'
            ],
            'classification' => [
                'required'
            ],
            'area' => [
                'required'
            ]
        ]);

        $branch = new Branch([
            'account_id' => $this->account->id,
            'region_id' => $this->region,
            'classification_id' => $this->classification,
            'area_id' => $this->area,
            'branch_code' => $this->branch_code,
            'branch_name' => $this->branch_name,
        ]);
        $branch->save();

        $this->branch_form = false;
        $this->branch = $branch;
    }

    public function addBranch() {
        $this->branch_form = true;
        $this->branch = 1;

        $this->regions = Region::orderBy('region_name', 'ASC')->get();
        $this->classifications = Classification::orderBy('classification_name', 'ASC')->get();
        $this->areas = Area::orderBy('area_name', 'ASC')->get();

        $this->reset('branch_code');
        $this->reset('branch_name');
        $this->reset('region');
        $this->reset('classification');
        $this->reset('area');
    }

    public function branchBack() {
        $this->branch_form = false;
        $this->reset('branch');
        $this->reset('branch_code');
        $this->reset('branch_name');
        $this->reset('region');
        $this->reset('classification');
        $this->reset('area');
    }

    public function login() {
        $this->validate([
            'accuracy' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
        ]);

        // check if logged in to account or branch
        $logged_account = AccountLoginModel::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();

        $logged_branch = BranchLogin::where('user_id', auth()->user()->id)
        ->whereNull('time_out')
        ->first();

        if(empty($logged_account) && empty($logged_branch)) {
            $branch_login = new BranchLogin([
                'user_id' => auth()->user()->id,
                'branch_id' => $this->branch->id,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'accuracy' => $this->accuracy,
                'time_in' => now(),
            ]);
            $branch_login->save();

            // save to branch address if not exist
            $branch_address = BranchAddress::where('branch_id', $this->branch->id)
                ->first();
            if(empty($branch_address)) {
                $api_key = 'pk.4d088f5b2675bd22fa5f84fef3ac3c38';
                $lat = $this->latitude;
                $lon = $this->longitude;

                $response = Http::get('https://us1.locationiq.com/v1/reverse.php?key='.$api_key.'&lat='.$lat.'&lon='.$lon.'&format=json&addressdetails=1&normalizeaddress=1&extratags=1');

                if ($response->successful()) {
                    $data = $response->json();

                    $address = $data['address'] ?? [];
                    $houseNumber = $address['house_number'] ?? '';
                    $road = $address['road'] ?? '';
                    $quarter = $address['quarter'] ?? '';
                    $suburb = $address['suburb'] ?? '';
                    $city = $address['city'] ?? '';
                    $state = $address['state'] ?? '';
                    $postcode = $address['postcode'] ?? '';
                    $country = $address['country'] ?? '';
                    $displayName = $data['display_name'] ?? '';

                    $branch_address = new BranchAddress([
                        'branch_id' => $this->branch->id,
                        'latitude' => $this->latitude,
                        'longitude' => $this->longitude,
                        'street1' => $houseNumber.' '.$road,
                        'street2' => $quarter.' '.$suburb,
                        'city' => $city,
                        'state' => $state,
                        'zip' => $postcode,
                        'country' => $country,
                        'address' => $displayName,
                    ]);
                    $branch_address->save();
                }

            }

            // logs
            activity('login')
            ->performedOn($branch_login)
            ->log(':causer.firstname :causer.lastname has logged in to branch '.($this->branch->account->short_name ?? '').' - '.$this->branch->branch_name);

            Session::put('logged_branch', $branch_login);
        }

        return redirect()->to('/home');
    }

    public function selectBranch($branch_id) {
        $this->branch = Branch::findOrFail($branch_id);
    }

    public function resetBranch() {
        $this->reset('branch');
    }

    public function loadLocation() {
        $this->dispatchBrowserEvent('reloadLocation');
    }

    public function setAccount($account_id) {
        $this->account = Account::findOrFail($account_id);
        $this->reset('search');
    }

    public function render()
    {
        $branches = [];
        if(!empty($this->account)) {
            if(!empty($this->scheduled)) {
                $branches = Branch::orderBy('branch_code', 'ASC')
                ->where('account_id', $this->account->id)
                ->whereHas('schedules', function($query) {
                    $query->where('date', $this->scheduled)
                    ->where('user_id', auth()->user()->id)
                    ->whereNull('status');
                })
                ->where(function($query) {
                    $query->where('branch_code', 'like', '%'.$this->search.'%')
                    ->orWhere('branch_name', 'like', '%'.$this->search.'%');
                })
                ->paginate(12, ['*'], 'branchPage')
                ->onEachSide(1)->appends(request()->query());
            } else {
                $branches = Branch::orderBy('branch_code', 'ASC')
                ->where('account_id', $this->account->id)
                ->where(function($query) {
                    $query->where('branch_code', 'like', '%'.$this->search.'%')
                    ->orWhere('branch_name', 'like', '%'.$this->search.'%');
                })
                ->paginate(12, ['*'], 'branchPage')
                ->onEachSide(1)->appends(request()->query());
            }
        }

        return view('livewire.accounts.account-branch-login')->with([
            'branches' => $branches
        ]);
    }
}
