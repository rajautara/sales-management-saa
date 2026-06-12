<?php

namespace App\Livewire\Receipts;

use App\Models\Receipt;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $receipts = Receipt::query()
            ->with(['payment.invoice.customer'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('payment.invoice', function ($iq) {
                            $iq->where('number', 'like', '%' . $this->search . '%')
                                ->orWhereHas('customer', function ($cq) {
                                    $cq->where('name', 'like', '%' . $this->search . '%');
                                });
                        });
                });
            })
            ->latest('date')
            ->paginate(10);

        return view('livewire.receipts.index', compact('receipts'));
    }
}
