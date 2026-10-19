<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

use App\Models\Company;
use App\Models\Discount;
use App\Models\InvoiceTerm;
use App\Models\Product;
use App\Models\Classification;
use App\Models\PriceCode;
use App\Models\Account;
use App\Models\Branch;
use App\Models\SalesPerson;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\SalesOrder;
use App\Models\PPUForm;
use App\Models\OperationProcess;
use App\Models\Region;
use App\Models\Area;
use App\Models\AccountProductReference;
use App\Models\CostCenter;
use App\Models\ActivityPlan;
use App\Models\OrganizationStructure;
use App\Models\Brand;
use App\Models\Department;
use App\Models\Salesman;
use App\Models\SalesmenLocation;
use App\Models\SalesOrderCutOff;

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

    /**
     * Every record type the delete modal accepts: its model, the permission the index page
     * checks before showing the delete button, and the page to return to.
     * A type flagged 'draft' may also be deleted by anyone while the record is still a draft,
     * matching the delete button on that page.
     *
     * @var array<string, array{model: class-string<Model>, permission: string, route: string, draft?: bool}>
     */
    private const TYPES = [
        'Company'                 => ['model' => Company::class, 'permission' => 'company delete', 'route' => '/company'],
        'Discount'                => ['model' => Discount::class, 'permission' => 'discount delete', 'route' => '/discount'],
        'InvoiceTerm'             => ['model' => InvoiceTerm::class, 'permission' => 'invoice term delete', 'route' => '/invoice-term'],
        'Product'                 => ['model' => Product::class, 'permission' => 'product delete', 'route' => '/product'],
        'Classification'          => ['model' => Classification::class, 'permission' => 'classification delete', 'route' => '/classification'],
        'PriceCode'               => ['model' => PriceCode::class, 'permission' => 'price code delete', 'route' => '/price-code'],
        'Account'                 => ['model' => Account::class, 'permission' => 'account delete', 'route' => '/account'],
        'Branch'                  => ['model' => Branch::class, 'permission' => 'branch delete', 'route' => '/branch'],
        'SalesPerson'             => ['model' => SalesPerson::class, 'permission' => 'sales person delete', 'route' => '/sales-people'],
        'User'                    => ['model' => User::class, 'permission' => 'user delete', 'route' => '/user'],
        'Role'                    => ['model' => Role::class, 'permission' => 'role delete', 'route' => '/role'],
        'SalesOrder'              => ['model' => SalesOrder::class, 'permission' => 'sales order delete', 'route' => '/sales-order', 'draft' => true],
        'PPUForm'                 => ['model' => PPUForm::class, 'permission' => 'ppu form delete', 'route' => '/ppu-form', 'draft' => true],
        'OperationProcess'        => ['model' => OperationProcess::class, 'permission' => 'operation process delete', 'route' => '/operation-process'],
        'Region'                  => ['model' => Region::class, 'permission' => 'region delete', 'route' => '/region'],
        'Area'                    => ['model' => Area::class, 'permission' => 'area delete', 'route' => '/area'],
        'AccountProductReference' => ['model' => AccountProductReference::class, 'permission' => 'account reference delete', 'route' => '/reference-account'],
        'CostCenter'              => ['model' => CostCenter::class, 'permission' => 'cost center delete', 'route' => '/cost-center'],
        'ActivityPlan'            => ['model' => ActivityPlan::class, 'permission' => 'mcp delete', 'route' => '/mcp'],
        'OrganizationStructure'   => ['model' => OrganizationStructure::class, 'permission' => 'organizational structure access', 'route' => '/organizational-structure'],
        'Brand'                   => ['model' => Brand::class, 'permission' => 'brand delete', 'route' => '/brand'],
        'Department'              => ['model' => Department::class, 'permission' => 'department delete', 'route' => '/department'],
        'Salesman'                => ['model' => Salesman::class, 'permission' => 'salesman delete', 'route' => '/salesman'],
        'SalesmanLocation'        => ['model' => SalesmenLocation::class, 'permission' => 'salesman location delete', 'route' => '/salesman-location'],
        'SalesOrderCutOff'        => ['model' => SalesOrderCutOff::class, 'permission' => 'so cut-off delete', 'route' => '/cut-off'],
    ];

    public function submitForm() {
        $this->error_message = '';

        $this->validate([
            'password' => 'required'
        ]);

        $type = $this->model instanceof Model ? $this->typeOf($this->model) : null;

        if(is_null($type) || !$this->canDelete($type, $this->model)) {
            $this->error_message = 'You are not allowed to delete this record.';
            return;
        }

        // check password
        if(!Hash::check($this->password, auth()->user()->password)) { // invalid
            $this->error_message = 'incorrect password.';
        } else { // delete function
            $name = $this->describe($type, $this->model);
            $this->model->delete();

            activity('delete')
            ->performedOn($this->model)
            ->withProperties($this->model)
            ->log(':causer.firstname :causer.lastname has deleted '.$name);

            return redirect()->to($this->returnRoute($type, $this->model))->with([
                'message_success' => $name.' was deleted.'
            ]);
        }

    }

    public function setModel($type, $model_id) {
        $this->reset('model', 'name', 'route', 'password', 'error_message');
        $this->resetErrorBag();

        if(!isset(self::TYPES[$type])) {
            $this->error_message = 'This record cannot be deleted here.';
            return;
        }

        $model_class = self::TYPES[$type]['model'];
        $model = $model_class::findOrFail($type === 'Account' ? decrypt($model_id) : $model_id);

        if(!$this->canDelete($type, $model)) {
            $this->error_message = 'You are not allowed to delete this record.';
            return;
        }

        $this->model = $model;
        $this->name = $this->describe($type, $model);
        $this->route = $this->returnRoute($type, $model);
    }

    private function typeOf(Model $model): ?string {
        foreach(self::TYPES as $type => $config) {
            if(get_class($model) === $config['model']) {
                return $type;
            }
        }

        return null;
    }

    private function canDelete(string $type, Model $model): bool {
        $config = self::TYPES[$type];

        if(auth()->user()->can($config['permission'])) {
            return true;
        }

        return !empty($config['draft']) && $model->status == 'draft';
    }

    private function returnRoute(string $type, Model $model): string {
        if($type === 'OrganizationStructure') {
            return self::TYPES[$type]['route'].'?type='.$model->type;
        }

        return self::TYPES[$type]['route'];
    }

    private function describe(string $type, Model $model): string {
        return match($type) {
            'Company' => 'company '.$model->name,
            'Discount' => 'discount '.$model->description,
            'InvoiceTerm' => 'invoice term '.$model->description,
            'Product' => 'product ['.$model->stock_code.'] '.$model->description,
            'Classification' => 'classification ['.$model->classification_code.'] '.$model->classification_name,
            'PriceCode' => 'price code ['.($model->company->name ?? '-').'] '.$model->code,
            'Account' => 'account ['.$model->account_code.'] '.$model->account_name,
            'Branch' => 'branch ['.$model->branch_code.'] '.$model->branch_name,
            'SalesPerson' => 'sales person ['.$model->code.'] '.($model->user ? $model->user->fullName() : '-'),
            'User' => 'user '.$model->fullName(),
            'Role' => 'role '.$model->name,
            'SalesOrder' => 'sales order '.$model->control_number,
            'PPUForm' => 'ppu form '.$model->control_number,
            'OperationProcess' => 'operation process '.$model->operation_process,
            'Region' => 'region '.$model->region_name,
            'Area' => 'area '.$model->area_name,
            'AccountProductReference' => 'product reference '.$model->account_reference,
            'CostCenter' => 'cost center '.$model->cost_center,
            'ActivityPlan' => 'activity plan '.$model->year,
            'OrganizationStructure' => 'Org structure '.($model->job_title->job_title ?? '-'),
            'Brand' => 'Brand '.$model->brand,
            'Department' => 'department ['.$model->department_code.'] '.$model->department_name,
            'Salesman' => 'salesman ['.$model->code.'] '.$model->name,
            'SalesmanLocation' => 'salesman location '.$model->province.' '.$model->city,
            'SalesOrderCutOff' => 'cut-off '.date('Y-m-d h:i a', (int) $model->start_date).' to '.date('Y-m-d h:i a', (int) $model->end_date),
        };
    }

    public function render()
    {
        return view('livewire.confirm-delete');
    }
}
