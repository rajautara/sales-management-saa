<?php

namespace App\Models;

use App\Enums\ProductType;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'category_id',
        'sku',
        'name',
        'type',
        'unit',
        'cost_price',
        'sell_price',
        'tax_rate',
        'classification_code',
        'uom_code',
        'tax_type',
        'track_stock',
        'quantity_on_hand',
        'low_stock_threshold',
        'is_active',
    ];

    protected $casts = [
        'type' => ProductType::class,
        'cost_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'track_stock' => 'boolean',
        'quantity_on_hand' => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
