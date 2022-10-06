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

        $this->emit('getTotal', $this->quantity);
    }

    public function render()
    {
        $special_products = $this->account->products;

        if($this->brand == 'ALL') {

            if(!empty($special_products)) {
                $products = Product::whereHas('price_codes', function($query) {
                    $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
                })
                ->where(function($query) {
                    $query->where('stock_code', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('category', 'like', '%'.$this->search.'%')
                    ->orWhere('size', 'like', '%'.$this->search.'%')
                    ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('order_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('other_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('brand', 'like', '%'.$this->search.'%');
                })
                ->where(function($query) use ($special_products) {
                    $query->where('special_product', 0)
                    ->orWhere(function($qry) use ($special_products) {
                        $qry->where('special_product', 1)
                        ->WhereIn('id', $special_products->pluck('id'));
                    });
                })
                ->paginate(10)->onEachSide(1);
            } else {
                $products = Product::whereHas('price_codes', function($query) {
                    $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
                })
                ->where(function($query) {
                    $query->where('stock_code', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('category', 'like', '%'.$this->search.'%')
                    ->orWhere('size', 'like', '%'.$this->search.'%')
                    ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('order_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('other_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('brand', 'like', '%'.$this->search.'%');
                })
                ->where('special_product', 0)
                ->paginate(10)->onEachSide(1);
            }

            // // enable DF20004 to Phil Seven only
            // if($this->account->account_code == 1200015) {
            //     $products = Product::whereHas('price_code', function($query) {
            //         $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
            //     })
            //     ->where(function($query) {
            //         $query->where('stock_code', 'like', '%'.$this->search.'%')
            //         ->orWhere('description', 'like', '%'.$this->search.'%')
            //         ->orWhere('category', 'like', '%'.$this->search.'%')
            //         ->orWhere('size', 'like', '%'.$this->search.'%')
            //         ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('order_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('other_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('brand', 'like', '%'.$this->search.'%');
            //     })
            //     ->where('stock_code', '<>', 'KS99065')
            //     ->paginate(10)->onEachSide(1);
            // } else if(in_array($this->account->account_code, [5000001, 1200100, 1200081, 1200077])) {
            //      // KS99065 to accounts [5000001, 1200100, 1200081, 1200077]
            //     $products = Product::whereHas('price_code', function($query) {
            //         $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
            //     })
            //     ->where(function($query) {
            //         $query->where('stock_code', 'like', '%'.$this->search.'%')
            //         ->orWhere('description', 'like', '%'.$this->search.'%')
            //         ->orWhere('category', 'like', '%'.$this->search.'%')
            //         ->orWhere('size', 'like', '%'.$this->search.'%')
            //         ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('order_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('other_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('brand', 'like', '%'.$this->search.'%');
            //     })
            //     ->where('stock_code', '<>', 'DF20004')
            //     ->paginate(10)->onEachSide(1);
            // } else {
            //     $products = Product::whereHas('price_code', function($query) {
            //         $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
            //     })
            //     ->where(function($query) {
            //         $query->where('stock_code', 'like', '%'.$this->search.'%')
            //         ->orWhere('description', 'like', '%'.$this->search.'%')
            //         ->orWhere('category', 'like', '%'.$this->search.'%')
            //         ->orWhere('size', 'like', '%'.$this->search.'%')
            //         ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('order_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('other_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('brand', 'like', '%'.$this->search.'%');
            //     })
            //     ->where('stock_code', '<>', 'DF20004')
            //     ->where('stock_code', '<>', 'KS99065')
            //     ->paginate(10)->onEachSide(1);
            // }
        } else {

            if(!empty($special_products)) {
                $products = Product::whereHas('price_codes', function($query) {
                    $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
                })
                ->where(function($query) {
                    $query->where('brand', $this->brand);
                })
                ->where(function($query) {
                    $query->where('stock_code', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('category', 'like', '%'.$this->search.'%')
                    ->orWhere('size', 'like', '%'.$this->search.'%')
                    ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('order_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('other_uom', 'like', '%'.$this->search.'%');
                })
                ->where(function($query) use ($special_products) {
                    $query->where('special_product', 0)
                    ->orWhere(function($qry) use ($special_products) {
                        $qry->where('special_product', 1)
                        ->WhereIn('id', $special_products->pluck('id'));
                    });
                })
                ->paginate(10)->onEachSide(1);
            } else {

                $products = Product::whereHas('price_codes', function($query) {
                    $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
                })
                ->where(function($query) {
                    $query->where('brand', $this->brand);
                })
                ->where(function($query) {
                    $query->where('stock_code', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('category', 'like', '%'.$this->search.'%')
                    ->orWhere('size', 'like', '%'.$this->search.'%')
                    ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('order_uom', 'like', '%'.$this->search.'%')
                    ->orWhere('other_uom', 'like', '%'.$this->search.'%');
                })
                ->where('special_product', 0)
                ->paginate(10)->onEachSide(1);
            }

            // enable DF20004 to Phil Seven only
            // if($this->account->account_code == 1200015) {
            //     $products = Product::whereHas('price_codes', function($query) {
            //         $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
            //     })
            //     ->where(function($query) {
            //         $query->where('brand', $this->brand);
            //     })
            //     ->where(function($query) {
            //         $query->where('stock_code', 'like', '%'.$this->search.'%')
            //         ->orWhere('description', 'like', '%'.$this->search.'%')
            //         ->orWhere('category', 'like', '%'.$this->search.'%')
            //         ->orWhere('size', 'like', '%'.$this->search.'%')
            //         ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('order_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('other_uom', 'like', '%'.$this->search.'%');
            //     })
            //     ->where('stock_code', '<>', 'KS99065')
            //     ->paginate(10)->onEachSide(1);
            // } else if(in_array($this->account->account_code, [5000001, 1200100, 1200081, 1200077])) {
            //     // KS99065 to accounts [5000001, 1200100, 1200081, 1200077]
            //     $products = Product::whereHas('price_codes', function($query) {
            //         $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
            //     })
            //     ->where(function($query) {
            //         $query->where('brand', $this->brand);
            //     })
            //     ->where(function($query) {
            //         $query->where('stock_code', 'like', '%'.$this->search.'%')
            //         ->orWhere('description', 'like', '%'.$this->search.'%')
            //         ->orWhere('category', 'like', '%'.$this->search.'%')
            //         ->orWhere('size', 'like', '%'.$this->search.'%')
            //         ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('order_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('other_uom', 'like', '%'.$this->search.'%');
            //     })
            //     ->where('stock_code', '<>', 'DF20004')
            //     ->paginate(10)->onEachSide(1);
            // } else {
            //     $products = Product::whereHas('price_codes', function($query) {
            //         $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
            //     })
            //     ->where(function($query) {
            //         $query->where('brand', $this->brand);
            //     })
            //     ->where(function($query) {
            //         $query->where('stock_code', 'like', '%'.$this->search.'%')
            //         ->orWhere('description', 'like', '%'.$this->search.'%')
            //         ->orWhere('category', 'like', '%'.$this->search.'%')
            //         ->orWhere('size', 'like', '%'.$this->search.'%')
            //         ->orWhere('stock_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('order_uom', 'like', '%'.$this->search.'%')
            //         ->orWhere('other_uom', 'like', '%'.$this->search.'%');
            //     })
            //     ->where('stock_code', '<>', 'DF20004')
            //     ->where('stock_code', '<>', 'KS99065')
            //     ->paginate(10)->onEachSide(1);
            // }
        }
        
        $this->brands = Product::select('brand')->distinct()->orderBy('brand', 'ASC')
        ->whereHas('price_codes', function($query) {
            $query->where('company_id', $this->account->company_id)->where('code', $this->account->price_code);
        })
        ->get('brand');

        return view('livewire.sales-order.sales-order-products')->with([
            'products' => $products,
            'brands' => $this->brands
        ]);
    }
}
