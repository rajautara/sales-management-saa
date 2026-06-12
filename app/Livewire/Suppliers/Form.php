<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?int $supplierId = null;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $taxNo = '';
    public bool $isActive = true;

    public function mount(Supplier $supplier = null): void
    {
        if ($supplier) {
            $this->supplierId = $supplier->id;
            $this->name = $supplier->name;
            $this->email = $supplier->email ?? '';
            $this->phone = $supplier->phone ?? '';
            $this->address = $supplier->address ?? '';
            $this->taxNo = $supplier->tax_no ?? '';
            $this->isActive = $supplier->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'taxNo' => ['nullable', 'string', 'max:255'],
            'isActive' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'tax_no' => $validated['taxNo'],
            'is_active' => $validated['isActive'],
        ];

        if ($this->supplierId) {
            Supplier::findOrFail($this->supplierId)->update($data);
        } else {
            Supplier::create($data);
        }

        session()->flash('success', $this->supplierId ? 'Supplier updated successfully.' : 'Supplier created successfully.');
        $this->redirectRoute('suppliers.index');
    }

    public function render()
    {
        return view('livewire.suppliers.form');
    }
}
