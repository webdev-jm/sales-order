<?php

namespace App\Http\Livewire\SalesOrder;

use Livewire\Component;
use App\Models\Product;
use App\Models\PafDetail;
use App\Models\SalesOrderProductUomPaf;
use Illuminate\Support\Facades\Session;

class SalesOrderPaf extends Component
{
    public $account, $logged_account;
    public $product_id, $uom;
    public $product, $paf_details;
    public $uom_arr = [];
    public $product_order = [];
    public $paf_rows = [];

    protected $listeners = [
        'pafDetails' => 'showPafDetails',
    ];

    public function showPafDetails($product_id, $uom)
    {
        $this->product_id = $product_id;
        $this->uom = $uom;

        $this->product = Product::findOrFail($this->product_id);
        $this->uom_arr = array_unique([
            $this->product->stock_uom,
            $this->product->order_uom,
            $this->product->other_uom
        ]);

        $this->paf_details = PafDetail::where('sku_code', $this->product->stock_code)
            ->whereHas('paf', function($query) {
                $query->where('account_code', $this->account->account_code)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            })
            ->get();

        $order_data = Session::get('order_data');
        $this->product_order = $order_data['items'][$this->product_id] ?? [];

        if(empty($this->paf_rows)) {
            $paf_data = $order_data['items'][$this->product_id]['data'][$this->uom]['paf_rows'] ?? [];
            $this->paf_rows = array_map(function($row) {
                return [
                    'paf_number' => $row['paf_number'] ?? '',
                    'uom' => $row['uom'] ?? '',
                    'quantity' => $row['quantity'] ?? '',
                ];
            }, $paf_data);
        }
    }

    public function addRow() {
        $this->paf_rows[] = [
            'paf_number' => '',
            'uom' => '',
            'quantity' => ''
        ];
    }

    public function removeRow($index) {
        if(isset($this->paf_rows[$index])) {
            unset($this->paf_rows[$index]);
            $this->paf_rows = array_values($this->paf_rows);
        }
    }

    public function savePAF() {
        $this->validate([
            'paf_rows.*.paf_number' => 'required',
            'paf_rows.*.uom' => 'required',
            'paf_rows.*.quantity' => 'required|numeric|min:0'
        ]);

        $this->paf_rows = array_filter($this->paf_rows, function($row) {
            return !empty($row['paf_number']) && !empty($row['uom']) && !empty($row['quantity']);
        });
        if(empty($this->paf_rows)) {
            session()->flash('error', 'Please add at least one PAF row.');
            return;
        }
        $order_data = Session::get('order_data', []);
        // Ensure the structure exists
        if (!isset($order_data['items'][$this->product_id]['data'])) {
            $order_data['items'][$this->product_id]['data'] = [];
        }
        $order_data['items'][$this->product_id]['data'][$this->uom]['paf_rows'] = $this->paf_rows;
        Session::put('order_data', $order_data);
    }

    public function mount() {
        $this->logged_account = Session::get('logged_account');
        $this->account = $this->logged_account->account;
    }

    public function render()
    {
        return view('livewire.sales-order.sales-order-paf');
    }
}
