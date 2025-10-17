<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use App\Models\Product;
use App\Models\SalesOrder;

use Carbon\Carbon;

Trait SoXmlTrait {

    public $url_link = '192.168.11.240/refreshable/public/api';

    public function generateXml($sales_order) {
        $parts = $this->convertData($sales_order);
        foreach($parts as $key => $data) {
            $part = $key + 1;
            $xml = $this->arrayToXml($data);

            $filename = $sales_order->po_number.'-'.$part.'.xml';

            // $ftp = Storage::disk('DFM');

            // change connection for each accounts
            if($sales_order->account_login->account->company->name == 'BEVI') {
                $ftp = Storage::disk('ftp_bevi');
                $ftp->put('BEVI-test/Incoming/SalesOrder/'.$filename, $xml);
            } else if($sales_order->account_login->account->company->name == 'BEVA') {
                $ftp = Storage::disk('ftp_beva');
                $ftp->put('BEVA-test/Incoming/SalesOrder/'.$filename, $xml);
            }
        }

        return $sales_order->po_number.'-'.$part.'.xml file created successfully.';
    }

    private function arrayToXml($data) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;  // Enable formatting and indentation

        $salesOrders = $dom->createElement('SalesOrders');
        $salesOrders->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema-instance');
        $salesOrders->setAttribute('xsd:noNamespaceSchemaLocation', 'SORTOIDOC.XSD');
        $dom->appendChild($salesOrders);

        $this->arrayToXmlHelper($data, $dom, $salesOrders);

        return $dom->saveXML();
    }

    private function arrayToXmlHelper($data, $dom, &$parent) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($key === 'StockLine') {
                    // Special handling for repeated 'StockLine' key within 'OrderDetails'
                    foreach ($value as $item) {
                        $subNode = $dom->createElement($key);
                        $parent->appendChild($subNode);
                        $this->arrayToXmlHelper($item, $dom, $subNode);
                    }
                } else {
                    $subNode = $dom->createElement($key);
                    $parent->appendChild($subNode);
                    $this->arrayToXmlHelper($value, $dom, $subNode);
                }
            } else {
                $child = $dom->createElement("$key", htmlspecialchars("$value"));
                $parent->appendChild($child);
            }
        }
    }

    private function convertData($sales_order) {

        $company = $sales_order->account_login->account->company->name;

        $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('API_TOKEN_SYSPRODATA'),
                'account_code' => $sales_order->account_login->account->account_code,
                'company' => $company
            ])
            ->get($this->url_link.'/so/ar_customer');

        $customer = $response->json()['data'];

        $details = $sales_order->order_products;
        $parts = array_unique($details->pluck('part')->toArray());

        $so_parts = array();
        foreach($parts as $part) {
            $so_details = $details->where('part', $part);

            $trade_discounts = $this->getTradeDiscounts($company, $details, $sales_order->account_login->account->account_code);

            $data = [
                'Orders' => [
                    'OrderHeader' => [
                        'CustomerPoNumber'              => $sales_order->po_number.'-'.$part,
                        'Customer'                      => $sales_order->account_login->account->account_code,
                        'OrderDate'                     => $sales_order->order_date,
                        'ShippingInstrs'                => $sales_order->shipping_instruction ?? '',
                        'RequestedShipDate'             => $sales_order->ship_date ?? '',
                        'OrderComments'                 => $sales_order->control_number,
                        'OrderDiscPercent1'             => $trade_discounts[0] ?? '',
                        'OrderDiscPercent2'             => $trade_discounts[0] ?? '',
                        'OrderDiscPercent3'             => $trade_discounts[0] ?? '',
                        'SalesOrderPromoQualityAction'  => 'W',
                        'SalesOrderPromoSelectAction'   => 'A',
                        'MultiShipCode'                 => $sales_order->shipping_address ?? '',
                    ],
                ]
            ];

            $num = 0;
            foreach($so_details as $detail) {
                foreach($detail->product_uoms as $uom) {
                    $num++;

                    $price_code = $this->getPriceCode($company, $detail->product->stock_code);

                    $data['Orders']['OrderDetails']['StockLine'][] = [
                        'CustomerPoLine'    => $num,
                        'Warehouse'         => $customer['warehouse'] ?? '',
                        'StockCode'         => $detail->product->stock_code,
                        'OrderQty'          => $uom->quantity,
                        'OrderUom'          => $uom->uom,
                        'PriceUom'          => $uom->uom,
                        'PriceCode'         => $price_code,
                    ];
                }
            }

            $so_parts[] = $data;
        }

        return $so_parts;
    }

    private function getTradeDiscounts($company, $details, $customer) {
        $trade_disc1 = '';
        $trade_disc2 = '';
        $trade_disc3 = '';
        if($company == 'BEVA') {
            $check = Product::where('product_class', 'CRS')
                ->whereIn('id', $details->pluck('product_id')->toArray())
                ->first();
            // get trade discount
            if($check) {
                $trade_disc1 = 30;
                $trade_disc2 = 0;
                $trade_disc3 = 0;
            } else {
                $check2 = Product::whereIn('stock_code', ['KS01046', 'KS01047'])
                    ->whereIn('id', $details->pluck('product_id')->toArray())
                    ->first();
                if($check2) {
                    $trade_disc1 = 12;
                    $trade_disc2 = 0;
                    $trade_disc3 = 0;
                }
            }
        } else {
            // get trade discount
            if($customer == '1200008') {
                $check = Product::where('product_class', 'DEF')
                    ->where('category', 'ALCOHOL')
                    ->whereIn('id', $details->pluck('product_id')->toArray())
                    ->first();

                if($check) {
                    $trade_disc1 = 15;
                }
            }
        }

        return [$trade_disc1, $trade_disc2, $trade_disc3];
    }

    private function getPriceCode($company, $stock_code) {
        $price_code = '';

        try {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('API_TOKEN_SYSPRODATA'),
                'stock_code' => $stock_code,
                'company' => $company
            ])
            ->get($this->url_link.'/so/inv_master');

            $product = $response->json()['data'];
            if(!empty($product)) {
                if($company == 'BEVA') {
                    switch($product['product_class']) {
                        case 'BHW':
                            $price_code = 'A';
                            break;
                        case 'CRS':
                            $price_code = 'X';
                            break;
                    }
                } else if($company == 'BEVI') {
                    switch($product['category']) {
                        case 'PURIFIED WATER':
                            $price_code = 'A';
                            break;
                    }
                }
            }

        } catch(\Exception $e) {

        }

        return $price_code;
    }

    public function checkSalesOrderStatus() {
        $sales_orders = SalesOrder::whereNull('upload_status')
            ->where(function($query) {
                $query->whereNull('reference')
                    ->orWhere('reference', '');
            })
            ->where('status', 'finalized')
            ->whereBetween('order_date', [
                Carbon::now()->subDays(3)->startOfDay(),
                Carbon::now()->endOfDay()
            ])
            ->get();

        foreach($sales_orders as $sales_order) {
            $this->salesOrderStatus($sales_order);
        }

    }

    public function salesOrderStatus($sales_order) {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.env('API_TOKEN_SYSPRODATA'),
            'po_number' => $sales_order->po_number,
            'company' => $sales_order->account_login->account->company->name
        ])
        ->get($this->url_link.'/so/sor_master');

        if(!empty($response->json())) {
            $so_data = $response->json()['data'];

            $so_arr = [];
            foreach($so_data as $data) {
                $so_arr[] = ltrim($data['sales_order'], 0);
            }
            $reference = implode(', ', $so_arr);

            if(!empty($reference)) {
                $sales_order->update([
                    'upload_status' => 1,
                    'reference' => $reference ?? NULL
                ]);
            }
        }
    }
}
