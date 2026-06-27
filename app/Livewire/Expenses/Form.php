<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Supplier;
use App\Support\TenantRule;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Form extends Component
{
    use WithFileUploads;

    public ?int $expenseId = null;

    public string $date = '';
    public ?int $expenseCategoryId = null;
    public ?int $supplierId = null;
    public string $description = '';
    public string $amount = '';
    public $receipt = null;
    public ?string $existingReceipt = null;

    public function mount(Expense $expense = null): void
    {
        if ($expense && $expense->exists) {
            $this->expenseId = $expense->id;
            $this->date = $expense->date->format('Y-m-d');
            $this->expenseCategoryId = $expense->expense_category_id;
            $this->supplierId = $expense->supplier_id;
            $this->description = $expense->description;
            $this->amount = (string) $expense->amount;
            $this->existingReceipt = $expense->receipt_attachment;
        } else {
            $this->date = now()->format('Y-m-d');
        }
    }

    protected function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'expenseCategoryId' => ['required', TenantRule::exists('expense_categories')],
            'supplierId' => ['nullable', TenantRule::exists('suppliers')],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        $expenseCategory = ExpenseCategory::findOrFail($validated['expenseCategoryId']);
        $supplierId = $validated['supplierId'] ? Supplier::findOrFail($validated['supplierId'])->id : null;

        $path = $this->existingReceipt;

        if ($this->receipt) {
            if ($this->existingReceipt && Storage::disk('local')->exists($this->existingReceipt)) {
                Storage::disk('local')->delete($this->existingReceipt);
            }
            // Stored on the private 'local' disk; served only through the
            // authenticated, company-scoped ExpenseReceiptController.
            $path = $this->receipt->store('expenses', 'local');
        }

        $data = [
            'date' => $validated['date'],
            'expense_category_id' => $expenseCategory->id,
            'supplier_id' => $supplierId,
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'receipt_attachment' => $path,
        ];

        if ($this->expenseId) {
            Expense::findOrFail($this->expenseId)->update($data);
        } else {
            Expense::create($data);
        }

        session()->flash('success', $this->expenseId ? 'Expense updated successfully.' : 'Expense created successfully.');
        $this->redirectRoute('expenses.index');
    }

    public function render()
    {
        return view('livewire.expenses.form', [
            'categories' => ExpenseCategory::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }
}
