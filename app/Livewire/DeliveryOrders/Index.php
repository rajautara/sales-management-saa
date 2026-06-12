<?php

namespace App\Livewire\DeliveryOrders;

use App\Enums\DeliveryOrderStatus;
use App\Models\DeliveryOrder;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $deliveryOrders = DeliveryOrder::query()
            ->with(['salesOrder.customer'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('salesOrder.customer', function ($cq) {
                            $cq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest('date')
            ->paginate(10);

        return view('livewire.delivery-orders.index', [
            'deliveryOrders' => $deliveryOrders,
            'statuses' => DeliveryOrderStatus::cases(),
        ]);
    }
}
