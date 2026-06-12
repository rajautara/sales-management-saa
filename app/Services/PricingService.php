<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;

class PricingService
{
    public static function resolvePrice(Product $product, ?Customer $customer = null): float
    {
        if ($customer && $customer->price_level_id) {
            $productPrice = $product->productPrices()
                ->where('price_level_id', $customer->price_level_id)
                ->first();

            if ($productPrice) {
                return (float) $productPrice->price;
            }
        }

        return (float) $product->sell_price;
    }

    public static function calculateLineTotal(float $qty, float $unitPrice, float $discount = 0, float $taxRate = 0): array
    {
        $subtotal = $qty * $unitPrice;
        $tax = $subtotal * ($taxRate / 100);
        $total = $subtotal + $tax - $discount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'discount' => round($discount, 2),
            'total' => round(max($total, 0), 2),
        ];
    }
}
