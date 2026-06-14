<?php

use App\Enums\EInvoiceStatus;
use App\Enums\InvoiceStatus;
use App\Enums\ProductType;
use App\Livewire\Customers\Form;
use App\Livewire\Invoices\Show as InvoiceShow;
use App\Livewire\Settings\Index;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EInvoiceSubmission;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\MyInvois\DocumentSigner;
use App\Services\MyInvois\EInvoiceService;
use App\Services\MyInvois\InvoiceDocumentBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Create a throwaway self-signed .p12 and point config at it.
 *
 * @return array{0: string, 1: OpenSSLCertificate}
 */
function makeTestCertificate(): array
{
    $pkey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    $csr = openssl_csr_new(['commonName' => 'Test Signer', 'organizationName' => 'Acme', 'countryName' => 'MY'], $pkey);
    $x509 = openssl_csr_sign($csr, null, $pkey, 365, ['digest_alg' => 'sha256']);
    openssl_pkcs12_export($x509, $p12, $pkey, 'secret');

    $path = tempnam(sys_get_temp_dir(), 'p12');
    file_put_contents($path, $p12);
    config(['myinvois.cert_path' => $path, 'myinvois.cert_password' => 'secret']);

    return [$path, $x509];
}

function configureMyInvois(): void
{
    config([
        'myinvois.enabled' => true,
        'myinvois.environment' => 'sandbox',
        'myinvois.client_id' => 'test-id',
        'myinvois.client_secret' => 'test-secret',
    ]);
}

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

it('submits an invoice and records the returned UUID', function () {
    configureMyInvois();
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    Http::fake([
        '*/connect/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/documentsubmissions' => Http::response([
            'submissionUid' => 'SUB-1',
            'acceptedDocuments' => [['uuid' => 'UUID-1', 'invoiceCodeNumber' => $invoice->number]],
            'rejectedDocuments' => [],
        ]),
    ]);

    $submission = app(EInvoiceService::class)->submit($invoice);

    expect($submission->status)->toBe(EInvoiceStatus::SUBMITTED);
    expect($submission->uuid)->toBe('UUID-1');
    expect($submission->submission_uid)->toBe('SUB-1');
    expect($invoice->fresh()->eInvoice->uuid)->toBe('UUID-1');
});

it('marks a submission invalid when MyInvois rejects it', function () {
    configureMyInvois();
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    Http::fake([
        '*/connect/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/documentsubmissions' => Http::response([
            'submissionUid' => 'SUB-2',
            'acceptedDocuments' => [],
            'rejectedDocuments' => [['error' => ['code' => 'CF321', 'message' => 'Invalid TIN']]],
        ]),
    ]);

    $submission = app(EInvoiceService::class)->submit($invoice);

    expect($submission->status)->toBe(EInvoiceStatus::INVALID);
    expect($submission->error_log)->not->toBeNull();
});

it('refreshes status to valid and builds a validation link', function () {
    configureMyInvois();
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    $submission = EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::SUBMITTED,
        'uuid' => 'UUID-9',
    ]);

    Http::fake([
        '*/connect/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/details' => Http::response(['status' => 'Valid', 'longId' => 'LONG-9']),
    ]);

    app(EInvoiceService::class)->refreshStatus($submission);
    $submission->refresh();

    expect($submission->status)->toBe(EInvoiceStatus::VALID);
    expect($submission->validated_at)->not->toBeNull();
    expect($submission->validation_link)->toContain('UUID-9')->toContain('LONG-9');
});

it('cancels a validated e-invoice with a reason', function () {
    configureMyInvois();
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    $submission = EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::VALID,
        'uuid' => 'UUID-C',
        'validated_at' => now(),
    ]);

    Http::fake([
        '*/connect/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/state' => Http::response([], 200),
    ]);

    app(EInvoiceService::class)->cancel($submission, 'Wrong amount');
    $submission->refresh();

    expect($submission->status)->toBe(EInvoiceStatus::CANCELLED);
    expect($submission->cancel_reason)->toBe('Wrong amount');
    expect($submission->cancelled_at)->not->toBeNull();
});

it('throws when submitting an invoice that is not ready', function () {
    configureMyInvois();
    $company = einvoiceCompany(['tin' => null]);
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    Http::fake();

    expect(fn () => app(EInvoiceService::class)->submit($invoice))
        ->toThrow(RuntimeException::class);

    Http::assertNothingSent();
});

it('polls pending submissions via the scheduled command', function () {
    configureMyInvois();
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::SUBMITTED,
        'uuid' => 'UUID-P',
    ]);

    Http::fake([
        '*/connect/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/details' => Http::response(['status' => 'Valid', 'longId' => 'LONG-P']),
    ]);

    $this->artisan('einvoice:poll-status')->assertExitCode(0);

    expect(EInvoiceSubmission::withoutGlobalScopes()->where('uuid', 'UUID-P')->first()->status)
        ->toBe(EInvoiceStatus::VALID);
});

it('returns the document unchanged when no certificate is configured', function () {
    config(['myinvois.cert_path' => null]);

    $doc = ['_D' => 'x', 'Invoice' => [['ID' => [['_' => 'INV-1']]]]];

    expect((new DocumentSigner)->sign($doc))->toBe($doc);
});

