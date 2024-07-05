<?php

namespace App\Http\Traits;

use App\Models\Discount;
use App\Models\PriceCode;

Trait SoProductPriceTrait {
    
    public function getProductPrice($product, $account, $uom, $quantity) {
        // uom conversion
        switch($uom) {
            case 'CASE':
                $uom = 'CS';
                break;
            case 'CAS':
                $uom = 'CS';
                break;
            case 'PIECES':
                $uom = 'PCS';
                break;
        }

        $line_discount = Discount::where('discount_code', $account->line_discount_code)
            ->where('company_id', $account->company_id)
            ->first();
        
        // check price code
        if($product->special_product) {
            $special_product = $account->products()
                ->where('product_id'. $product->id)
                ->first();

            $code = $special_product->pivot->price_code ?? $account->price_code;

            $price_code = PriceCode::where('company_id', $account->company_id)
                ->where('product_id', $product->id)
                ->where('code', $code)
                ->first();
        } else {
            $price_code = PriceCode::where('company_id', $account->company_id)
                ->where('product_id', $product->id)
                ->where('code', $account->price_code)
                ->first();
        }

        $selling_price = $price_code->selling_price ?? 0;
        $price_basis = $price_code->price_basis ?? NULL;

        // convert selling price to stock uom price
        if($price_basis == 'A') {
            if($product->order_uom_operator == 'M') { // multiply
                $selling_price = $selling_price / $product->order_uom_conversion;
            }
            if($product->order_uom_operator == 'D') { // Divide
                $selling_price = $selling_price * $product->order_uom_conversion;
            }
        } else if($price_basis == 'O') {
            // check operation
            if($product->other_uom_operator == 'M') { // multiply
                $selling_price = $selling_price / $product->other_uom_conversion;
            }
            if($product->other_uom_operator == 'D') { // Divide
                $selling_price = $selling_price * $product->other_uom_conversion;
            }
        } $quantity = (float)$quantity;

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

        $uom_total = 0;
        // check if stock UOM
        if($uom == $product->stock_uom) {
            $uom_total += $quantity * $selling_price;
        } else if($uom == $product->order_uom) { // order UOM
            // check operation
            if($product->order_uom_operator == 'M') { // multiply
                $uom_total += ($quantity * $product->order_uom_conversion) * $selling_price;
            }
            if($product->order_uom_operator == 'D') { // Divide
                $uom_total += ($quantity / $product->order_uom_conversion) * $selling_price;
            }
        } else if($uom == $product->other_uom) { // Other UOM
            // check operation
            if($product->other_uom_operator == 'M') { // multiply
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

        return [
            'total' => $uom_total,
            'discounted' => $uom_total,
            'line_discount' => $line_discount
        ];
    }

    private function quantityConversion($quantity, $conversion, $operator, $reverse = false) {
        if($operator == 'M') { // mutiply
            if($reverse) {
                return $quantity / $conversion;
            } else {
                return $quantity * $conversion;
            }
        } elseif($operator == 'D') { // divide
            if($reverse) {
                return $quantity * $conversion;
            } else {
                return $quantity / $conversion;
            }
        }

        return $quantity;
    }

}