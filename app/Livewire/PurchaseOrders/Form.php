<?php

namespace App\Livewire\PurchaseOrders;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\DocumentNumberService;
use App\Services\PricingService;
use App\Support\TenantRule;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?int $purchaseOrderId = null;

    public ?int $supplierId = null;
    public string $date = '';
    public string $notes = '';

    public array $items = [];

    public function mount(PurchaseOrder $purchaseOrder = null): void
    {
        if ($purchaseOrder && $purchaseOrder->exists) {
            $this->purchaseOrderId = $purchaseOrder->id;
            $this->supplierId = $purchaseOrder->supplier_id;
            $this->date = $purchaseOrder->date->format('Y-m-d');
            $this->notes = $purchaseOrder->notes ?? '';

            foreach ($purchaseOrder->items as $item) {
                $this->items[] = [
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'qty' => (float) $item->qty,
                    'unit_price' => (float) $item->unit_price,
                    'tax_rate' => (float) $item->tax_rate,
                ];
            }
        } else {
            $this->date = now()->format('Y-m-d');
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'description' => '',
            'qty' => 1,
            'unit_price' => 0,
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

        $this->items[$index]['unit_price'] = (float) $product->cost_price;
        $this->items[$index]['tax_rate'] = (float) $product->tax_rate;

        if (empty($this->items[$index]['description'])) {
            $this->items[$index]['description'] = $product->name;
        }
    }

    public function rowTotal(int $index): float
    {
        $item = $this->items[$index] ?? [];

        return PricingService::calculateLineTotal(
            (float) ($item['qty'] ?? 0),
            (float) ($item['unit_price'] ?? 0),
            0,
            (float) ($item['tax_rate'] ?? 0)
        )['total'];
    }

    public function totals(): array
    {
        $subtotal = 0;
        $tax = 0;
        $total = 0;

        foreach ($this->items as $item) {
            $line = PricingService::calculateLineTotal(
                (float) ($item['qty'] ?? 0),
                (float) ($item['unit_price'] ?? 0),
                0,
                (float) ($item['tax_rate'] ?? 0)
            );

            $subtotal += $line['subtotal'];
            $tax += $line['tax'];
            $total += $line['total'];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
        ];
    }

    protected function rules(): array
    {
        return [
            'supplierId' => ['required', TenantRule::exists('suppliers')],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', TenantRule::exists('products')],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        $supplier = Supplier::findOrFail($validated['supplierId']);
        $items = collect($this->items)
            ->map(function (array $item) {
                $productId = $item['product_id'] ?? null;
                $item['product_id'] = $productId ? Product::findOrFail($productId)->id : null;

                return $item;
            })
            ->all();
        $totals = $this->totals();

        DB::transaction(function () use ($validated, $supplier, $items, $totals) {
            $data = [
                'supplier_id' => $supplier->id,
                'date' => $validated['date'],
                'notes' => $validated['notes'],
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
            ];

            if ($this->purchaseOrderId) {
                $purchaseOrder = PurchaseOrder::findOrFail($this->purchaseOrderId);
                $purchaseOrder->update($data);
            } else {
                $data['number'] = DocumentNumberService::next(DocumentNumberService::TYPE_PURCHASE_ORDER);
                $data['status'] = PurchaseOrderStatus::DRAFT;
                $purchaseOrder = PurchaseOrder::create($data);
            }

            $purchaseOrder->items()->delete();

            foreach ($items as $item) {
                $line = PricingService::calculateLineTotal(
                    (float) $item['qty'],
                    (float) $item['unit_price'],
                    0,
                    (float) ($item['tax_rate'] ?? 0)
                );

                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'] ?: null,
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax' => $line['tax'],
                    'total' => $line['total'],
                ]);
            }
        });

        session()->flash('success', $this->purchaseOrderId ? 'Purchase order updated successfully.' : 'Purchase order created successfully.');
        $this->redirectRoute('purchase-orders.index');
    }

    public function render()
    {
        return view('livewire.purchase-orders.form', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }
}
