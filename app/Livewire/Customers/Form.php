<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\PriceLevel;
use App\Support\TenantRule;
use Livewire\Attributes\Layout;
use Livewire\Component;

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

    // e-Invoice (MyInvois) fields
    public string $tin = '';

    public string $registrationType = '';

    public string $registrationNo = '';

    public string $sstRegistrationNo = '';

    public string $addressCity = '';

    public string $addressPostcode = '';

    public string $addressStateCode = '';

    public string $addressCountryCode = 'MYS';

    public function mount(?Customer $customer = null): void
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

            $this->tin = $customer->tin ?? '';
            $this->registrationType = $customer->registration_type ?? '';
            $this->registrationNo = $customer->registration_no ?? '';
            $this->sstRegistrationNo = $customer->sst_registration_no ?? '';
            $this->addressCity = $customer->address_city ?? '';
            $this->addressPostcode = $customer->address_postcode ?? '';
            $this->addressStateCode = $customer->address_state_code ?? '';
            $this->addressCountryCode = $customer->address_country_code ?: 'MYS';
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
            'tin' => ['nullable', 'string', 'max:255'],
            'registrationType' => ['nullable', 'string', 'max:20'],
            'registrationNo' => ['nullable', 'string', 'max:255'],
            'sstRegistrationNo' => ['nullable', 'string', 'max:255'],
            'addressCity' => ['nullable', 'string', 'max:255'],
            'addressPostcode' => ['nullable', 'string', 'max:10'],
            'addressStateCode' => ['nullable', 'string', 'max:2'],
            'addressCountryCode' => ['nullable', 'string', 'max:3'],
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
            'tin' => $validated['tin'],
            'registration_type' => $validated['registrationType'],
            'registration_no' => $validated['registrationNo'],
            'sst_registration_no' => $validated['sstRegistrationNo'],
            'address_city' => $validated['addressCity'],
            'address_postcode' => $validated['addressPostcode'],
            'address_state_code' => $validated['addressStateCode'],
            'address_country_code' => $validated['addressCountryCode'] ?: 'MYS',
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
