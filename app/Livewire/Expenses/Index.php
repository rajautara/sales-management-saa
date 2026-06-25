<?php

namespace App\Livewire\Expenses;

use App\Livewire\Traits\WithSorting;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;
    use WithSorting;

    public string $search = '';
    public ?int $categoryId = null;
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $expenses = Expense::query()
            ->with(['category', 'supplier'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%'.$this->search.'%')
                        ->orWhereHas('supplier', function ($sq) {
                            $sq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->categoryId, fn ($query) => $query->where('expense_category_id', $this->categoryId))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->tap(fn ($query) => $this->applySort($query, ['date', 'amount']))
            ->paginate(10);

        return view('livewire.expenses.index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
