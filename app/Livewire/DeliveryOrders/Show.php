<?php

namespace App\Livewire\DeliveryOrders;

use App\Enums\DeliveryOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Models\DeliveryOrder;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

use App\Livewire\Traits\HasWhatsappShare;

#[Layout('layouts.app')]
class Show extends Component
{
    use HasWhatsappShare;

    public DeliveryOrder $deliveryOrder;

    public function mount(DeliveryOrder $deliveryOrder): void
    {
        $this->deliveryOrder = $deliveryOrder->load(['salesOrder.customer', 'salesOrder.items', 'items.product', 'items.salesOrderItem']);
    }

    public function markDelivered(): void
    {
        DB::transaction(function () {
            $deliveryOrder = $this->deliveryOrder;
            $salesOrder = $deliveryOrder->salesOrder;

            foreach ($deliveryOrder->items as $item) {
                $salesOrderItem = $item->salesOrderItem;
                $newDelivered = (float) $salesOrderItem->delivered_qty + (float) $item->qty;
                $salesOrderItem->delivered_qty = min($newDelivered, (float) $salesOrderItem->qty);
                $salesOrderItem->save();

                if ($item->product && $item->product->track_stock) {
                    StockService::recordOut($item->product, (float) $item->qty, $deliveryOrder);
                }
            }

            $allDelivered = $salesOrder->items->every(function ($item) {
                return (float) $item->delivered_qty >= (float) $item->qty;
            });

            if ($allDelivered && $salesOrder->status !== SalesOrderStatus::CANCELLED) {
                $salesOrder->update(['status' => SalesOrderStatus::DELIVERED]);
            }

            $deliveryOrder->update(['status' => DeliveryOrderStatus::DELIVERED]);
        });

        $this->deliveryOrder->refresh();
        session()->flash('success', 'Delivery order marked as delivered. Stock has been updated.');
    }

    public function markReturned(): void
    {
        DB::transaction(function () {
            $deliveryOrder = $this->deliveryOrder;
            $salesOrder = $deliveryOrder->salesOrder;

            // Only reverse stock/quantities if the DO was previously DELIVERED
            if ($deliveryOrder->status === DeliveryOrderStatus::DELIVERED) {
                foreach ($deliveryOrder->items as $item) {
                    $salesOrderItem = $item->salesOrderItem;
                    if ($salesOrderItem) {
                        $newDelivered = (float) $salesOrderItem->delivered_qty - (float) $item->qty;
                        $salesOrderItem->delivered_qty = max(0, $newDelivered);
                        $salesOrderItem->save();
                    }

                    if ($item->product && $item->product->track_stock) {
                        // Record stock IN to reverse the OUT movement
                        StockService::recordIn(
                            $item->product,
                            (float) $item->qty,
                            $deliveryOrder,
                            "Stock returned from DO {$deliveryOrder->number}"
                        );
                    }
                }

                // If Sales Order status was DELIVERED, revert it back to PROCESSING
                if ($salesOrder && $salesOrder->status === SalesOrderStatus::DELIVERED) {
                    $salesOrder->update(['status' => SalesOrderStatus::PROCESSING]);
                }
            }

            $deliveryOrder->update(['status' => DeliveryOrderStatus::RETURNED]);
        });

        $this->deliveryOrder->refresh();
        session()->flash('success', 'Delivery order marked as returned. Stock and order quantities have been updated.');
    }

    public function render()
    {
        return view('livewire.delivery-orders.show');
    }
}
