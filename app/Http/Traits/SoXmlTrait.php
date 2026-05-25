<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\ShippingAddress;
use App\Models\Company;
use App\Models\Account;

use Carbon\Carbon;

use App\Jobs\CheckSalesOrderStatus;

Trait SoXmlTrait {

    /**
     * Generate an XML file for each part of the sales order and upload it to
     * the correct FTP path. The destination path is company-specific because
     * each company (BEVI, BEVA, BIGI) has its own folder in the shared FTP server.
     */
    public function generateXml($sales_order): string
    {
        $parts = $this->convertData($sales_order);

        foreach ($parts as $key => $data) {
            $part     = $key + 1;
            $xml      = $this->arrayToXml($data);
            $filename = $sales_order->po_number . '-' . $part . '.xml';
            $ftp      = Storage::disk('ftp_bevi');

            switch ($sales_order->account_login->account->company->name) {
                case 'BEVI':
                    $ftp->put('BEVI-test/Incoming/SalesOrder/' . $filename, $xml);
                    break;
                case 'BEVA':
                    $ftp->put('BEVA-test/Incoming/SalesOrder/' . $filename, $xml);
                    break;
                case 'BIGI':
                    $ftp->put('BIGI-test/Incoming/SalesOrder/' . $filename, $xml);
                    break;
            }
        }

        return $sales_order->po_number . '.xml file created successfully.';
    }

    private function arrayToXml($data): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $salesOrders = $dom->createElement('SalesOrders');
        $salesOrders->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema-instance');
        $salesOrders->setAttribute('xsd:noNamespaceSchemaLocation', 'SORTOIDOC.XSD');
        $dom->appendChild($salesOrders);

        $this->arrayToXmlHelper($data, $dom, $salesOrders);

        return $dom->saveXML();
    }

    private function arrayToXmlHelper($data, $dom, &$parent): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($key === 'StockLine') {
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

    private function convertData($sales_order): array
    {
        $company  = $sales_order->account_login->account->company->name;
        $cacheKey = 'syspro_customer_' . $sales_order->account_login->account->account_code . '_' . $company;

        try {
            $customer = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($sales_order, $company) {
                $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . config('syspro.token'),
                        'account_code'  => $sales_order->account_login->account->account_code,
                        'company'       => $company,
                    ])
                    ->timeout(60)
                    ->get(config('syspro.url') . '/so/ar_customer');

                if ($response->failed() || empty($response->json()['data'])) {
                    throw new \RuntimeException(
                        'Failed to fetch customer for account: ' . $sales_order->account_login->account->account_code .
                        ' - Status: ' . $response->status()
                    );
                }

                return $response->json()['data'];
            });
        } catch (\Exception $e) {
            throw new \RuntimeException('Connection timeout while fetching customer: ' . $e->getMessage());
        }

        $shipping_address = ShippingAddress::find($sales_order->shipping_address_id ?? 0);

        $details = $sales_order->order_products;
        $parts   = array_unique($details->pluck('part')->toArray());

        $so_parts = [];

        foreach ($parts as $part) {
            $so_details      = $details->where('part', $part);
            $trade_discounts = $this->getTradeDiscounts($company, $so_details, $sales_order->account_login->account->account_code);

            $data = [
                'Orders' => [
                    'OrderHeader' => [
                        'CustomerPoNumber'             => $sales_order->po_number . ($part > 1 ? '-' . $part : ''),
                        'Customer'                     => $sales_order->account_login->account->account_code,
                        'OrderDate'                    => $sales_order->order_date,
                        'ShippingInstrs'               => $sales_order->shipping_instruction ?? '',
                        'RequestedShipDate'            => $sales_order->ship_date ?? '',
                        'OrderComments'                => $sales_order->control_number,
                        'OrderDiscPercent1'            => $trade_discounts[0] ?? '',
                        'OrderDiscPercent2'            => $trade_discounts[1] ?? '',
                        'OrderDiscPercent3'            => $trade_discounts[2] ?? '',
                        'SalesOrderPromoQualityAction' => 'W',
                        'SalesOrderPromoSelectAction'  => 'A',
                        'MultiShipCode'                => $shipping_address->address_code ?? '',
                    ],
                ],
            ];

            $num = 0;
            foreach ($so_details as $detail) {
                foreach ($detail->product_uoms as $uom) {
                    $num++;
                    $price_code = $this->getPriceCode($company, $detail->product->stock_code);

                    $data['Orders']['OrderDetails']['StockLine'][] = [
                        'CustomerPoLine' => $num,
                        'Warehouse'      => $customer['warehouse'] ?? '',
                        'StockCode'      => $detail->product->stock_code,
                        'OrderQty'       => $uom->quantity,
                        'OrderUom'       => $uom->uom,
                        'PriceUom'       => $uom->uom,
                        'PriceCode'      => $price_code,
                    ];
                }
            }

            $so_parts[] = $data;
        }

        return $so_parts;
    }

    /**
     * Determine trade discount percentages for an order based on company and product class.
     *
     * Business rules (hardcoded by agreement with each company):
     *  - BEVA + CRS product class       → 30% discount
     *  - BEVA + stock codes KS01046/KS01047 → 12% discount
     *  - BEVA + other                   → account's own discount
     *  - BEVI + customer 1200008 + DEF/ALCOHOL product → 15% discount tier 1
     */
    private function getTradeDiscounts($company, $details, $customer): array
    {
        $trade_disc1 = '';
        $trade_disc2 = '';
        $trade_disc3 = '';

        if ($company == 'BEVA') {
            $check = Product::where('product_class', 'CRS')
                ->whereIn('id', $details->pluck('product_id')->toArray())
                ->first();

            if ($check) {
                $trade_disc1 = 30;
            } else {
                $check2 = Product::whereIn('stock_code', ['KS01046', 'KS01047'])
                    ->whereIn('id', $details->pluck('product_id')->toArray())
                    ->first();

                if ($check2) {
                    $trade_disc1 = 12;
                } else {
                    $company_model = Company::where('name', $company)->first();
                    $account = Account::where('account_code', $customer)
                        ->where('company_id', $company_model->id)
                        ->first();
                    $discount = $account ? $account->discount : null;
                    if ($discount) {
                        $trade_disc1 = $discount->discount_1 ?? 0;
                        $trade_disc2 = $discount->discount_2 ?? 0;
                        $trade_disc3 = $discount->discount_3 ?? 0;
                    }
                }
            }
        } else {
            if ($customer == '1200008') {
                $check = Product::where('product_class', 'DEF')
                    ->where('category', 'ALCOHOL')
                    ->whereIn('id', $details->pluck('product_id')->toArray())
                    ->first();

                if ($check) {
                    $trade_disc1 = 15;
                }
            }
        }

        return [$trade_disc1, $trade_disc2, $trade_disc3];
    }

    private function getPriceCode($company, $stock_code): string
    {
        $price_code = '';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('syspro.token'),
                'stock_code'    => $stock_code,
                'company'       => $company,
            ])
            ->timeout(30)
            ->get(config('syspro.url') . '/so/inv_master');

            $product = $response->json()['data'];

            if (!empty($product)) {
                if ($company == 'BEVA') {
                    switch ($product['product_class']) {
                        case 'BHW':
                            $price_code = 'A';
                            break;
                        case 'CRS':
                            $price_code = 'X';
                            break;
                    }
                } elseif ($company == 'BEVI') {
                    if ($product['category'] === 'PURIFIED WATER') {
                        $price_code = 'A';
                    }
                }
            }
        } catch (\Exception $e) {
            activity('error')->log('getPriceCode failed for stock_code=' . $stock_code . ' company=' . $company . ': ' . $e->getMessage());
        }

        return $price_code;
    }

    public function checkSalesOrderStatus(): void
    {
        $sales_orders = SalesOrder::whereNull('upload_status')
            ->where(function ($query) {
                $query->whereNull('reference')->orWhere('reference', '');
            })
            ->where('status', 'finalized')
            ->whereBetween('order_date', [
                Carbon::now()->subDays(1)->startOfDay(),
                Carbon::now()->endOfDay(),
            ])
            ->get();

        foreach ($sales_orders as $sales_order) {
            CheckSalesOrderStatus::dispatch($sales_order);
        }
    }

    public function salesOrderStatus($sales_order): void
    {
        try {
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('syspro.token'),
                    'po_number'     => $sales_order->po_number,
                    'company'       => $sales_order->account_login->account->company->name,
                ])
                ->timeout(30)
                ->get(config('syspro.url') . '/so/sor_master');

            if ($response->failed()) {
                activity('error')->log('Request failed for PO: ' . $sales_order->po_number . ' - ' . $response->status());
                return;
            }

            if (!empty($response->json())) {
                $so_data = $response->json()['data'];

                $so_arr = [];
                foreach ($so_data as $data) {
                    $so_arr[] = ltrim($data['sales_order'], 0);
                }
                $reference = implode(', ', $so_arr);

                activity('info')->log('Reference for PO Number: ' . $sales_order->po_number . ' is ' . $reference);

                if (!empty($reference)) {
                    $sales_order->update([
                        'upload_status' => 1,
                        'reference'     => $reference ?? null,
                    ]);
                }
            } else {
                activity('info')->log('No data found for PO Number: ' . $sales_order->po_number . ' ' . $response->body());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            activity('error')->log('Connection failed for PO: ' . $sales_order->po_number . ' - ' . $e->getMessage());
        } catch (\Exception $e) {
            activity('error')->log($e->getMessage());
        }
    }
}
