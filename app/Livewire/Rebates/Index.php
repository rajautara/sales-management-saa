<?php

namespace App\Livewire\Rebates;

use App\Enums\PaymentMethod;
use App\Livewire\Traits\WithSorting;
use App\Models\Rebate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithSorting;

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

    public function delete(int $id): void
    {
        Rebate::findOrFail($id)->delete();

        session()->flash('success', 'Rebate deleted.');
    }

    public function render()
    {
        $rebates = Rebate::query()
            ->with(['invoice.customer', 'customer'])
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
            ->tap(fn ($query) => $this->applySort($query, ['date', 'amount']))
            ->paginate(10);

        return view('livewire.rebates.index', [
            'rebates' => $rebates,
            'methods' => PaymentMethod::cases(),
        ]);
    }
}
