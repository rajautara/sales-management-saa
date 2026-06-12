<?php

namespace App\Livewire\Expenses;

use App\Models\ExpenseCategory;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Categories extends Component
{
    use WithPagination;

    public ?int $categoryId = null;
    public string $name = '';
    public string $description = '';

    public function edit(ExpenseCategory $category): void
    {
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
    }

    public function resetForm(): void
    {
        $this->reset(['categoryId', 'name', 'description']);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->categoryId) {
            ExpenseCategory::findOrFail($this->categoryId)->update($validated);
            session()->flash('success', 'Category updated successfully.');
        } else {
            ExpenseCategory::create($validated);
            session()->flash('success', 'Category created successfully.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function delete(ExpenseCategory $category): void
    {
        $category->delete();
        session()->flash('success', 'Category deleted successfully.');
    }

    public function render()
    {
        return view('livewire.expenses.categories', [
            'categories' => ExpenseCategory::orderBy('name')->paginate(10),
        ]);
    }
}
