<?php

namespace App\Http\Livewire\SalesOrder\Multiple;

use Livewire\Component;
use App\Models\Discount;

class Summary extends Component
{
    public $so_data;
    public $logged_account;
    public $po_data;
    public $grand_total;

    protected $listeners = [
        'setSummary' => 'getSoData'
    ];

    public function getSoData($so_data, $logged_account) {
        $this->so_data = $so_data;
        $this->logged_account = $logged_account;

        $this->processData();
    }

    private function processData() {
        $this->po_data = [];
        $this->grand_total = [
            'quantity' => 0,
            'amount' => 0,
            'net_amount' => 0,
            'po_value' => 0,
        ];
        if(!empty($this->so_data)) {
            foreach($this->so_data as $po_number => $data) {
                $ship_to_name = '';
                $address = '';
                if(!empty($data['shipping_address'])) {
                    $ship_to_name = '['.$data['shipping_address']['address_code'].'] '.$data['shipping_address']['ship_to_name'];
                    $addressParts = [
                        $data['shipping_address']['building'],
                        $data['shipping_address']['street'],
                        $data['shipping_address']['city'],
                        $data['shipping_address']['postal']
                    ];
                    $address = implode(', ', array_filter($addressParts, fn($v) => !empty($v)));
                }

                $total_quantity = 0;
                $total_amount = 0;
                $net_amount = 0;
                foreach($data['lines'] as $line_data) {
                    $total_quantity += $line_data['quantity'];
                    $total_amount += $line_data['total'];
                    $net_amount += $line_data['total_less_discount'];
                }

                // apply acount discount
                if(!empty($this->logged_account['account']['discount_id'])) {
                    $discount = Discount::find($this->logged_account['account']['discount_id']);
                    $discounts = [$discount->discount_1, $discount->discount_2, $discount->discount_3];

                    foreach ($discounts as $discountValue) {
                        if ($discountValue > 0) {
                            $net_amount = $net_amount * ((100 - $discountValue) / 100);
                        }
                    }
                }

                $this->po_data[$po_number] = [
                    'po_number' =>$po_number,
                    'ship_to_name' => $ship_to_name,
                    'address'=> $address,
                    'ship_date' => $data['ship_date'],
                    'total_quantity' => $total_quantity,
                    'total_amount' => $total_amount,
                    'net_amount' => $net_amount,
                    'po_value' => $data['po_value'],
                ];

                $this->grand_total['quantity'] += $total_quantity;
                $this->grand_total['amount'] += $total_amount;
                $this->grand_total['net_amount'] += $net_amount;
                $this->grand_total['po_value'] += $data['po_value'];

            }

        }
    }

    public function finalizeAll() {
        $this->emit('finalizeAll', 'finalized');
    }

    public function render()
    {
        return view('livewire.sales-order.multiple.summary');
    }

}
