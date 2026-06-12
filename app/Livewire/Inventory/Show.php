<?php

namespace App\Livewire\Inventory;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Show extends Component
{
    public Product $product;

    public string $adjustmentType = 'adjustment';
    public string $adjustmentQty = '';
    public string $adjustmentNotes = '';

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    protected function rules(): array
    {
        return [
            'adjustmentType' => ['required', Rule::in(['in', 'out', 'adjustment'])],
            'adjustmentQty' => ['required', 'numeric', 'min:0.01'],
            'adjustmentNotes' => ['nullable', 'string'],
        ];
    }

    public function adjustStock(): void
    {
        $validated = $this->validate();
        $qty = (float) $validated['adjustmentQty'];

        if ($validated['adjustmentType'] === 'out') {
            if ($this->product->quantity_on_hand < $qty) {
                $this->addError('adjustmentQty', 'Not enough stock available.');

                return;
            }
            StockService::recordOut($this->product, $qty, $this->product, $validated['adjustmentNotes']);
        } elseif ($validated['adjustmentType'] === 'in') {
            StockService::recordIn($this->product, $qty, $this->product, $validated['adjustmentNotes']);
        } else {
            StockService::adjust($this->product, $qty, $validated['adjustmentNotes']);
        }

        $this->reset(['adjustmentQty', 'adjustmentNotes']);
        $this->product->refresh();
        session()->flash('success', 'Stock adjusted successfully.');
    }

    public function render()
    {
        $movements = $this->product->stockMovements()
            ->with('reference')
            ->latest()
            ->paginate(15);

        return view('livewire.inventory.show', [
            'movements' => $movements,
            'types' => [
                'in' => 'Stock In',
                'out' => 'Stock Out',
                'adjustment' => 'Adjustment',
            ],
        ]);
    }
}
