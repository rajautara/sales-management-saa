<?php

namespace App\Livewire\PurchaseOrders;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
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
        $purchaseOrders = PurchaseOrder::query()
            ->with('supplier')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('supplier', function ($sq) {
                            $sq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest('date')
            ->paginate(10);

        return view('livewire.purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'statuses' => PurchaseOrderStatus::cases(),
        ]);
    }
}
