<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;

class StockService
{
    public static function recordIn(Product $product, float $qty, Model $reference, ?string $notes = null): StockMovement
    {
        return static::record($product, StockMovementType::IN, $qty, $reference, $notes);
    }

    public static function recordOut(Product $product, float $qty, Model $reference, ?string $notes = null): StockMovement
    {
        return static::record($product, StockMovementType::OUT, -abs($qty), $reference, $notes);
    }

    public static function adjust(Product $product, float $qty, ?string $notes = null): StockMovement
    {
        return static::record($product, StockMovementType::ADJUSTMENT, $qty, $product, $notes);
    }

    protected static function record(Product $product, StockMovementType $type, float $qty, Model $reference, ?string $notes): StockMovement
    {
        $movement = $product->stockMovements()->create([
            'company_id' => $product->company_id,
            'type' => $type,
            'qty' => $qty,
            'balance' => static::balanceFor($product) + $qty,
            'reference_type' => $reference->getMorphClass(),
            'reference_id' => $reference->getKey(),
            'notes' => $notes,
        ]);

        $product->quantity_on_hand = $movement->balance;
        $product->save();

        return $movement;
    }

    public static function balanceFor(Product $product): float
    {
        return (float) $product->stockMovements()->sum('qty');
    }
}
