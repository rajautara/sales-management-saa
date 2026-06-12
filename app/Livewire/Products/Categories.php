<?php

namespace App\Livewire\Products;

use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Categories extends Component
{
    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';

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

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($validated);
        } else {
            Category::create($validated);
        }

        $this->reset(['editingId', 'name', 'description']);
        session()->flash('success', $this->editingId ? 'Category updated successfully.' : 'Category created successfully.');
    }

    public function edit(Category $category): void
    {
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
    }

    public function delete(Category $category): void
    {
        $category->delete();
        session()->flash('success', 'Category deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'description']);
    }

    public function render()
    {
        return view('livewire.products.categories', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
