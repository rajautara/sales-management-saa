<?php

namespace App\Livewire\SalesOrders;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use Livewire\Component;
use Livewire\Attributes\Layout;

use App\Livewire\Traits\HasWhatsappShare;

#[Layout('layouts.app')]
class Show extends Component
{
    use HasWhatsappShare;

    public SalesOrder $salesOrder;

    public function mount(SalesOrder $salesOrder): void
    {
        $this->salesOrder = $salesOrder->load(['customer', 'quotation', 'items.product', 'deliveryOrders', 'invoices']);
    }

    public function confirm(): void
    {
        $this->salesOrder->update(['status' => SalesOrderStatus::CONFIRMED]);
        $this->salesOrder->refresh();
        session()->flash('success', 'Sales order confirmed.');
    }

    public function process(): void
    {
        $this->salesOrder->update(['status' => SalesOrderStatus::PROCESSING]);
        $this->salesOrder->refresh();
        session()->flash('success', 'Sales order moved to processing.');
    }

    public function complete(): void
    {
        $this->salesOrder->update(['status' => SalesOrderStatus::COMPLETED]);
        $this->salesOrder->refresh();
        session()->flash('success', 'Sales order completed.');
    }

    public function cancel(): void
    {
        $this->salesOrder->update(['status' => SalesOrderStatus::CANCELLED]);
        $this->salesOrder->refresh();
        session()->flash('success', 'Sales order cancelled.');
    }

    public function render()
    {
        return view('livewire.sales-orders.show');
    }
}
