<?php

namespace App\Livewire\DeliveryOrders;

use App\Enums\DeliveryOrderStatus;
use App\Models\DeliveryOrder;
use App\Models\SalesOrder;
use App\Support\TenantRule;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?int $deliveryOrderId = null;
    public ?int $salesOrderId = null;

    public string $date = '';
    public string $notes = '';

    public array $items = [];

    public function mount(DeliveryOrder $deliveryOrder = null, SalesOrder $salesOrder = null): void
    {
        if ($deliveryOrder && $deliveryOrder->exists) {
            $this->deliveryOrderId = $deliveryOrder->id;
            $this->salesOrderId = $deliveryOrder->sales_order_id;
            $this->date = $deliveryOrder->date->format('Y-m-d');
            $this->notes = $deliveryOrder->notes ?? '';

            foreach ($deliveryOrder->items as $item) {
                $this->items[] = [
                    'sales_order_item_id' => $item->sales_order_item_id,
                    'product_id' => $item->product_id,
                    'description' => $item->salesOrderItem->description,
                    'qty' => (float) $item->qty,
                    'max_qty' => (float) $item->qty,
                ];
            }
        } elseif ($salesOrder && $salesOrder->exists) {
            $this->salesOrderId = $salesOrder->id;
            $this->date = now()->format('Y-m-d');

            foreach ($salesOrder->items as $item) {
                $remaining = (float) $item->qty - (float) $item->delivered_qty;
                $this->items[] = [
                    'sales_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'qty' => $remaining > 0 ? $remaining : 0,
                    'max_qty' => $remaining,
                ];
            }
        } else {
            $this->date = now()->format('Y-m-d');
        }
    }

    protected function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['required', TenantRule::exists('sales_order_items')],
            'items.*.qty' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if (! $this->salesOrderId) {
            throw ValidationException::withMessages(['salesOrderId' => 'A sales order is required.']);
        }

        $salesOrder = SalesOrder::with('items')->findOrFail($this->salesOrderId);
        $existingDeliveryOrder = $this->deliveryOrderId
            ? DeliveryOrder::with('items')->findOrFail($this->deliveryOrderId)
            : null;

        if ($existingDeliveryOrder && $existingDeliveryOrder->sales_order_id !== $salesOrder->id) {
            abort(403);
        }

        $salesOrderItems = $salesOrder->items->keyBy('id');
        $existingDeliveryQuantities = $existingDeliveryOrder
            ? $existingDeliveryOrder->items->pluck('qty', 'sales_order_item_id')
            : collect();

        $itemsToSave = collect($this->items)
            ->filter(fn ($item) => (float) $item['qty'] > 0)
            ->map(function ($item, $index) use ($salesOrderItems, $existingDeliveryQuantities) {
                $salesOrderItemId = (int) ($item['sales_order_item_id'] ?? 0);
                $salesOrderItem = $salesOrderItems->get($salesOrderItemId);

                if (! $salesOrderItem) {
                    throw ValidationException::withMessages([
                        "items.{$index}.sales_order_item_id" => 'The selected sales order item is invalid.',
                    ]);
                }

                if (! empty($item['product_id']) && (int) $item['product_id'] !== (int) $salesOrderItem->product_id) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => 'The selected product does not match the sales order item.',
                    ]);
                }

                $availableQty = (float) $salesOrderItem->qty
                    - (float) $salesOrderItem->delivered_qty
                    + (float) ($existingDeliveryQuantities->get($salesOrderItemId) ?? 0);

                if ((float) $item['qty'] > $availableQty) {
                    throw ValidationException::withMessages([
                        "items.{$index}.qty" => 'Quantity cannot exceed the remaining quantity.',
                    ]);
                }

                return [
                    'sales_order_item_id' => $salesOrderItem->id,
                    'product_id' => $salesOrderItem->product_id,
                    'qty' => $item['qty'],
                ];
            })
            ->values();

        if ($itemsToSave->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'At least one item must have a quantity greater than 0.']);
        }

        $deliveryOrder = DB::transaction(function () use ($itemsToSave, $validated, $salesOrder, $existingDeliveryOrder) {
            if ($existingDeliveryOrder) {
                $deliveryOrder = $existingDeliveryOrder;
                $deliveryOrder->update([
                    'date' => $validated['date'],
                    'notes' => $validated['notes'],
                ]);
                $deliveryOrder->items()->delete();
            } else {
                $deliveryOrder = DeliveryOrder::create([
                    'sales_order_id' => $salesOrder->id,
                    'number' => DocumentNumberService::next(DocumentNumberService::TYPE_DELIVERY_ORDER),
                    'date' => $validated['date'],
                    'status' => DeliveryOrderStatus::PENDING,
                    'notes' => $validated['notes'],
                ]);
            }

            foreach ($itemsToSave as $item) {
                $deliveryOrder->items()->create([
                    'sales_order_item_id' => $item['sales_order_item_id'],
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                ]);
            }

            return $deliveryOrder;
        });

        session()->flash('success', $this->deliveryOrderId ? 'Delivery order updated successfully.' : 'Delivery order created successfully.');
        $this->redirectRoute('delivery-orders.show', $deliveryOrder);
    }

    public function render()
    {
        return view('livewire.delivery-orders.form');
    }
}
