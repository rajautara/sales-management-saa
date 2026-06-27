<?php

namespace App\Livewire\Products;

use App\Livewire\Traits\AuthorizesAdmin;
use App\Models\Discount;
use App\Models\Product;
use App\Support\TenantRule;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Discounts extends Component
{
    use AuthorizesAdmin;

    public ?int $editingId = null;

    public string $name = '';
    public string $type = 'percent';
    public string $value = '0';
    public string $appliesTo = 'order';
    public ?int $productId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public bool $isActive = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0'],
            'appliesTo' => ['required', 'in:order,product'],
            'productId' => ['nullable', 'required_if:appliesTo,product', TenantRule::exists('products')],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'isActive' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        $productId = $validated['appliesTo'] === 'product'
            ? Product::findOrFail($validated['productId'])->id
            : null;

        $data = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'applies_to' => $validated['appliesTo'],
            'product_id' => $productId,
            'start_date' => $validated['startDate'],
            'end_date' => $validated['endDate'],
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId) {
            Discount::findOrFail($this->editingId)->update($data);
        } else {
            Discount::create($data);
        }

        $this->reset(['editingId', 'name', 'type', 'value', 'appliesTo', 'productId', 'startDate', 'endDate']);
        $this->isActive = true;
        $this->type = 'percent';
        $this->appliesTo = 'order';

        session()->flash('success', $this->editingId ? 'Discount updated successfully.' : 'Discount created successfully.');
    }

    public function edit(Discount $discount): void
    {
        $this->editingId = $discount->id;
        $this->name = $discount->name;
        $this->type = $discount->type->value;
        $this->value = (string) $discount->value;
        $this->appliesTo = $discount->applies_to;
        $this->productId = $discount->product_id;
        $this->startDate = $discount->start_date?->format('Y-m-d');
        $this->endDate = $discount->end_date?->format('Y-m-d');
        $this->isActive = $discount->is_active;
    }

    public function delete(Discount $discount): void
    {
        $discount->delete();
        session()->flash('success', 'Discount deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'type', 'value', 'appliesTo', 'productId', 'startDate', 'endDate']);
        $this->isActive = true;
        $this->type = 'percent';
        $this->appliesTo = 'order';
    }

    public function render()
    {
        return view('livewire.products.discounts', [
            'discounts' => Discount::with('product')->orderBy('name')->get(),
            'products' => Product::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
