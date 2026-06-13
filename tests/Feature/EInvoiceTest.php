<?php

use App\Enums\EInvoiceStatus;
use App\Enums\InvoiceStatus;
use App\Enums\ProductType;
use App\Livewire\Settings\Index;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EInvoiceSubmission;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\MyInvois\EInvoiceService;
use App\Services\MyInvois\InvoiceDocumentBuilder;
use Livewire\Livewire;

function einvoiceCompany(array $overrides = []): Company
{
    return Company::create(array_merge([
        'name' => 'Acme Sdn Bhd',
        'registration_no' => '202301000001',
        'tin' => 'C1234567890',
        'sst_registration_no' => 'A01-2345-67890123',
        'msic_code' => '46900',
        'business_activity_desc' => 'Wholesale trade',
        'address' => '12 Jalan Utama',
        'address_city' => 'Kuala Lumpur',
        'address_postcode' => '50000',
        'address_state_code' => '14',
        'address_country_code' => 'MYS',
        'phone' => '0312345678',
        'email' => 'billing@acme.test',
        'currency' => 'MYR',
        'is_active' => true,
    ], $overrides));
}

function einvoiceReadyInvoice(Company $company): Invoice
{
    $customer = Customer::create([
        'company_id' => $company->id,
        'name' => 'Buyer Sdn Bhd',
        'email' => 'buyer@test.test',
        'phone' => '0398765432',
        'billing_address' => '99 Jalan Pembeli',
        'address_city' => 'Petaling Jaya',
        'address_postcode' => '47800',
        'address_state_code' => '10',
        'address_country_code' => 'MYS',
        'tin' => 'C9876543210',
        'registration_type' => 'BRN',
        'registration_no' => '202401000099',
        'is_active' => true,
    ]);

    $product = Product::create([
        'company_id' => $company->id,
        'name' => 'Widget',
        'type' => ProductType::PRODUCT,
        'unit' => 'pcs',
        'cost_price' => 5,
        'sell_price' => 10,
        'tax_rate' => 0,
        'classification_code' => '022',
        'uom_code' => 'UNT',
        'tax_type' => '06',
        'track_stock' => false,
        'quantity_on_hand' => 0,
        'low_stock_threshold' => 0,
        'is_active' => true,
    ]);

    $invoice = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'number' => 'INV-2026-0001',
        'date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => InvoiceStatus::SENT,
        'subtotal' => 100,
        'discount' => 0,
        'tax' => 0,
        'total' => 100,
        'amount_paid' => 0,
    ]);

    $invoice->items()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'description' => 'Widget',
        'qty' => 10,
        'unit_price' => 10,
        'discount' => 0,
        'tax_rate' => 0,
        'tax' => 0,
        'total' => 100,
    ]);

    return $invoice->load(['company', 'customer', 'items.product']);
}

it('passes readiness validation for a complete invoice', function () {
    $company = einvoiceCompany();
    $user = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    $invoice = einvoiceReadyInvoice($company);

    $errors = app(EInvoiceService::class)->validateReadiness($invoice);

    expect($errors)->toBe([]);
    expect(app(EInvoiceService::class)->isReady($invoice))->toBeTrue();
});

