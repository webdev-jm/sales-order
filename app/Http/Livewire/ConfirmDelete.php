<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;

use App\Models\Company;
use App\Models\Discount;
use App\Models\InvoiceTerm;
use App\Models\Product;
use App\Models\PriceCode;
use App\Models\Account;
use App\Models\Branch;
use App\Models\SalesPerson;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\SalesOrder;
use App\Models\OperationProcess;
use App\Models\Region;
use App\Models\Area;
use App\Models\AccountProductReference;
use App\Models\CostCenter;
use App\Models\ActivityPlan;
use App\Models\District;
use App\Models\OrganizationStructure;

class ConfirmDelete extends Component
{

    public $password;
    public $error_message;
    public $model;
    public $name;
    public $route;

    protected $listeners = [
        'setDeleteModel' => 'setModel'
    ];

    public function submitForm() {
        $this->error_message = '';

        $this->validate([
            'password' => 'required'
        ]);

        // check password
        if(!Hash::check($this->password, auth()->user()->password)) { // invalid
            $this->error_message = 'incorrect password.';
        } else { // delete function
            $this->model->delete();

            activity('delete')
            ->performedOn($this->model)
            ->withProperties($this->model)
            ->log(':causer.firstname :causer.lastname has deleted '.$this->name);

            return redirect()->to($this->route)->with([
                'message_success' => $this->name.' was deleted.'
            ]);
        }

    }

    public function setModel($type, $model_id) {
        switch($type) {
            case 'Company':
                $this->model = Company::findOrFail($model_id);
                $this->name = 'company '.$this->model->name;
                $this->route = '/company';
                break;
            case 'Discount':
                $this->model = Discount::findOrFail($model_id);
                $this->name = 'discount '.$this->model->description;
                $this->route = '/discount';
                break;
            case 'InvoiceTerm':
                $this->model = InvoiceTerm::findOrFail($model_id);
                $this->name = 'invoice term '.$this->model->description;
                $this->route = '/invoice-term';
                break;
            case 'Product':
                $this->model = Product::findOrFail($model_id);
                $this->name = 'product ['.$this->model->stock_code.'] '.$this->model->description;
                $this->route = '/product';
                break;
            case 'PriceCode':
                $this->model = PriceCode::findOrFail($model_id);
                $this->name = 'price code ['.$this->model->company->name.'] '.$this->model->code;
                $this->route = '/price-code';
                break;
            case 'Account':
                $this->model = Account::findOrFail($model_id);
                $this->name = 'account ['.$this->model->account_code.'] '.$this->model->account_name;
                $this->route = '/account';
                break;
            case 'Branch':
                $this->model = Branch::findOrFail($model_id);
                $this->name = 'branch ['.$this->model->branch_code.'] '.$this->model->branch_name;
                $this->route = '/branch';
                break;
            case 'SalesPerson':
                $this->model = SalesPerson::findOrFail($model_id);
                $this->name = 'sales person ['.$this->model->code.'] '.$this->model->user->fullName();
                $this->route = '/sales-people';
                break;
            case 'User':
                $this->model = User::findOrFail($model_id);
                $this->name = 'user '.$this->model->fullName();
                $this->route = '/user';
                break;
            case 'Role':
                $this->model = Role::findOrFail($model_id);
                $this->name = 'role '.$this->model->name;
                $this->route = '/role';
                break;
            case 'SalesOrder':
                $this->model = SalesOrder::findOrFail($model_id);
                $this->name = 'sales order '.$this->model->control_number;
                $this->route = '/sales-order';
                break;
            case 'OperationProcess':
                $this->model = OperationProcess::findOrFail($model_id);
                $this->name = 'operation process '.$this->model->operation_process;
                $this->route = '/operation-process';
                break;
            case 'Region':
                $this->model = Region::findOrFail($model_id);
                $this->name = 'region '.$this->model->region_name;
                $this->route = '/region';
                break;
            case 'Area':
                $this->model = Area::findOrFail($model_id);
                $this->name = 'area '.$this->model->area_name;
                $this->route = '/area';
                break;
            case 'AccountProductReference':
                $this->model = AccountProductReference::findOrFail($model_id);
                $this->name = 'product reference '.$this->model->account_reference;
                $this->route = '/reference-account';
                break;
            case 'CostCenter':
                $this->model = CostCenter::findOrFail($model_id);
                $this->name = 'cost center '.$this->model->cost_center;
                $this->route = '/cost-center';
                break;
            case 'ActivityPlan':
                $this->model = ActivityPlan::findOrFail($model_id);
                $this->name = 'activity plan '.$this->model->year;
                $this->route = '/mcp';
                break;
            case 'District':
                $this->model = District::findOrFail($model_id);
                $this->name = 'district '.$this->model->district_name;
                $this->route = '/district';
                break;
            case 'OrganizationStructure':
                $this->model = OrganizationStructure::findOrFail($model_id);
                $this->name = 'Org structure '.$this->model->job_title->job_title;
                $this->route = '/organizational-structure?type='.$this->model->type;
                break;
        }
    }

    public function render()
    {
        return view('livewire.confirm-delete');
    }
}
