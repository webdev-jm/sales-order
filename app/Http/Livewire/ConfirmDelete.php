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
                $this->name = $this->model->name;
                $this->route = '/company';
                break;
            case 'Discount':
                $this->model = Discount::findOrFail($model_id);
                $this->name = $this->model->description;
                $this->route = '/discount';
                break;
            case 'InvoiceTerm':
                $this->model = InvoiceTerm::findOrFail($model_id);
                $this->name = $this->model->description;
                $this->route = '/invoice-term';
                break;
            case 'Product':
                $this->model = Product::findOrFail($model_id);
                $this->name = '['.$this->model->stock_code.'] '.$this->model->description;
                $this->route = '/product';
                break;
            case 'PriceCode':
                $this->model = PriceCode::findOrFail($model_id);
                $this->name = '['.$this->model->company->name.'] '.$this->model->code;
                $this->route = '/price-code';
                break;
            case 'Account':
                $this->model = Account::findOrFail($model_id);
                $this->name = '['.$this->model->account_code.'] '.$this->model->account_name;
                $this->route = '/account';
                break;
            case 'Branch':
                $this->model = Branch::findOrFail($model_id);
                $this->name = '['.$this->model->branch_code.'] '.$this->model->branch_name;
                $this->route = '/branch';
                break;
            case 'SalesPerson':
                $this->model = SalesPerson::findOrFail($model_id);
                $this->name = '['.$this->model->code.'] '.$this->model->user->firstname.' '.$this->model->user->lastname;
                $this->route = '/sales-people';
                break;
            case 'User':
                $this->model = User::findOrFail($model_id);
                $this->name = $this->model->firstname.' '.$this->model->lastname;
                $this->route = '/user';
                break;
            case 'Role':
                $this->model = Role::findOrFail($model_id);
                $this->name = $this->model->name;
                $this->route = '/role';
                break;
            case 'SalesOrder':
                $this->model = SalesOrder::findOrFail($model_id);
                $this->name = $this->model->control_number;
                $this->route = '/sales-order';
                break;
            case 'OperationProcess':
                $this->model = OperationProcess::findOrFail($model_id);
                $this->name = $this->model->operation_process;
                $this->route = '/operation-process';
                break;
            case 'Region':
                $this->model = Region::findOrFail($model_id);
                $this->name = $this->model->region_name;
                $this->route = '/region';
                break;
            case 'Area':
                $this->model = Area::findOrFail($model_id);
                $this->name = $this->model->area_name;
                $this->route = '/area';
                break;
        }
    }

    public function render()
    {
        return view('livewire.confirm-delete');
    }
}
