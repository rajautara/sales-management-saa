<?php

namespace App\Livewire\Payments;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $method = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMethod(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $payments = Payment::query()
            ->with(['invoice.customer', 'receipt'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('reference_no', 'like', '%' . $this->search . '%')
                        ->orWhereHas('invoice', function ($iq) {
                            $iq->where('number', 'like', '%' . $this->search . '%')
                                ->orWhereHas('customer', function ($cq) {
                                    $cq->where('name', 'like', '%' . $this->search . '%');
                                });
                        });
                });
            })
            ->when($this->method !== '', fn ($query) => $query->where('method', $this->method))
            ->latest('date')
            ->paginate(10);

        return view('livewire.payments.index', [
            'payments' => $payments,
            'methods' => PaymentMethod::cases(),
        ]);
    }
}