it('reports missing mandatory fields in readiness validation', function () {
    $company = einvoiceCompany(['tin' => null, 'address_state_code' => null]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    $invoice = einvoiceReadyInvoice($company);
    // Strip mandatory buyer fields.
    $invoice->customer->update(['tin' => null, 'registration_no' => null]);
    $invoice->refresh();

    $errors = app(EInvoiceService::class)->validateReadiness($invoice);

    expect($errors)->not->toBe([]);
    expect(implode(' ', $errors))
        ->toContain('Company TIN')
        ->toContain('state code')
        ->toContain('Customer TIN');
});

it('builds a UBL 2.1 document with supplier, buyer, lines and totals', function () {
    $company = einvoiceCompany();
    $user = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    $invoice = einvoiceReadyInvoice($company);

    $doc = (new InvoiceDocumentBuilder)->build($invoice);

    expect($doc['_D'])->toContain('Invoice-2');

    $ubl = $doc['Invoice'][0];
    expect($ubl['ID'][0]['_'])->toBe('INV-2026-0001');
    expect($ubl['DocumentCurrencyCode'][0]['_'])->toBe('MYR');

    // Supplier TIN present.
    $supplierIds = collect($ubl['AccountingSupplierParty'][0]['Party'][0]['PartyIdentification'])
        ->pluck('ID.0._');
    expect($supplierIds)->toContain('C1234567890');

    // Buyer TIN present.
    $buyerIds = collect($ubl['AccountingCustomerParty'][0]['Party'][0]['PartyIdentification'])
        ->pluck('ID.0._');
    expect($buyerIds)->toContain('C9876543210');

    // One line with correct amount and classification.
    expect($ubl['InvoiceLine'])->toHaveCount(1);
    $line = $ubl['InvoiceLine'][0];
    expect($line['LineExtensionAmount'][0]['_'])->toBe('100.00');
    expect($line['Item'][0]['CommodityClassification'][0]['ItemClassificationCode'][0]['_'])->toBe('022');

    // Monetary total payable.
    expect($ubl['LegalMonetaryTotal'][0]['PayableAmount'][0]['_'])->toBe('100.00');
});

it('uses default codes when product has none', function () {
    $company = einvoiceCompany();
    $user = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    $invoice = einvoiceReadyInvoice($company);
    $invoice->items->first()->product->update([
        'classification_code' => null,
        'uom_code' => null,
    ]);
    $invoice->load('items.product');

    $line = (new InvoiceDocumentBuilder)->build($invoice)['Invoice'][0]['InvoiceLine'][0];

    expect($line['Item'][0]['CommodityClassification'][0]['ItemClassificationCode'][0]['_'])
        ->toBe(config('myinvois.defaults.classification_code'));
    expect($line['InvoicedQuantity'][0]['unitCode'])
        ->toBe(config('myinvois.defaults.uom_code'));
});

it('isolates e-invoice submissions between companies', function () {
    $companyA = einvoiceCompany(['name' => 'Company A']);
    $companyB = einvoiceCompany(['name' => 'Company B']);

    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $userB = User::factory()->create(['company_id' => $companyB->id]);

    $invoiceA = einvoiceReadyInvoice($companyA);
    $invoiceB = einvoiceReadyInvoice($companyB);

    EInvoiceSubmission::create([
        'company_id' => $companyA->id,
        'invoice_id' => $invoiceA->id,
        'status' => EInvoiceStatus::VALID,
        'uuid' => 'UUID-A',
    ]);
    EInvoiceSubmission::create([
        'company_id' => $companyB->id,
        'invoice_id' => $invoiceB->id,
        'status' => EInvoiceStatus::VALID,
        'uuid' => 'UUID-B',
    ]);

    $this->actingAs($userA);
    expect(EInvoiceSubmission::pluck('uuid')->all())->toBe(['UUID-A']);

    $this->actingAs($userB);
    expect(EInvoiceSubmission::pluck('uuid')->all())->toBe(['UUID-B']);
});

it('saves company e-invoice fields from settings', function () {
    $company = einvoiceCompany(['tin' => null, 'msic_code' => null]);
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('admin');
    $this->actingAs($user);

    Livewire::test(Index::class)
        ->set('companyTin', 'C5555555555')
        ->set('companyMsicCode', '47190')
        ->set('companyAddressStateCode', '10')
        ->call('save')
        ->assertHasNoErrors();

    $company->refresh();
    expect($company->tin)->toBe('C5555555555');
    expect($company->msic_code)->toBe('47190');
    expect($company->address_state_code)->toBe('10');
});

it('marks a valid submission cancellable only within 72 hours', function () {
    $company = einvoiceCompany();
    $user = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    $invoice = einvoiceReadyInvoice($company);

    $fresh = EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::VALID,
        'uuid' => 'UUID-FRESH',
        'validated_at' => now()->subHours(1),
    ]);
    $stale = EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::VALID,
        'uuid' => 'UUID-STALE',
        'validated_at' => now()->subHours(100),
    ]);

    expect($fresh->isCancellable())->toBeTrue();
    expect($stale->isCancellable())->toBeFalse();
});
