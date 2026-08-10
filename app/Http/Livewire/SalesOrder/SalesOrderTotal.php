<?php

namespace App\Http\Livewire\SalesOrder;

use App\Services\AccountLoginResolver;
use App\Services\SalesOrderService;
use Livewire\Component;
use App\Models\Product;

use Illuminate\Support\Facades\Session;

use Livewire\WithPagination;

class SalesOrderTotal extends Component
{
    use WithPagination;

    public $logged_account,
    $account,
    $discount,
    $po_value = 0;

    public $po_message = '';

    public $orders = [];

    public $total = '0.00';
    public $grand_total = '0.00';

    protected $listeners = ['getTotal'];
    protected $rules = [
        'po_number' => [
            'required', 'min:3'
        ]
    ];

    protected $salesOrderService;

    public function boot(SalesOrderService $salesOrderService)
    {
        $this->salesOrderService = $salesOrderService;
    }

    public function change_po_value() {
        // compare with total
        if($this->po_value != '') {
            if($this->po_value == str_replace(',', '', $this->total)) {
                $this->po_message = '';
            } else {
                $this->po_message = 'PO value does not match the total.';
            }
        } else {
            $this->po_message = 'Required';
        }

        $this->orders['po_value'] = $this->po_value;
    }

    public function getTotal($product_details) {
        $this->processDetails($product_details);
        $this->emit('saveData');
    }

    private function uomQuantityAllocation($quantity, $product_id, $uom) {
        $data = [];

        // $product = Product::findOrFail($product_id);

        // // Identify which UOM is 'CS'
        // $cs_uom = null;
        // if ($product->stock_uom == 'CS') {
        //     $cs_uom = 'stock_uom';
        // } elseif ($product->order_uom == 'CS') {
        //     $cs_uom = 'order_uom';
        // } elseif ($product->other_uom == 'CS') {
        //     $cs_uom = 'other_uom';
        // }

        // if($uom !== 'CS' && !empty($cs_uom)) {
        //     $cs_quantity = $this->quantityConversion($quantity, $product->{$cs_uom.'_conversion'}, $product->{$cs_uom.'_operator'}, true);

        //     // Extract decimal part if exists
        //     $decimal_quantity = $cs_quantity - floor($cs_quantity);
        //     if ($decimal_quantity > 0) {
        //         $qty = $this->quantityConversion(
        //             $decimal_quantity,
        //             $product->{$cs_uom . '_conversion'},
        //             $product->{$cs_uom . '_operator'},
        //             false
        //         );
        //         $data[$uom] = (int) floor($qty);
        //     }

        //     $data['CS'] = (int) floor($cs_quantity);
        // } else {
        //     $data[$uom] = $quantity;
        // }

        $data[$uom] = $quantity;

        return $data;
    }

    /**
     * Recalculate the running order totals shown beside the product list.
     *
     * The arithmetic lives in SalesOrderService::calculateOrderTotals() so the
     * live screen and the stored order can never drift apart.
     *
     * @param  array<int, array<string, float|int|string>>  $product_details  [product_id => [uom => quantity]]
     */
    public function processDetails($product_details) {
        $service_input = $this->buildServiceInput($product_details);

        $orders = $this->salesOrderService->calculateOrderTotals($service_input, $this->account);

        $this->restoreLineDetails($orders, $service_input);

        $orders['po_value'] = $this->po_value;

        $this->total = number_format($orders['total'], 2);
        $this->grand_total = number_format($orders['grand_total'], 2);
        $this->orders = $orders;
    }

    /**
     * Shape the quantities coming from the browser into the structure the
     * service expects: [product_id => ['product' => Product, 'data' => [uom => ['quantity' => qty]]]].
     */
    private function buildServiceInput($product_details): array {
        if(empty($product_details)) {
            return [];
        }

        $products = Product::whereIn('id', array_keys($product_details))->get()->keyBy('id');

        $service_input = [];
        foreach($product_details as $product_id => $details) {
            $product = $products[$product_id] ?? null;
            if(empty($product)) {
                continue;
            }

            $uom_data = [];
            foreach((array) $details as $c_uom => $c_quantity) {
                // Clearing the quantity box posts back an empty string, which is a
                // TypeError once it reaches the addition below.
                $c_quantity = is_numeric($c_quantity) ? (float) $c_quantity : 0.0;

                // uomQuantityAllocation(quantity, product_id, uom)
                foreach($this->uomQuantityAllocation($c_quantity, $product->id, $c_uom) as $uom => $quantity) {
                    $uom_data[$uom]['quantity'] = ($uom_data[$uom]['quantity'] ?? 0) + $quantity;
                }
            }

            $service_input[$product_id] = [
                'product' => $product,
                'data' => $uom_data,
            ];
        }

        return $service_input;
    }

    /**
     * Re-attach the per-line values the service does not compute: the warehouse
     * shown on the order screen and any PAF rows the user already captured
     * through the PAF modal (which are only held in the session).
     */
    private function restoreLineDetails(array &$orders, array $service_input): void {
        $session_items = Session::get('order_data')['items'] ?? [];

        foreach($orders['items'] ?? [] as $product_id => $details) {
            $product = $service_input[$product_id]['product'] ?? null;

            foreach($details['data'] ?? [] as $uom => $row) {
                $orders['items'][$product_id]['data'][$uom]['warehouse'] = !empty($product) && $product->order_uom !== $uom
                    ? $product->warehouse
                    : NULL;

                $paf_rows = $session_items[$product_id]['data'][$uom]['paf_rows'] ?? [];
                if(!empty($paf_rows)) {
                    $orders['items'][$product_id]['data'][$uom]['paf_rows'] = $paf_rows;
                }
            }
        }
    }

    public function mount() {
        $this->logged_account = app(AccountLoginResolver::class)->resolve();
        $this->account = $this->logged_account->account;
        $this->discount = $this->account->discount;

        $order_data = Session::get('order_data');
        if(empty($this->orders) && !empty($order_data)) {
            $this->orders = $order_data;
        }

        if(!empty($order_data['total'])) {
            $this->total = number_format($order_data['total'], 2);
            $this->grand_total = number_format($order_data['grand_total'], 2);
            $this->po_value = $order_data['po_value'];
        }
    }

    public function render()
    {
        if(!empty($this->orders)) {
            Session::put('order_data', $this->orders);
        }
        return view('livewire.sales-order.sales-order-total');
    }
}
