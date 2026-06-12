<?php

namespace App\Livewire\PurchaseOrders;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Show extends Component
{
    public PurchaseOrder $purchaseOrder;

    public array $received = [];
    public bool $showReceiveForm = false;

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->resetReceived();
    }

    protected function resetReceived(): void
    {
        $this->received = [];
        foreach ($this->purchaseOrder->items as $item) {
            $this->received[$item->id] = [
                'qty' => '0',
            ];
        }
    }

    public function updateStatus(string $status): void
    {
        $this->purchaseOrder->update(['status' => PurchaseOrderStatus::from($status)]);
        $this->purchaseOrder->refresh();
        session()->flash('success', 'Purchase order status updated.');
    }

    public function saveReceive(): void
    {
        $rules = [];
        foreach ($this->purchaseOrder->items as $item) {
            $rules["received.{$item->id}.qty"] = ['required', 'numeric', 'min:0'];
        }

        $validated = $this->validate($rules);

        DB::transaction(function () use ($validated) {
            $allFullyReceived = true;

            foreach ($this->purchaseOrder->items as $item) {
                $receiveQty = (float) ($validated['received'][$item->id]['qty'] ?? 0);
                if ($receiveQty <= 0) {
                    if ((float) $item->received_qty < (float) $item->qty) {
                        $allFullyReceived = false;
                    }
                    continue;
                }

                $newReceived = (float) $item->received_qty + $receiveQty;
                $item->update(['received_qty' => $newReceived]);

                if ($item->product && $item->product->track_stock) {
                    StockService::recordIn($item->product, $receiveQty, $this->purchaseOrder, "Received from PO {$this->purchaseOrder->number}");
                }

                if ($newReceived < (float) $item->qty) {
                    $allFullyReceived = false;
                }
            }

            $this->purchaseOrder->update([
                'status' => $allFullyReceived ? PurchaseOrderStatus::RECEIVED : PurchaseOrderStatus::PARTIAL,
            ]);
        });

        $this->purchaseOrder->refresh();
        $this->showReceiveForm = false;
        $this->resetReceived();
        session()->flash('success', 'Goods received recorded successfully.');
    }

    public function render()
    {
        return view('livewire.purchase-orders.show', [
            'statuses' => PurchaseOrderStatus::cases(),
        ]);
    }
}
