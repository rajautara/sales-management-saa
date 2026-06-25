<?php

namespace App\Livewire\Quotations;

use App\Enums\QuotationStatus;
use App\Livewire\Traits\WithSorting;
use App\Models\Quotation;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithSorting;

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
        $quotations = Quotation::query()
            ->with('customer')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($cq) {
                            $cq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->tap(fn ($query) => $this->applySort($query, ['date', 'number']))
            ->paginate(10);

        return view('livewire.quotations.index', [
            'quotations' => $quotations,
            'statuses' => QuotationStatus::cases(),
        ]);
    }
}