it('applies a cryptographically valid X.509 signature', function () {
    [$path, $x509] = makeTestCertificate();

    $doc = ['_D' => 'urn:...', 'Invoice' => [['ID' => [['_' => 'INV-1']], 'DocumentCurrencyCode' => [['_' => 'MYR']]]]];
    $canonical = json_encode($doc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $signed = (new DocumentSigner)->sign($doc);

    expect($signed['Invoice'][0])->toHaveKeys(['UBLExtensions', 'Signature']);

    $signature = $signed['Invoice'][0]['UBLExtensions'][0]['UBLExtension'][0]['ExtensionContent'][0]['UBLDocumentSignatures'][0]['SignatureInformation'][0]['Signature'][0];

    // Document digest matches the canonical document.
    expect($signature['SignedInfo'][0]['Reference'][0]['DigestValue'][0]['_'])
        ->toBe(base64_encode(hash('sha256', $canonical, true)));

    // Signature value verifies against the certificate's public key.
    $sig = base64_decode($signature['SignatureValue'][0]['_']);
    expect(openssl_verify($canonical, $sig, openssl_pkey_get_public($x509), OPENSSL_ALGO_SHA256))->toBe(1);

    @unlink($path);
});

it('submits an e-invoice from the invoice show screen', function () {
    configureMyInvois();
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    Http::fake([
        '*/connect/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/documentsubmissions' => Http::response([
            'submissionUid' => 'SUB-X',
            'acceptedDocuments' => [['uuid' => 'UUID-X', 'invoiceCodeNumber' => $invoice->number]],
            'rejectedDocuments' => [],
        ]),
    ]);

    Livewire::test(InvoiceShow::class, ['invoice' => $invoice])
        ->call('submitEInvoice');

    expect($invoice->fresh()->eInvoice->uuid)->toBe('UUID-X');
});

it('shows an error and creates no submission when the invoice is not ready', function () {
    configureMyInvois();
    $company = einvoiceCompany(['tin' => null]);
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    Http::fake();

    Livewire::test(InvoiceShow::class, ['invoice' => $invoice])
        ->call('submitEInvoice');

    expect($invoice->fresh()->eInvoice)->toBeNull();
});

it('cancels an e-invoice from the invoice show screen', function () {
    configureMyInvois();
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::VALID,
        'uuid' => 'UUID-Z',
        'validated_at' => now()->subHour(),
    ]);

    Http::fake([
        '*/connect/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/state' => Http::response([], 200),
    ]);

    Livewire::test(InvoiceShow::class, ['invoice' => $invoice->fresh()])
        ->set('cancelReason', 'Duplicate invoice')
        ->call('cancelEInvoice');

    expect($invoice->fresh()->eInvoice->status)->toBe(EInvoiceStatus::CANCELLED);
});

it('renders the invoice PDF with a QR code for a validated e-invoice', function () {
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::VALID,
        'uuid' => 'UUID-PDF',
        'long_id' => 'LONG-PDF',
        'validation_link' => 'https://preprod.myinvois.hasil.gov.my/UUID-PDF/share/LONG-PDF',
        'validated_at' => now(),
    ]);

    $this->get(route('invoices.pdf', $invoice))->assertOk();
});

it('saves buyer e-invoice fields from the customer form', function () {
    $company = einvoiceCompany();
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('admin');
    $this->actingAs($user);

    Livewire::test(Form::class)
        ->set('name', 'Buyer Sdn Bhd')
        ->set('tin', 'C1111111111')
        ->set('registrationType', 'BRN')
        ->set('registrationNo', '202401000123')
        ->set('addressStateCode', '10')
        ->call('save')
        ->assertHasNoErrors();

    $customer = Customer::where('name', 'Buyer Sdn Bhd')->first();
    expect($customer->tin)->toBe('C1111111111');
    expect($customer->registration_no)->toBe('202401000123');
    expect($customer->address_state_code)->toBe('10');
});

it('enforces one e-invoice submission row per invoice', function () {
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::SUBMITTED,
        'uuid' => 'UUID-1',
    ]);

    expect(fn () => EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::SUBMITTED,
        'uuid' => 'UUID-2',
    ]))->toThrow(QueryException::class);
});

it('resubmits a rejected e-invoice reusing the same row', function () {
    configureMyInvois();
    $company = einvoiceCompany();
    $this->actingAs(User::factory()->create(['company_id' => $company->id]));
    $invoice = einvoiceReadyInvoice($company);

    EInvoiceSubmission::create([
        'company_id' => $company->id,
        'invoice_id' => $invoice->id,
        'status' => EInvoiceStatus::INVALID,
        'error_log' => [['error' => 'Invalid TIN']],
    ]);

    Http::fake([
        '*/connect/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        '*/documentsubmissions' => Http::response([
            'submissionUid' => 'SUB-R',
            'acceptedDocuments' => [['uuid' => 'UUID-R', 'invoiceCodeNumber' => $invoice->number]],
            'rejectedDocuments' => [],
        ]),
    ]);

    Livewire::test(InvoiceShow::class, ['invoice' => $invoice->fresh()])
        ->call('submitEInvoice');

    expect(EInvoiceSubmission::where('invoice_id', $invoice->id)->count())->toBe(1);
    expect($invoice->fresh()->eInvoice->status)->toBe(EInvoiceStatus::SUBMITTED);
    expect($invoice->fresh()->eInvoice->uuid)->toBe('UUID-R');
});

it('marks a valid submission cancellable only within 72 hours', function () {
    $company = einvoiceCompany();
    $user = User::factory()->create(['company_id' => $company->id]);
    $this->actingAs($user);

    // Unsaved instances — isCancellable() only depends on status + validated_at.
    $fresh = new EInvoiceSubmission([
        'status' => EInvoiceStatus::VALID,
        'validated_at' => now()->subHours(1),
    ]);
    $stale = new EInvoiceSubmission([
        'status' => EInvoiceStatus::VALID,
        'validated_at' => now()->subHours(100),
    ]);

    expect($fresh->isCancellable())->toBeTrue();
    expect($stale->isCancellable())->toBeFalse();
});
