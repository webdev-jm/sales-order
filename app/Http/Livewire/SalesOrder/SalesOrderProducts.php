<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;

class SalesOrderProducts extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $save_warning, $so_type;

    public $account, $uom, $search = '', $brands, $brand = '';
    public $quantity;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingBrand() {
        $this->resetPage();
    }

    public function mount() {
        $logged_account = auth()->user()->logged_account();
        $account = $logged_account->account;
        
        $this->account = $account;
        $this->brand = 'ALL';

        $order_data = Session::get('order_data');
        if(empty($this->quantity) && !empty($order_data)) {
            if(!empty($order_data['items'])) {
                foreach($order_data['items'] as $product_id => $item) {
                    foreach($item['data'] as $uom => $data) {
                        $this->quantity[$product_id][$uom] = $data['quantity'];
                        $this->uom[$product_id] = $uom;
                    }
                }
            }
        }
    }

    public function change() {
        if(isset($this->uom)) {
            foreach($this->uom as $product_id => $uom) {
                if(isset($this->quantity[$product_id])) {
                    foreach($this->quantity[$product_id] as $uom_key => $qty) {
                        if($uom_key != $uom) {
                            $this->quantity[$product_id][$uom] = $qty;
                            unset($this->quantity[$product_id][$uom_key]);
                        }
                    }
                }
            }
        }

        // show warning to save if items are more than 5
        if($this->so_type == 'Sales Orders / Add') {
            if(!empty($this->quantity) && count($this->quantity) >= 6) {
                $this->save_warning = 'Please save your progress to avoid losing data.';
            }
        }

        $this->emit('getTotal', $this->quantity);
    }

    public function render()
    {
        $special_products = $this->account->products;
        $references = $this->account->references;
        $hasReferences = $references->count() > 0;
        
        $query = Product::query();
        
        // Apply search filters
        $query->where(function ($q) {
            $q->where('stock_code', 'like', "%{$this->search}%")
              ->orWhere('description', 'like', "%{$this->search}%")
              ->orWhere('category', 'like', "%{$this->search}%")
              ->orWhere('size', 'like', "%{$this->search}%")
              ->orWhere('stock_uom', 'like', "%{$this->search}%")
              ->orWhere('order_uom', 'like', "%{$this->search}%")
              ->orWhere('other_uom', 'like', "%{$this->search}%")
              ->orWhere('brand', 'like', "%{$this->search}%")
              ->orWhereHas('references', function ($qry) {
                  $qry->where('account_reference', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%");
              });
        });
        
        // Apply account-based filtering
        if ($hasReferences) {
            $query->where(function ($q) use ($special_products) {
                $q->whereHas('references', function ($qry) {
                    $qry->where('account_id', $this->account->id);
                })->orWhereHas('price_codes', function ($qry) {
                    $qry->where('company_id', $this->account->company_id)
                        ->where('code', $this->account->price_code);
                });
                
                if ($special_products->isNotEmpty()) {
                    $q->orWhere(function ($qry) use ($special_products) {
                        $qry->where('special_product', 1)
                            ->whereIn('id', $special_products->pluck('id'));
                    });
                }
            });
        } else {
            $query->whereHas('price_codes', function ($q) {
                $q->where('company_id', $this->account->company_id)
                    ->where('code', $this->account->price_code);
            });
            
            if ($special_products->isNotEmpty()) {
                $query->where(function ($q) use ($special_products) {
                    $q->where('special_product', 0)
                      ->orWhere(function ($qry) use ($special_products) {
                          $qry->where('special_product', 1)
                              ->whereIn('id', $special_products->pluck('id'));
                      });
                });
            } else {
                $query->where('special_product', 0);
            }
        }
        
        // Apply brand filter
        if ($this->brand !== 'ALL') {
            $query->where('brand', $this->brand);
        }
        
        // Pagination
        $products = $query->paginate(10)->onEachSide(1);
        
        // Fetch brands
        $this->brands = Product::select('brand')->distinct()->orderBy('brand', 'ASC')
            ->whereHas('price_codes', function ($query) {
                $query->where('company_id', $this->account->company_id)
                      ->where('code', $this->account->price_code);
            })->get('brand');

        return view('livewire.sales-order.sales-order-products')->with([
            'products' => $products,
            'brands' => $this->brands
        ]);
    }
}
