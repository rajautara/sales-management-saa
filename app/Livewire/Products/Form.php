<?php

namespace App\Livewire\Products;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use App\Support\TenantRule;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?int $productId = null;

    public string $sku = '';
    public string $name = '';
    public string $type = 'product';
    public ?int $categoryId = null;
    public string $unit = '';
    public string $costPrice = '0';
    public string $sellPrice = '0';
    public string $taxRate = '0';
    public bool $trackStock = true;
    public string $lowStockThreshold = '0';
    public bool $isActive = true;

    public function mount(Product $product = null): void
    {
        if ($product) {
            $this->productId = $product->id;
            $this->sku = $product->sku ?? '';
            $this->name = $product->name;
            $this->type = $product->type?->value ?? 'product';
            $this->categoryId = $product->category_id;
            $this->unit = $product->unit ?? '';
            $this->costPrice = (string) $product->cost_price;
            $this->sellPrice = (string) $product->sell_price;
            $this->taxRate = (string) $product->tax_rate;
            $this->trackStock = $product->track_stock;
            $this->lowStockThreshold = (string) $product->low_stock_threshold;
            $this->isActive = $product->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'sku' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:product,service'],
            'categoryId' => ['nullable', TenantRule::exists('categories')],
            'unit' => ['nullable', 'string', 'max:50'],
            'costPrice' => ['nullable', 'numeric', 'min:0'],
            'sellPrice' => ['nullable', 'numeric', 'min:0'],
            'taxRate' => ['nullable', 'numeric', 'min:0'],
            'trackStock' => ['boolean'],
            'lowStockThreshold' => ['nullable', 'numeric', 'min:0'],
            'isActive' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        $categoryId = $validated['categoryId'] ? Category::findOrFail($validated['categoryId'])->id : null;

        $data = [
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'category_id' => $categoryId,
            'unit' => $validated['unit'],
            'cost_price' => $validated['costPrice'] ?? 0,
            'sell_price' => $validated['sellPrice'] ?? 0,
            'tax_rate' => $validated['taxRate'] ?? 0,
            'track_stock' => $validated['trackStock'],
            'low_stock_threshold' => $validated['lowStockThreshold'] ?? 0,
            'is_active' => $validated['isActive'],
        ];

        if ($this->productId) {
            Product::findOrFail($this->productId)->update($data);
        } else {
            Product::create($data);
        }

        session()->flash('success', $this->productId ? 'Product updated successfully.' : 'Product created successfully.');
        $this->redirectRoute('products.index');
    }

    public function render()
    {
        return view('livewire.products.form', [
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
            'types' => ProductType::cases(),
        ]);
    }
}
