<?php

namespace App\Livewire\Settings;

use App\Livewire\Traits\AuthorizesAdmin;
use App\Models\Company;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Index extends Component
{
    use AuthorizesAdmin;
    use WithFileUploads;

    public string $companyName = '';

    public ?string $companyRegistrationNo = null;

    public ?string $companyAddress = null;

    public ?string $companyPhone = null;

    public ?string $companyEmail = null;

    public string $companyCurrency = 'MYR';

    // e-Invoice (MyInvois) company fields
    public ?string $companyTin = null;

    public ?string $companySstRegistrationNo = null;

    public ?string $companyMsicCode = null;

    public ?string $companyBusinessActivityDesc = null;

    public ?string $companyAddressCity = null;

    public ?string $companyAddressPostcode = null;

    public ?string $companyAddressStateCode = null;

    public string $companyAddressCountryCode = 'MYS';

    public $logo;

    public ?string $existingLogoPath = null;

    public string $taxDefault = '0';

    public string $quotationPrefix = 'QT';

    public string $salesOrderPrefix = 'SO';

    public string $deliveryOrderPrefix = 'DO';

    public string $invoicePrefix = 'INV';

    public string $receiptPrefix = 'RC';

    public string $purchaseOrderPrefix = 'PO';

    public ?string $invoiceTerms = null;

    public ?string $bankName = null;

    public ?string $bankAccountName = null;

    public ?string $bankAccountNo = null;

    public function mount(): void
    {
        $company = auth()->user()?->company;

        if ($company instanceof Company) {
            $this->companyName = $company->name;
            $this->companyRegistrationNo = $company->registration_no;
            $this->companyAddress = $company->address;
            $this->companyPhone = $company->phone;
            $this->companyEmail = $company->email;
            $this->companyCurrency = $company->currency;
            $this->existingLogoPath = $company->logo_path;

            $this->companyTin = $company->tin;
            $this->companySstRegistrationNo = $company->sst_registration_no;
            $this->companyMsicCode = $company->msic_code;
            $this->companyBusinessActivityDesc = $company->business_activity_desc;
            $this->companyAddressCity = $company->address_city;
            $this->companyAddressPostcode = $company->address_postcode;
            $this->companyAddressStateCode = $company->address_state_code;
            $this->companyAddressCountryCode = $company->address_country_code ?: 'MYS';
        }

        $this->taxDefault = (string) Setting::get('tax_default', '0');
        $this->quotationPrefix = (string) Setting::get('quotation_prefix', 'QT');
        $this->salesOrderPrefix = (string) Setting::get('sales_order_prefix', 'SO');
        $this->deliveryOrderPrefix = (string) Setting::get('delivery_order_prefix', 'DO');
        $this->invoicePrefix = (string) Setting::get('invoice_prefix', 'INV');
        $this->receiptPrefix = (string) Setting::get('receipt_prefix', 'RC');
        $this->purchaseOrderPrefix = (string) Setting::get('purchase_order_prefix', 'PO');
        $this->invoiceTerms = Setting::get('invoice_terms', '');
        $this->bankName = Setting::get('bank_name', '');
        $this->bankAccountName = Setting::get('bank_account_name', '');
        $this->bankAccountNo = Setting::get('bank_account_no', '');
    }

    protected function rules(): array
    {
        return [
            'companyName' => ['required', 'string', 'max:255'],
            'companyRegistrationNo' => ['nullable', 'string', 'max:255'],
            'companyAddress' => ['nullable', 'string'],
            'companyPhone' => ['nullable', 'string', 'max:255'],
            'companyEmail' => ['nullable', 'email', 'max:255'],
            'companyCurrency' => ['required', 'string', 'max:3'],
            'companyTin' => ['nullable', 'string', 'max:255'],
            'companySstRegistrationNo' => ['nullable', 'string', 'max:255'],
            'companyMsicCode' => ['nullable', 'string', 'max:10'],
            'companyBusinessActivityDesc' => ['nullable', 'string', 'max:255'],
            'companyAddressCity' => ['nullable', 'string', 'max:255'],
            'companyAddressPostcode' => ['nullable', 'string', 'max:10'],
            'companyAddressStateCode' => ['nullable', 'string', 'max:2'],
            'companyAddressCountryCode' => ['nullable', 'string', 'max:3'],
            'logo' => ['nullable', 'image', 'max:1024'], // max 1MB
            'taxDefault' => ['nullable', 'numeric', 'min:0'],
            'quotationPrefix' => ['nullable', 'string', 'max:50'],
            'salesOrderPrefix' => ['nullable', 'string', 'max:50'],
            'deliveryOrderPrefix' => ['nullable', 'string', 'max:50'],
            'invoicePrefix' => ['nullable', 'string', 'max:50'],
            'receiptPrefix' => ['nullable', 'string', 'max:50'],
            'purchaseOrderPrefix' => ['nullable', 'string', 'max:50'],
            'invoiceTerms' => ['nullable', 'string'],
            'bankName' => ['nullable', 'string', 'max:255'],
            'bankAccountName' => ['nullable', 'string', 'max:255'],
            'bankAccountNo' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $company = auth()->user()?->company;

        if ($company instanceof Company) {
            $company->update([
                'name' => $validated['companyName'],
                'registration_no' => $validated['companyRegistrationNo'],
                'address' => $validated['companyAddress'],
                'phone' => $validated['companyPhone'],
                'email' => $validated['companyEmail'],
                'currency' => $validated['companyCurrency'],
                'tin' => $validated['companyTin'],
                'sst_registration_no' => $validated['companySstRegistrationNo'],
                'msic_code' => $validated['companyMsicCode'],
                'business_activity_desc' => $validated['companyBusinessActivityDesc'],
                'address_city' => $validated['companyAddressCity'],
                'address_postcode' => $validated['companyAddressPostcode'],
                'address_state_code' => $validated['companyAddressStateCode'],
                'address_country_code' => $validated['companyAddressCountryCode'] ?: 'MYS',
            ]);

            if ($this->logo) {
                $path = $this->logo->store('logos', 'public');
                $company->update(['logo_path' => $path]);
                $this->existingLogoPath = $path;
                $this->reset('logo');
            }
        }

        Setting::set('tax_default', $validated['taxDefault']);
        Setting::set('quotation_prefix', $validated['quotationPrefix']);
        Setting::set('sales_order_prefix', $validated['salesOrderPrefix']);
        Setting::set('delivery_order_prefix', $validated['deliveryOrderPrefix']);
        Setting::set('invoice_prefix', $validated['invoicePrefix']);
        Setting::set('receipt_prefix', $validated['receiptPrefix']);
        Setting::set('purchase_order_prefix', $validated['purchaseOrderPrefix']);
        Setting::set('invoice_terms', $validated['invoiceTerms']);
        Setting::set('bank_name', $validated['bankName']);
        Setting::set('bank_account_name', $validated['bankAccountName']);
        Setting::set('bank_account_no', $validated['bankAccountNo']);

        session()->flash('success', 'Settings saved successfully.');
    }

    public function removeLogo(): void
    {
        $company = auth()->user()?->company;

        if ($company instanceof Company) {
            $company->update(['logo_path' => null]);
            $this->existingLogoPath = null;
            $this->reset('logo');
            session()->flash('success', 'Logo removed successfully.');
        }
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}
