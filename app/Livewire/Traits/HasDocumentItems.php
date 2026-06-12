<?php

namespace App\Livewire\Traits;

use App\Models\Customer;
use App\Models\Product;
use App\Services\PricingService;
use App\Support\TenantRule;
use Livewire\Attributes\Computed;

trait HasDocumentItems
{
    public array $items = [];

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'description' => '',
            'qty' => 1,
            'unit_price' => 0,
            'discount' => 0,
            'tax_rate' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems($value, $key): void
    {
        if (! str_contains((string) $key, '.product_id')) {
            return;
        }

        $parts = explode('.', $key);
        $index = is_numeric($parts[0] ?? null) ? $parts[0] : ($parts[1] ?? null);

        if ($index === null || ! isset($this->items[$index])) {
            return;
        }

        $productId = $this->items[$index]['product_id'];

        if (! $productId) {
            return;
        }

        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        $customer = $this->getCustomerModel();
        $this->items[$index]['unit_price'] = PricingService::resolvePrice($product, $customer);
        $this->items[$index]['tax_rate'] = (float) $product->tax_rate;

        if (empty($this->items[$index]['description'])) {
            $this->items[$index]['description'] = $product->name;
        }
    }

    protected function getCustomerModel(): ?Customer
    {
        return ! empty($this->customerId) ? Customer::find($this->customerId) : null;
    }

    public function updatedCustomerId($value): void
    {
        $customer = $value ? Customer::find($value) : null;

        foreach ($this->items as $index => $item) {
            $productId = $item['product_id'] ?? null;
            if ($productId) {
                $product = Product::find($productId);
                if ($product) {
                    $this->items[$index]['unit_price'] = PricingService::resolvePrice($product, $customer);
                }
            }
        }
    }

    #[Computed]
    public function totals(): array
    {
        $subtotal = 0;
        $tax = 0;
        $discount = 0;
        $total = 0;

        foreach ($this->items as $item) {
            $line = PricingService::calculateLineTotal(
                (float) ($item['qty'] ?? 0),
                (float) ($item['unit_price'] ?? 0),
                (float) ($item['discount'] ?? 0),
                (float) ($item['tax_rate'] ?? 0)
            );

            $subtotal += $line['subtotal'];
            $tax += $line['tax'];
            $discount += $line['discount'];
            $total += $line['total'];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
        ];
    }

    public function rowTotal(int $index): float
    {
        $item = $this->items[$index] ?? [];

        return PricingService::calculateLineTotal(
            (float) ($item['qty'] ?? 0),
            (float) ($item['unit_price'] ?? 0),
            (float) ($item['discount'] ?? 0),
            (float) ($item['tax_rate'] ?? 0)
        )['total'];
    }

    protected function documentItemRules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', TenantRule::exists('products')],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function validatedDocumentItems(): array
    {
        return collect($this->items)
            ->map(function (array $item) {
                $productId = $item['product_id'] ?? null;

                if ($productId) {
                    $item['product_id'] = Product::findOrFail($productId)->id;
                } else {
                    $item['product_id'] = null;
                }

                return $item;
            })
            ->all();
    }

    protected function fillItemsFromModel(iterable $modelItems): void
    {
        $this->items = [];

        foreach ($modelItems as $modelItem) {
            $this->items[] = [
                'product_id' => $modelItem->product_id,
                'description' => $modelItem->description,
                'qty' => (float) $modelItem->qty,
                'unit_price' => (float) $modelItem->unit_price,
                'discount' => (float) $modelItem->discount,
                'tax_rate' => (float) $modelItem->tax_rate,
            ];
        }
    }
}
