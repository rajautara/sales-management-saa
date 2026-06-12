<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\PriceLevel;
use App\Support\TenantRule;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?int $customerId = null;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $billingAddress = '';
    public string $shippingAddress = '';
    public string $taxNo = '';
    public string $creditLimit = '0';
    public ?int $priceLevelId = null;
    public bool $isActive = true;

    public function mount(Customer $customer = null): void
    {
        if ($customer) {
            $this->customerId = $customer->id;
            $this->name = $customer->name;
            $this->email = $customer->email ?? '';
            $this->phone = $customer->phone ?? '';
            $this->billingAddress = $customer->billing_address ?? '';
            $this->shippingAddress = $customer->shipping_address ?? '';
            $this->taxNo = $customer->tax_no ?? '';
            $this->creditLimit = (string) $customer->credit_limit;
            $this->priceLevelId = $customer->price_level_id;
            $this->isActive = $customer->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'billingAddress' => ['nullable', 'string'],
            'shippingAddress' => ['nullable', 'string'],
            'taxNo' => ['nullable', 'string', 'max:255'],
            'creditLimit' => ['nullable', 'numeric', 'min:0'],
            'priceLevelId' => ['nullable', TenantRule::exists('price_levels')],
            'isActive' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        $priceLevelId = $validated['priceLevelId'] ? PriceLevel::findOrFail($validated['priceLevelId'])->id : null;

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'billing_address' => $validated['billingAddress'],
            'shipping_address' => $validated['shippingAddress'],
            'tax_no' => $validated['taxNo'],
            'credit_limit' => $validated['creditLimit'] ?? 0,
            'price_level_id' => $priceLevelId,
            'is_active' => $validated['isActive'],
        ];

        if ($this->customerId) {
            Customer::findOrFail($this->customerId)->update($data);
        } else {
            Customer::create($data);
        }

        session()->flash('success', $this->customerId ? 'Customer updated successfully.' : 'Customer created successfully.');
        $this->redirectRoute('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.form', [
            'priceLevels' => PriceLevel::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
