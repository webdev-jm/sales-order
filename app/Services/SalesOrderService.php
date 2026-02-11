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

class SalesOrderService {

    use GlobalTrait;

    // get total of products
    public function calculateOrderTotals($data, $account) {

        $discount = $account->discount;

        // process data
        $orders = [];
        $total = 0;
        $total_quantity = 0;
        if(!empty($data)) {
            $line_discount = Discount::where('discount_code', $account->line_discount_code)
                ->where('company_id', $account->company_id)
                ->first();

            foreach($data as $product_id => $details) {
                $product = $details['product'];
                $orders['items'][$product_id] = [
                    'stock_code' => $product->stock_code,
                    'description' => $product->description,
                    'size' => $product->size,
                ];

                // check price code
                if($product->special_product) {
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

                $product_total = 0;
                $product_quantity = 0;
                if(!empty($price_code)) {
                    foreach($details['data'] as $uom => $val) {
                        // get price
                        $selling_price = $price_code->selling_price;
                        $price_basis = $price_code->price_basis;

                        // convert selling price to stock uom price
                        if($price_basis == 'A') {
                            if($product->order_uom_operator == 'M') { // Multiply
                                $selling_price = $selling_price / $product->order_uom_conversion;
                            }
                            if($product->order_uom_operator == 'D') { // Divide
                                $selling_price = $selling_price * $product->order_uom_conversion;
                            }
                        } else if($price_basis == 'O') {
                            // check operation
                            if($product->other_uom_operator == 'M') { // Multiply
                                $selling_price = $selling_price / $product->other_uom_conversion;
                            }
                            if($product->other_uom_operator == 'D') { // Divide
                                $selling_price = $selling_price * $product->other_uom_conversion;
                            }
                        }

                        $quantity = (float)$val['quantity'];

                        // check account sales order UOM
                        if(!empty($account->sales_order_uom) && $uom != $account->sales_order_uom) {
                            if($product->order_uom == $account->sales_order_uom && $uom != $product->order_uom) {
                                if($uom == $product->stock_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                } elseif($uom == $product->other_uom) {
                                    // check operation
                                    if($product->other_uom_operator == 'M') { // Multiply
                                        // convert to stock uom first
                                        $quantity = $quantity * $product->other_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                    } elseif($product->other_uom_operator == 'D') { // Divide
                                        // convert to stock uom first
                                        $quantity = $quantity / $product->other_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = true);
                                    }
                                }
                                $uom = $product->order_uom;
                            } else if($product->other_uom == $account->sales_order_uom && $uom != $product->other_uom) {
                                if($uom == $product->stock_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                } else if($uom == $product->order_uom) {
                                    if($product->order_uom_operator == 'M') {
                                        // convert to stock uom
                                        $quantity = $quantity * $product->order_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                    } elseif($product->order_uom_operator == 'D') {
                                        $quantity = $quantity / $product->order_uom_conversion;
                                        $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = true);
                                    }
                                }
                                $uom = $product->other_uom;
                            } else if($product->stock_uom == $account->sales_order_uom && $uom != $product->stock_uom) {
                                if($uom == $product->order_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->order_uom_conversion, $product->order_uom_operator, $reverse = false);
                                } else if($uom == $product->other_uom) {
                                    $quantity = $this->quantityConversion($quantity, $product->other_uom_conversion, $product->other_uom_operator, $reverse = false);
                                }

                                $uom = $product->stock_uom;
                            }
                        }

                        // get total
                        $uom_total = 0;
                        if(strtoupper($uom) == strtoupper($product->stock_uom)) {
                            $uom_total += $quantity * $selling_price;
                        } else if($uom == $product->order_uom) { // order UOM
                            // check operator
                            if($product->order_uom_operator == 'M') { // Multiply
                                $uom_total += ($quantity * $product->order_uom_conversion) * $selling_price;
                            }
                            if($product->order_uom_operator == '') { // Divide
                                $uom_total += ($quantity / $product->order_uom_conversion) * $selling_price;
                            }
                        } else if($uom == $product->other_uom) { // other UOM
                            // check operator
                            if($product->other_uom_operator == 'M') { // Multiply
                                $uom_total += ($quantity * $product->other_uom_conversion) * $selling_price;
                            }
                            if($product->other_uom_operator == 'D') { // Divide
                                $uom_total += ($quantity / $product->other_uom_conversion) * $selling_price;
                            }
                        }

                        // apply line discount
                        $uom_discounted = $uom_total;
                        if(!empty($line_discount)) {
                            $discounted = $total;
                            if($line_discount->discount_1 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_1) / 100);
                            }
                            if($line_discount->discount_2 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_2) / 100);
                            }
                            if($line_discount->discount_3 > 0) {
                                $uom_discounted = $uom_discounted * ((100 - $line_discount->discount_3) / 100);
                            }
                        }

                        if($uom_total > 0) {
                            $orders['items'][$product->id]['data'][$uom] = [
                                'quantity' => $quantity,
                                'total' => $uom_total,
                                'discount' => $line_discount->description ?? '0',
                                'discounted' => $uom_discounted,
                            ];
                        }

                        $product_total += $uom_discounted;
                        $product_quantity += $quantity;
                    }
                }

                if($product_total > 0) {
                    $orders['items'][$product->id]['product_total'] = $product_total;
                    $orders['items'][$product->id]['product_quantity'] = $product_quantity;
                } else {
                    unset($orders['items'][$product->id]);
                }

                $total += $product_total;
                $total_quantity += $product_quantity;
            }
        }

        // apply inventory discount
        $discounted = $total;
        if(!empty($discount)) {
            if($discount->discount_1 > 0) {
                $discounted = $discounted * ((100 - $discount->discount_1) / 100);
            }
            if($discount->discount_2 > 0) {
                $discounted = $discounted * ((100 - $discount->discount_2) / 100);
            }
            if($discount->discount_3 > 0) {
                $discounted = $discounted * ((100 - $discount->discount_3) / 100);
            }
        }

        $orders['total_quantity'] = $total_quantity;
        $orders['total'] = $total;
        $orders['discount_id'] = $discount->id ?? NULL;
        $orders['grand_total'] = $discounted;
        $orders['po_value'] = '';

        return $orders;

    }

    // create sales order
    public function createOrder($data, $account, $order_data) {
        $data['control_number'] = $this->generateControlNumber();

        // for accounts with PO prefix
        if(!empty($account->po_prefix)) {
            $data['po_number'] = $account->po_prefix.$data['po_number'];
        }

        $shipping_address_id = $data->shipping_address_id == 'default' ? NULL : $data->shipping_address_id;

        $sales_order = new SalesOrder([
            'account_login_id' => Session::get('logged_account')->id,
            'shipping_address_id' => $shipping_address_id,
            'control_number' => $data->control_number,
            'po_number' => $data->po_number,
            'paf_number' => $data->paf_number,
            'order_date' => $data->order_date,
            'ship_date' => $data->ship_date,
            'shipping_instruction' => $data->shipping_instruction,
            'ship_to_name' => $data->ship_to_name,
            'ship_to_building' => $data->ship_to_address1,
            'ship_to_street' => $data->ship_to_address2,
            'ship_to_city' => $data->ship_to_address3,
            'ship_to_postal' => $data->postal_code,
            'status' => $data->status,
            'total_quantity' => $order_data['total_quantity'],
            'total_sales' => $order_data['total'],
            'grand_total' => $order_data['grand_total'],
            'po_value' => $order_data['po_value'] ?? 0
        ]);
        $sales_order->save();

        $this->processOrderItems($sales_order, $order_data['items'], $account);

        return $sales_order;
    }

    public function updateOrder($sales_order, $data, $account, $order_data) {
        // for accounts with PO prefix
        if(!empty($account->po_prefix)) {
            $data['po_number'] = $account->po_prefix.$data['po_number'];
        }

        $shipping_address_id = $data->shipping_address_id == 'default' ? NULL : $data->shipping_address_id;

        $sales_order->update([
            'shipping_address_id' => $shipping_address_id,
            'po_number' => $data['po_number'],
            'paf_number' => $data->paf_number,
            'ship_date' => $data->ship_date,
            'shipping_instruction' => $data->shipping_instruction,
            'ship_to_name' => $data->ship_to_name,
            'ship_to_building' => $data->ship_to_address1,
            'ship_to_street' => $data->ship_to_address2,
            'ship_to_city' => $data->ship_to_address3,
            'ship_to_postal' => $data->postal_code,
            'status' => $data->status,
            'total_quantity' => $order_data['total_quantity'],
            'total_sales' => $order_data['total'],
            'grand_total' => $order_data['grand_total'],
            'po_value' => $order_data['po_value'] ?? 0,
        ]);

        // clear existing items
        foreach($sales_order->order_products as $order_product) {
            foreach($order_product->product_uoms as $uom) {
                SalesOrderProductUomPAF::where('sales_order_product_uom_id', $uom->id)->forceDelete();
            }
            $order_product->product_uoms()->forceDelete();
        }
        $sales_order->order_products()->forceDelete();

        $this->processOrderItems($sales_order, $order_data['items'], $account);

        return $sales_order;
    }

    // process sales order items
    public function processOrderItems($sales_order, $items, $account) {

        $limit = $account->company->order_limit ?? $this->getSettings()->sales_order_limit;
        $custom_limits = Config::get('sales-order.custom_limits', []);
        $limit = $custom_limits[$account->account_code] ?? $limit;

        $curr_limit = $limit;

        $special_groups = $this->getSpecialProductGroup($account);
        $all_special_ids = [];
        foreach ($special_groups as $group_ids) {
            $all_special_ids = array_merge($all_special_ids, $group_ids);
        }

        $num = 0;
        $part = 1;
        foreach($items as $product_id => $details) {
            // skip if belongs to group
            if(in_array($product_id, $all_special_ids)) {
                continue;
            }

            if(Config::get('enable_parts')) {
                $num++;
                if($num > $curr_limit) {
                    $curr_limit += $limit;
                    $part++;
                }
            }

            $this->createOrderProduct($sales_order, $product_id, $details, $part);
        }

        // special product group
        foreach($special_groups as $group_name => $product_ids) {
            if(empty($product_ids)) continue;

            $items_in_group = array_filter($items, fn($key) => in_array($key, $product_ids), ARRAY_FILTER_USE_KEY);

            if(!empty($items_in_group)) {
                $part++;
                foreach($items_in_group as $pid => $details) {
                    $this->createOrderProduct($sales_order, $pid, $details, $part);
                }
            }
        }
    }

    // create product function
    private function createOrderProduct($sales_order, $product_id, $details, $part) {
        $order_product = new SalesOrderProduct([
            'sales_order_id' => $sales_order->id,
            'product_id' => $product_id,
            'part' => $part,
            'total_quantity' => $details['product_quantity'],
            'total_sales'   => $details['product_total']
        ]);
        $order_product->save();

        foreach($details['data'] as $uom => $data) {
            $product_uom = new SalesOrderProductUom([
                'sales_order_product_id' => $order_product->id,
                'uom' => $uom,
                'quantity' => $data['quantity'],
                'uom_total' => $data['total'],
                'uom_total_less_disc' => $data['discounted'],
                // 'warehouse' => $data['warehouse'],
            ]);
            $product_uom->save();

            // check if there's a PAF row
            if(!empty($data['paf_rows'])) {
                foreach($data['paf_rows'] as $paf_row) {
                    if(isset($paf_row['paf_number']) && !empty($paf_row['uom']) && !empty($paf_row['quantity'])) {
                        $product_uom_paf = new SalesOrderProductUomPAF([
                            'sales_order_product_uom_id' => $product_uom->id,
                            'paf_number' => $paf_row['paf_number'],
                            'uom' => $paf_row['uom'],
                            'quantity' => $paf_row['quantity'],
                        ]);
                        $product_uom_paf->save();
                    }
                }
            }
        }
    }

    // function for separation of special products
    private function getSpecialProductGroup($account) {
        $groups = Config::get('sales-order.separate_products.groups', []);
        $result = [];

        foreach($groups as $group_name => $config) {
            if((!empty($config['accounts']) && in_array($account->account_code, $config['accounts'])) || empty($config['accounts'])) {
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

    // quantity conversion base of product configuration
    private function quantityConversion($quantity, $conversion, $operator, $reverse = false)
    {
        // Avoid division by zero
        if ($conversion == 0) {
            return (float) $quantity;
        }

        if ($operator == 'M') { // multiply
            if ($reverse) {
                return $quantity / $conversion;
            } else {
                return $quantity * $conversion;
            }
        } elseif ($operator == 'D') { // divide
            if ($reverse) {
                return $quantity * $conversion;
            } else {
                return $quantity / $conversion;
            }
        }

        return (float) $quantity;
    }

    // Generate control number
    public function generateControlNumber() {
        $date_code = date('Ymd');

        do {
            $control_number = 'SO-'.$date_code.'-001';
            // get the most recent sales order
            $sales_order = SalesOrder::withTrashed()->orderBy('control_number', 'DESC')
                ->first();
            if(!empty($sales_order)) {
                $latest_control_number = $sales_order->control_number;
                list(, $prev_date, $last_number) = explode('-', $latest_control_number);

                // Increment the number based on the date
                $number = ($date_code == $prev_date) ? ((int)$last_number + 1) : 1;

                // Format the number with leading zeros
                $formatted_number = str_pad($number, 3, '0', STR_PAD_LEFT);

                // Construct the new control number
                $control_number = "SO-$date_code-$formatted_number";
            }

        } while(SalesOrder::withTrashed()->where('control_number', $control_number)->exists());

        return $control_number;
    }

    // Not in used yet
    private function uomQuantityAllocation($quantity, $product_id, $uom)
    {
        // If the UOM is already 'CS', just return the quantity as a float.
        if ($uom === 'CS') {
            return (float) $quantity;
        }

        // Assuming you are using Laravel's Eloquent to find the product.
        // Replace with your actual data-fetching method.
        $product = Product::findOrFail($product_id);

        // --- Logic to find the 'CS' unit and its conversion details ---

        $cs_uom_column = null;
        $conversion = 1;
        $operator = 'M';

        // 1. Identify which UOM field holds 'CS'.
        if ($product->stock_uom == 'CS') {
            $cs_uom_column = 'stock_uom';
        } elseif ($product->order_uom == 'CS') {
            $cs_uom_column = 'order_uom';
        } elseif ($product->other_uom == 'CS') {
            $cs_uom_column = 'other_uom';
        }

        // 2. If no 'CS' unit is defined, return the original quantity.
        if (is_null($cs_uom_column)) {
            return (float) $quantity;
        }

        // 3. Get the conversion details based on which field was 'CS'.
        if ($cs_uom_column === 'stock_uom') {
            // Rule: If 'stock_uom' is the case unit, its conversion is 1.
            // This is now handled directly in the code without needing database columns.
            $conversion = 1;
            $operator = 'M';
        } elseif ($cs_uom_column === 'order_uom') {
            $conversion = $product->order_uom_conversion;
            $operator = $product->order_uom_operator;
        } elseif ($cs_uom_column === 'other_uom') {
            $conversion = $product->other_uom_conversion;
            $operator = $product->other_uom_operator;
        }

        // --- Perform the conversion and return the decimal value ---

        // Call the helper function with reverse=true to convert to the 'CS' unit.
        $cs_quantity = $this->quantityConversion(
            $quantity,
            $conversion,
            $operator,
            true // Reverse is true to convert from pieces to cases
        );

        return (float) $cs_quantity;
    }

}
