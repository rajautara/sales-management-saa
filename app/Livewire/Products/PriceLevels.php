<?php

namespace App\Livewire\Products;

use App\Livewire\Traits\AuthorizesAdmin;
use App\Models\PriceLevel;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Support\TenantRule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class PriceLevels extends Component
{
    use AuthorizesAdmin;

    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';

    public array $newProductId = [];
    public array $newPrice = [];
    public array $editPrices = [];

    public function mount(): void
    {
        ProductPrice::with('product')->get()->each(function (ProductPrice $productPrice) {
            $this->editPrices[$productPrice->id] = (string) $productPrice->price;
        });
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function savePriceLevel(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            PriceLevel::findOrFail($this->editingId)->update($validated);
        } else {
            PriceLevel::create($validated);
        }

        $this->reset(['editingId', 'name', 'description']);
        session()->flash('success', $this->editingId ? 'Price level updated successfully.' : 'Price level created successfully.');
    }

    public function editPriceLevel(PriceLevel $priceLevel): void
    {
        $this->editingId = $priceLevel->id;
        $this->name = $priceLevel->name;
        $this->description = $priceLevel->description ?? '';
    }

    public function deletePriceLevel(PriceLevel $priceLevel): void
    {
        $priceLevel->delete();
        session()->flash('success', 'Price level deleted successfully.');
    }

    public function cancelPriceLevel(): void
    {
        $this->reset(['editingId', 'name', 'description']);
    }

    public function addProductPrice(int $priceLevelId): void
    {
        $this->validate([
            "newProductId.{$priceLevelId}" => ['required', TenantRule::exists('products')],
            "newPrice.{$priceLevelId}" => ['required', 'numeric', 'min:0'],
        ]);

        $priceLevel = PriceLevel::find($priceLevelId);
        if (! $priceLevel) {
            throw ValidationException::withMessages([
                "newProductId.{$priceLevelId}" => 'The selected price level is invalid.',
            ]);
        }

        $product = Product::findOrFail($this->newProductId[$priceLevelId]);

        $productPrice = ProductPrice::updateOrCreate(
            ['price_level_id' => $priceLevel->id, 'product_id' => $product->id],
            ['price' => $this->newPrice[$priceLevelId]]
        );

        $this->editPrices[$productPrice->id] = (string) $productPrice->price;

        unset($this->newProductId[$priceLevelId], $this->newPrice[$priceLevelId]);
        session()->flash('success', 'Product price saved successfully.');
    }

    public function updateProductPrice(int $productPriceId): void
    {
        $this->validate([
            "editPrices.{$productPriceId}" => ['required', 'numeric', 'min:0'],
        ]);

        ProductPrice::findOrFail($productPriceId)->update([
            'price' => $this->editPrices[$productPriceId],
        ]);

        session()->flash('success', 'Product price updated successfully.');
    }

    public function deleteProductPrice(int $productPriceId): void
    {
        ProductPrice::findOrFail($productPriceId)->delete();
        unset($this->editPrices[$productPriceId]);
        session()->flash('success', 'Product price deleted successfully.');
    }

    public function render()
    {
        $priceLevels = PriceLevel::with(['productPrices.product'])->orderBy('name')->get();
        $products = Product::orderBy('name')->pluck('name', 'id');

        return view('livewire.products.price-levels', compact('priceLevels', 'products'));
    }
}
