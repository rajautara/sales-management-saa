<?php

use App\Enums\InvoiceStatus;
use App\Enums\ProductType;
use App\Livewire\Advisor\Index;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\Advisor\AdvisorService;
use App\Services\Advisor\FinancialSnapshotService;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function advisorCompany(string $name = 'Acme'): Company
{
    return Company::create(['name' => $name, 'currency' => 'MYR', 'is_active' => true]);
}

function advisorAdmin(Company $company): User
{
    $user = User::factory()->create(['company_id' => $company->id]);
    $user->assignRole('admin');

    return $user;
}

/**
 * Create a paid invoice with a single product line, plus the product.
 */
function paidInvoice(Company $company, Customer $customer, float $sell, float $cost, float $qty, string $number, ?string $date = null): Invoice
{
    $product = Product::create([
        'company_id' => $company->id,
        'name' => 'Widget '.$number,
        'type' => ProductType::PRODUCT,
        'unit' => 'pcs',
        'cost_price' => $cost,
        'sell_price' => $sell,
        'tax_rate' => 0,
        'track_stock' => false,
        'quantity_on_hand' => 0,
        'low_stock_threshold' => 0,
        'is_active' => true,
    ]);

    $total = $sell * $qty;
    $invoice = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'number' => $number,
        'date' => $date ?? now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'status' => InvoiceStatus::PAID,
        'subtotal' => $total,
        'discount' => 0,
        'tax' => 0,
        'total' => $total,
        'amount_paid' => $total,
    ]);

    $invoice->items()->create([
        'company_id' => $company->id,
        'product_id' => $product->id,
        'description' => $product->name,
        'qty' => $qty,
        'unit_price' => $sell,
        'discount' => 0,
        'tax_rate' => 0,
        'tax' => 0,
        'total' => $total,
    ]);

    return $invoice;
}

it('builds an accurate profit-and-loss snapshot', function () {
    $company = advisorCompany();
    $this->actingAs(advisorAdmin($company));

    $customer = Customer::create(['company_id' => $company->id, 'name' => 'Buyer', 'is_active' => true]);

    // Sales 100, COGS 10*5=50.
    paidInvoice($company, $customer, sell: 10, cost: 5, qty: 10, number: 'INV-A-1');

    Expense::create([
        'company_id' => $company->id,
        'date' => now()->toDateString(),
        'description' => 'Rent',
        'amount' => 20,
    ]);

    $snapshot = app(FinancialSnapshotService::class)->build('this_month');
    $pnl = $snapshot['profit_and_loss'];

    expect($pnl['sales'])->toBe(100.0);
    expect($pnl['cogs'])->toBe(50.0);
    expect($pnl['expenses'])->toBe(20.0);
    expect($pnl['net_profit'])->toBe(30.0);
    expect($pnl['margin_pct'])->toBe(30.0);
    expect($snapshot['currency'])->toBe('MYR');
});

it('buckets outstanding receivables by age', function () {
    $company = advisorCompany();
    $this->actingAs(advisorAdmin($company));

    $customer = Customer::create(['company_id' => $company->id, 'name' => 'Buyer', 'is_active' => true]);

    // Overdue by ~45 days, unpaid 200 → d31_60 bucket.
    Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'number' => 'INV-OD-1',
        'date' => now()->subDays(50)->toDateString(),
        'due_date' => now()->subDays(45)->toDateString(),
        'status' => InvoiceStatus::SENT,
        'subtotal' => 200,
        'discount' => 0,
        'tax' => 0,
        'total' => 200,
        'amount_paid' => 0,
    ]);

    $receivables = app(FinancialSnapshotService::class)->build('this_month')['receivables'];

    expect($receivables['total_outstanding'])->toBe(200.0);
    expect($receivables['overdue_amount'])->toBe(200.0);
    expect($receivables['overdue_invoice_count'])->toBe(1);
    expect($receivables['aging']['d31_60']['amount'])->toBe(200.0);
    expect($receivables['aging']['d31_60']['count'])->toBe(1);
});

it('never leaks customer names into the snapshot payload', function () {
    $company = advisorCompany();
    $this->actingAs(advisorAdmin($company));

    $customer = Customer::create([
        'company_id' => $company->id,
        'name' => 'Top Secret Customer Sdn Bhd',
        'is_active' => true,
    ]);
    paidInvoice($company, $customer, sell: 10, cost: 5, qty: 5, number: 'INV-PII-1');

    $snapshot = app(FinancialSnapshotService::class)->build('this_month');
    $json = json_encode($snapshot);

    expect($json)->not->toContain('Top Secret Customer Sdn Bhd');
});

it('isolates snapshot data between companies', function () {
    $companyA = advisorCompany('Company A');
    $companyB = advisorCompany('Company B');

    $customerA = Customer::create(['company_id' => $companyA->id, 'name' => 'A Buyer', 'is_active' => true]);
    $customerB = Customer::create(['company_id' => $companyB->id, 'name' => 'B Buyer', 'is_active' => true]);

    paidInvoice($companyA, $customerA, sell: 10, cost: 5, qty: 10, number: 'INV-A'); // 100
    paidInvoice($companyB, $customerB, sell: 10, cost: 5, qty: 99, number: 'INV-B'); // 990

    $this->actingAs(advisorAdmin($companyA));

    $snapshot = app(FinancialSnapshotService::class)->build('this_month');

    expect($snapshot['profit_and_loss']['sales'])->toBe(100.0);
});

it('generates a review via the OpenAI-compatible endpoint', function () {
    config(['ai.enabled' => true, 'ai.api_key' => 'test-key', 'ai.base_url' => 'https://api.openai.com/v1', 'ai.model' => 'gpt-4o-mini']);

    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => '## Ringkasan Kesihatan\nSihat.']]],
        ]),
    ]);

    $review = app(AdvisorService::class)->review(['profit_and_loss' => ['sales' => 100.0]]);

    expect($review)->toContain('Ringkasan Kesihatan');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/chat/completions')
            && $request['model'] === 'gpt-4o-mini';
    });
});

it('surfaces a friendly error when the AI endpoint fails', function () {
    config(['ai.enabled' => true, 'ai.api_key' => 'bad-key']);

    Http::fake([
        '*/chat/completions' => Http::response(['error' => ['message' => 'Invalid API key']], 401),
    ]);

    expect(fn () => app(AdvisorService::class)->review(['x' => 1]))
        ->toThrow(RuntimeException::class);
});

it('appends the assistant answer when a question is sent', function () {
    config(['ai.enabled' => true, 'ai.api_key' => 'test-key']);

    $company = advisorCompany();
    $this->actingAs(advisorAdmin($company));

    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Cashflow anda sihat.']]],
        ]),
    ]);

    Livewire::test(Index::class)
        ->set('input', 'Macam mana cashflow saya?')
        ->call('send')
        ->assertSet('error', null)
        ->assertCount('messages', 2);
});

it('blocks staff from the advisor route', function () {
    $company = advisorCompany();
    $staff = User::factory()->create(['company_id' => $company->id]);
    $staff->assignRole('staff');

    $this->actingAs($staff)->get(route('advisor.index'))->assertForbidden();
});
