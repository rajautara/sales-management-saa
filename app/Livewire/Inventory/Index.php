<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $lowStockOnly = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLowStockOnly(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->lowStockOnly, function ($query) {
                $query->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')
                    ->where('low_stock_threshold', '>', 0);
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.inventory.index', compact('products'));
    }
}
