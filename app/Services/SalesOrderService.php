<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\PriceCode;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUom;
use App\Models\SalesOrderProductUomPAF;

use Illuminate\Support\Facades\Session;

use App\Http\Traits\GlobalTrait;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SalesOrderService {

    use GlobalTrait;

    /**
     * Calculate order totals for all products.
     *
     * Each product may have up to three UOMs (stock, order, other). The price
     * basis flag ('S' = stock UOM, 'A' = order/alternate UOM, 'O' = other UOM)
     * determines which unit the selling price is expressed in. We convert the
     * price to stock-UOM before multiplying by quantity so that totals are
     * always in the same unit regardless of which UOM the rep selected.
     */
    public function calculateOrderTotals($data, $account): array
    {
        $discount = $account->discount;

        $orders = [];
        $total = 0;
        $total_quantity = 0;

        if (!empty($data)) {
            $line_discount = Discount::where('discount_code', $account->line_discount_code)
                ->where('company_id', $account->company_id)
                ->first();

            foreach ($data as $product_id => $details) {
                $product = $details['product'];
                $orders['items'][$product_id] = [
                    'stock_code'  => $product->stock_code,
                    'description' => $product->description,
                    'size'        => $product->size,
                ];

                // Special products can have a per-account price code override.
                if ($product->special_product) {
                    $special_product = $account->products()
                        ->where('product_id', $product->id)
                        ->first();

                    $code = $special_product->pivot->price_code ?? $account->price_code;
                } else {
                    $code = $account->price_code;
                }

                $price_code = PriceCode::where('company_id', $account->company_id)
                    ->where('product_id', $product->id)
                    ->where('code', $code)
                    ->first();

                $product_total    = 0;
                $product_quantity = 0;

                if (!empty($price_code)) {
                    foreach ($details['data'] as $uom => $val) {
                        $selling_price = $price_code->selling_price;
                        $price_basis   = $price_code->price_basis;

                        // Convert the selling price from the price-basis UOM down to stock UOM.
                        // 'A' = price is per order UOM, 'O' = price is per other UOM.
                        // If the operator is Multiply (M): stock = price / conversion
                        // If the operator is Divide (D):    stock = price * conversion
                        if ($price_basis == 'A' && $product->order_uom_conversion != 0) {
                            if ($product->order_uom_operator == 'M') {
                                $selling_price = $selling_price / $product->order_uom_conversion;
                            }
                            if ($product->order_uom_operator == 'D') {
                                $selling_price = $selling_price * $product->order_uom_conversion;
                            }
                        } elseif ($price_basis == 'O' && $product->other_uom_conversion != 0) {
                            if ($product->other_uom_operator == 'M') {
                                $selling_price = $selling_price / $product->other_uom_conversion;
                            }
                            if ($product->other_uom_operator == 'D') {
                                $selling_price = $selling_price * $product->other_uom_conversion;
                            }
                        }

                        $quantity = (float) $val['quantity'];

                        // When the account forces a specific sales-order UOM, convert the
                        // entered quantity into that preferred UOM before computing totals.
                        if (!empty($account->sales_order_uom) && $uom != $account->sales_order_uom) {
                            if ($product->order_uom == $account->sales_order_uom && $uom != $product->order_uom) {
                                // Convert stock or other UOM → order UOM
                                if ($uom == $product->stock_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                } elseif ($uom == $product->other_uom) {
                                    if ($product->other_uom_operator == 'M') {
                                        $quantity = $quantity * $product->other_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                    } elseif ($product->other_uom_operator == 'D') {
                                        $quantity = $quantity / $product->other_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                    }
                                }
                                $uom = $product->order_uom;

                            } elseif ($product->other_uom == $account->sales_order_uom && $uom != $product->other_uom) {
                                // Convert stock or order UOM → other UOM
                                if ($uom == $product->stock_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                } elseif ($uom == $product->order_uom) {
                                    if ($product->order_uom_operator == 'M') {
                                        $quantity = $quantity * $product->order_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                    } elseif ($product->order_uom_operator == 'D') {
                                        $quantity = $quantity / $product->order_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                    }
                                }
                                $uom = $product->other_uom;

                            } elseif ($product->stock_uom == $account->sales_order_uom && $uom != $product->stock_uom) {
                                // Convert order or other UOM → stock UOM
                                if ($uom == $product->order_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = false);
                                } elseif ($uom == $product->other_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = false);
                                }
                                $uom = $product->stock_uom;
                            }
                        }

                        // Compute line total: convert entered UOM quantity to stock UOM, then multiply by stock price.
                        $uom_total = 0;
                        if (strtoupper($uom) == strtoupper($product->stock_uom)) {
                            $uom_total += $quantity * $selling_price;
                        } elseif ($uom == $product->order_uom) {
                            if ($product->order_uom_operator == 'M') {
                                $uom_total += ($quantity * $product->order_uom_conversion) * $selling_price;
                            }
                            if ($product->order_uom_operator == 'D') {
                                $uom_total += ($quantity / $product->order_uom_conversion) * $selling_price;
                            }
                        } elseif ($uom == $product->other_uom) {
                            if ($product->other_uom_operator == 'M') {
                                $uom_total += ($quantity * $product->other_uom_conversion) * $selling_price;
                            }
                            if ($product->other_uom_operator == 'D') {
                                $uom_total += ($quantity / $product->other_uom_conversion) * $selling_price;
                            }
                        }

                        // Apply line-level (per-UOM) discount before accumulating product total.
                        $uom_discounted = $uom_total;
                        if (!empty($line_discount)) {
                            if ($line_discount->discount_1 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_1) / 100);
                            }
                            if ($line_discount->discount_2 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_2) / 100);
                            }
                            if ($line_discount->discount_3 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_3) / 100);
                            }
                        }

                        if ($uom_total > 0) {
                            $orders['items'][$product->id]['data'][$uom] = [
                                'quantity'   => $quantity,
                                'total'      => $uom_total,
                                'discount'   => $line_discount->description ?? '0',
                                'discounted' => $uom_discounted,
                            ];
                        }

                        $product_total    += $uom_discounted;
                        $product_quantity += $quantity;
                    }
                }

                if ($product_total > 0) {
                    $orders['items'][$product->id]['product_total']    = $product_total;
                    $orders['items'][$product->id]['product_quantity'] = $product_quantity;
                } else {
                    unset($orders['items'][$product->id]);
                }

                $total          += $product_total;
                $total_quantity += $product_quantity;
            }
        }

        // Apply inventory (account-level) discount on top of line discounts.
        $discounted = $total;
        if (!empty($discount)) {
            if ($discount->discount_1 > 0) {
                $discounted = $discounted * ((100 - $discount->discount_1) / 100);
            }
            if ($discount->discount_2 > 0) {
                $discounted = $discounted * ((100 - $discount->discount_2) / 100);
            }
            if ($discount->discount_3 > 0) {
                $discounted = $discounted * ((100 - $discount->discount_3) / 100);
            }
        }

        $orders['total_quantity'] = $total_quantity;
        $orders['total']          = $total;
        $orders['discount_id']    = $discount->id ?? null;
        $orders['grand_total']    = $discounted;
        $orders['po_value']       = '';

        return $orders;
    }

    /**
     * Persist a new SalesOrder along with all its line items.
     */
    public function createOrder($data, $account, $order_data): SalesOrder
    {
        $data->control_number = $this->generateControlNumber();

        // Prepend the account's PO prefix when defined (e.g. "PH-" → "PH-00001").
        if (!empty($account->po_prefix)) {
            $data->merge(['po_number' => $account->po_prefix . $data->po_number]);
        }

        $shipping_address_id = $data->shipping_address_id == 'default' ? null : $data->shipping_address_id;

        $sales_order = SalesOrder::create([
            'account_login_id'     => Session::get('logged_account')->id,
            'shipping_address_id'  => $shipping_address_id,
            'control_number'       => $data->control_number,
            'po_number'            => $data->po_number,
            'paf_number'           => $data->paf_number,
            'order_date'           => $data->order_date,
            'ship_date'            => $data->ship_date,
            'shipping_instruction' => $data->shipping_instruction,
            'ship_to_name'         => $data->ship_to_name,
            'ship_to_building'     => $data->ship_to_address1,
            'ship_to_street'       => $data->ship_to_address2,
            'ship_to_city'         => $data->ship_to_address3,
            'ship_to_postal'       => $data->postal_code,
            'status'               => $data->status,
            'total_quantity'       => $order_data['total_quantity'],
            'total_sales'          => $order_data['total'],
            'grand_total'          => $order_data['grand_total'],
            'po_value'             => $order_data['po_value'] ?? 0,
        ]);

        $this->processOrderItems($sales_order, $order_data['items'], $account);

        return $sales_order;
    }

    /**
     * Replace all line items on an existing SalesOrder and update its header fields.
     */
    public function updateOrder($sales_order, $data, $account, $order_data): SalesOrder
    {
        if (!empty($account->po_prefix)) {
            $data->merge(['po_number' => $account->po_prefix . $data->po_number]);
        }

        $shipping_address_id = $data->shipping_address_id == 'default' ? null : $data->shipping_address_id;

        $sales_order->update([
            'shipping_address_id'  => $shipping_address_id,
            'po_number'            => $data->po_number,
            'paf_number'           => $data->paf_number,
            'ship_date'            => $data->ship_date,
            'shipping_instruction' => $data->shipping_instruction,
            'ship_to_name'         => $data->ship_to_name,
            'ship_to_building'     => $data->ship_to_address1,
            'ship_to_street'       => $data->ship_to_address2,
            'ship_to_city'         => $data->ship_to_address3,
            'ship_to_postal'       => $data->postal_code,
            'status'               => $data->status,
            'total_quantity'       => $order_data['total_quantity'],
            'total_sales'          => $order_data['total'],
            'grand_total'          => $order_data['grand_total'],
            'po_value'             => $order_data['po_value'] ?? 0,
        ]);

        // Hard-delete existing line items so they can be recreated cleanly.
        foreach ($sales_order->order_products as $order_product) {
            foreach ($order_product->product_uoms as $uom) {
                SalesOrderProductUomPAF::where('sales_order_product_uom_id', $uom->id)->forceDelete();
            }
            $order_product->product_uoms()->forceDelete();
        }
        $sales_order->order_products()->forceDelete();

        $this->processOrderItems($sales_order, $order_data['items'], $account);

        return $sales_order;
    }

    /**
     * Create SalesOrderProduct rows for all items.
     *
     * Products belonging to a "special group" (configured in config/sales-order.php
     * under `separate_products.groups`) are grouped into their own part number so
     * they generate a separate XML file when uploaded to the ERP.
     */
    public function processOrderItems(SalesOrder $sales_order, array $items, $account): void
    {
        $limit        = $account->company->order_limit ?? $this->getSettings()->sales_order_limit;
        $custom_limits = Config::get('sales-order.custom_limits', []);
        $limit        = $custom_limits[$account->account_code] ?? $limit;

        $curr_limit = $limit;

        $special_groups  = $this->getSpecialProductGroup($account);
        $all_special_ids = [];
        foreach ($special_groups as $group_ids) {
            $all_special_ids = array_merge($all_special_ids, $group_ids);
        }

        $num  = 0;
        $part = 1;
        foreach ($items as $product_id => $details) {
            if (in_array($product_id, $all_special_ids)) {
                continue;
            }

            if (Config::get('enable_parts')) {
                $num++;
                if ($num > $curr_limit) {
                    $curr_limit += $limit;
                    $part++;
                }
            }

            $this->createOrderProduct($sales_order, $product_id, $details, $part);
        }

        // Special-group products start on a new part number after all regular products.
        foreach ($special_groups as $group_name => $product_ids) {
            if (empty($product_ids)) {
                continue;
            }

            $items_in_group = array_filter($items, fn($key) => in_array($key, $product_ids), ARRAY_FILTER_USE_KEY);

            if (!empty($items_in_group)) {
                $part++;
                foreach ($items_in_group as $pid => $details) {
                    $this->createOrderProduct($sales_order, $pid, $details, $part);
                }
            }
        }
    }

    /**
     * Persist a single SalesOrderProduct with its UOM rows and optional PAF rows.
     */
    private function createOrderProduct(SalesOrder $sales_order, int $product_id, array $details, int $part): void
    {
        $order_product = SalesOrderProduct::create([
            'sales_order_id'  => $sales_order->id,
            'product_id'      => $product_id,
            'part'            => $part,
            'total_quantity'  => $details['product_quantity'],
            'total_sales'     => $details['product_total'],
        ]);

        foreach ($details['data'] as $uom => $data) {
            $product_uom = SalesOrderProductUom::create([
                'sales_order_product_id' => $order_product->id,
                'uom'                    => $uom,
                'quantity'               => $data['quantity'],
                'uom_total'              => $data['total'],
                'uom_total_less_disc'    => $data['discounted'],
            ]);

            if (!empty($data['paf_rows'])) {
                foreach ($data['paf_rows'] as $paf_row) {
                    if (isset($paf_row['paf_number']) && !empty($paf_row['uom']) && !empty($paf_row['quantity'])) {
                        SalesOrderProductUomPAF::create([
                            'sales_order_product_uom_id' => $product_uom->id,
                            'paf_number'                 => $paf_row['paf_number'],
                            'uom'                        => $paf_row['uom'],
                            'quantity'                   => $paf_row['quantity'],
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Resolve which product IDs belong to each special group for this account.
     * Group definitions live in config/sales-order.php under `separate_products.groups`.
     */
    private function getSpecialProductGroup($account): array
    {
        $groups = Config::get('sales-order.separate_products.groups', []);
        $result = [];

        foreach ($groups as $group_name => $config) {
            if ((!empty($config['accounts']) && in_array($account->account_code, $config['accounts'])) || empty($config['accounts'])) {
                $product_ids = Product::whereIn('stock_code', $config['stock_codes'])
                    ->pluck('id')
                    ->toArray();

                if (!empty($product_ids)) {
                    $result[$group_name] = $product_ids;
                }
            }
        }

        return $result;
    }

    /**
     * Convert a quantity between UOMs using the product's operator and conversion factor.
     *
     * Operator M (Multiply): 1 order unit = conversion × stock units
     *   forward:  stock  = quantity * conversion
     *   reverse:  order  = quantity / conversion
     * Operator D (Divide): 1 stock unit = conversion × order units
     *   forward:  order  = quantity / conversion
     *   reverse:  stock  = quantity * conversion
     */
    private function quantityConversion(float $quantity, float $conversion, string $operator, bool $reverse = false): float
    {
        if ($conversion == 0) {
            return $quantity;
        }

        if ($operator == 'M') {
            return $reverse ? $quantity / $conversion : $quantity * $conversion;
        }

        if ($operator == 'D') {
            return $reverse ? $quantity * $conversion : $quantity / $conversion;
        }

        return $quantity;
    }

    /**
     * Generate a unique control number in the format SO-YYYYMMDD-NNN.
     *
     * A do-while loop is used instead of a simple increment because two concurrent
     * requests could read the same "latest" number before either has saved. The loop
     * retries until the generated number is not already taken, making it safe under
     * typical concurrent load (race window is small; no distributed lock needed here).
     */
    public function generateControlNumber(): string
    {
        $date_code = date('Ymd');

        return DB::transaction(function () use ($date_code) {
            $latest = SalesOrder::withTrashed()
                ->where('control_number', 'like', "SO-{$date_code}-%")
                ->lockForUpdate()
                ->orderByDesc('control_number')
                ->value('control_number');

            $next = $latest ? ((int) substr($latest, strrpos($latest, '-') + 1)) + 1 : 1;

            return \sprintf('SO-%s-%03d', $date_code, $next);
        });
    }
}
