<?php

use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Enums\ProductType;
use App\Livewire\SalesOrders\Form as SalesOrderForm;
use App\Livewire\PurchaseOrders\Form as PurchaseOrderForm;
use Livewire\Livewire;
use Database\Seeders\DatabaseSeeder;

it('auto populates unit price when product is selected in sales orders', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::where('email', 'admin@example.com')->first();
    $this->actingAs($user);

    $customer = Customer::create([
        'name' => 'Test Customer',
        'is_active' => true,
    ]);

    $product = Product::create([
        'sku' => 'PROD-777',
        'name' => 'Fancy Product',
        'type' => ProductType::PRODUCT,
        'unit' => 'pcs',
        'cost_price' => 50.00,
        'sell_price' => 150.00,
        'tax_rate' => 5.00,
        'track_stock' => false,
        'quantity_on_hand' => 0,
        'low_stock_threshold' => 1,
        'is_active' => true,
    ]);

    Livewire::test(SalesOrderForm::class)
        ->set('customerId', $customer->id)
        ->set('items.0.product_id', $product->id)
        ->assertSet('items.0.unit_price', 150.00);
});

it('auto populates unit price when product is selected in purchase orders', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::where('email', 'admin@example.com')->first();
    $this->actingAs($user);

    $supplier = Supplier::create([
        'name' => 'Test Supplier',
        'is_active' => true,
    ]);

    $product = Product::create([
        'sku' => 'PROD-888',
        'name' => 'Fancy Material',
        'type' => ProductType::PRODUCT,
        'unit' => 'pcs',
        'cost_price' => 75.00,
        'sell_price' => 200.00,
        'tax_rate' => 10.00,
        'track_stock' => false,
        'quantity_on_hand' => 0,
        'low_stock_threshold' => 1,
        'is_active' => true,
    ]);

    Livewire::test(PurchaseOrderForm::class)
        ->set('supplierId', $supplier->id)
        ->set('items.0.product_id', $product->id)
        ->assertSet('items.0.unit_price', 75.00);
});

test('guest can access public signed invoice route with correct signature', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::where('email', 'admin@example.com')->first();
    $this->actingAs($user);

    $customer = Customer::create([
        'company_id' => $user->company_id,
        'name' => 'Daun Hijau Food House',
        'phone' => '+60123456789',
        'is_active' => true,
    ]);

    $invoice = \App\Models\Invoice::create([
        'company_id' => $user->company_id,
        'customer_id' => $customer->id,
        'number' => 'INV-2026-001',
        'date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => \App\Enums\InvoiceStatus::DRAFT,
        'subtotal' => 471.80,
        'discount' => 0.00,
        'tax' => 0.00,
        'total' => 471.80,
    ]);

    // Logout to simulate guest access
    Auth::logout();

    // 1. Assert that access without a valid signature aborts with 403
    $this->get(route('public.invoices.show', $invoice))
        ->assertStatus(403);

    // 2. Assert that access with a valid temporary signature is successful and displays the invoice details
    $signedUrl = URL::temporarySignedRoute('public.invoices.show', now()->addDays(7), $invoice);

    $this->get($signedUrl)
        ->assertOk()
        ->assertSee('INV-2026-001')
        ->assertSee('Daun Hijau Food House')
        ->assertSee('471.80');

    // 3. Assert that the link expires — access after the TTL aborts with 403
    $this->travel(8)->days();
    $this->get($signedUrl)->assertStatus(403);
    $this->travelBack();
});

test('public document route returns 404 when the company is disabled', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::where('email', 'admin@example.com')->first();
    $this->actingAs($user);

    $customer = Customer::create([
        'company_id' => $user->company_id,
        'name' => 'Daun Hijau Food House',
        'is_active' => true,
    ]);

    $invoice = \App\Models\Invoice::create([
        'company_id' => $user->company_id,
        'customer_id' => $customer->id,
        'number' => 'INV-2026-009',
        'date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => \App\Enums\InvoiceStatus::DRAFT,
        'subtotal' => 100.00,
        'discount' => 0.00,
        'tax' => 0.00,
        'total' => 100.00,
    ]);

    $signedUrl = URL::temporarySignedRoute('public.invoices.show', now()->addDays(7), $invoice);

    // Disable the company, then access the validly-signed public link as a guest.
    $user->company->update(['is_active' => false]);
    Auth::logout();

    $this->get($signedUrl)->assertStatus(404);
});

test('whatsapp link contains correct text and number', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::where('email', 'admin@example.com')->first();
    $this->actingAs($user);

    $customer = Customer::create([
        'company_id' => $user->company_id,
        'name' => 'Daun Hijau Food House',
        'phone' => '+60123456789',
        'is_active' => true,
    ]);

    $invoice = \App\Models\Invoice::create([
        'company_id' => $user->company_id,
        'customer_id' => $customer->id,
        'number' => 'INV-2026-001',
        'date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => \App\Enums\InvoiceStatus::DRAFT,
        'subtotal' => 471.80,
        'discount' => 0.00,
        'tax' => 0.00,
        'total' => 471.80,
    ]);

    $component = Livewire::test(\App\Livewire\Invoices\Show::class, ['invoice' => $invoice]);
    $url = $component->instance()->getWhatsappUrl(
        'Invoice',
        $invoice->number,
        $customer,
        (float) $invoice->total,
        'https://niagawan.my/Hfn1gpst',
        'RM'
    );

    expect($url)->toContain('https://api.whatsapp.com/send?phone=60123456789&text=');
    expect(urldecode($url))->toContain('Hi Daun Hijau Food House, thank you for your purchase, total is RM471.80. Please click at this link to access your invoice: https://niagawan.my/Hfn1gpst');
});

test('admin user can create, edit, and delete users in settings', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::where('email', 'admin@example.com')->first();
    $this->actingAs($admin);

    // 1. Create a User
    Livewire::test(App\Livewire\Settings\Users::class)
        ->set('name', 'New Test Staff')
        ->set('email', 'staffnew@example.com')
        ->set('password', 'secret123')
        ->set('role', 'staff')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('name', '')
        ->assertSet('email', '')
        ->assertSet('password', '');

    $newStaff = User::where('email', 'staffnew@example.com')->first();
    expect($newStaff)->not->toBeNull();
    expect($newStaff->name)->toBe('New Test Staff');
    expect($newStaff->hasRole('staff'))->toBeTrue();

    // 2. Edit a User
    Livewire::test(App\Livewire\Settings\Users::class)
        ->call('edit', $newStaff->id)
        ->assertSet('name', 'New Test Staff')
        ->assertSet('email', 'staffnew@example.com')
        ->assertSet('role', 'staff')
        ->set('name', 'Updated Staff Name')
        ->set('role', 'admin')
        ->call('save')
        ->assertHasNoErrors();

    $newStaff->refresh();
    expect($newStaff->name)->toBe('Updated Staff Name');
    expect($newStaff->hasRole('admin'))->toBeTrue();

    // 3. Delete a User
    Livewire::test(App\Livewire\Settings\Users::class)
        ->call('delete', $newStaff->id)
        ->assertHasNoErrors();

    expect(User::find($newStaff->id))->toBeNull();

    // 4. Delete oneself should fail
    Livewire::test(App\Livewire\Settings\Users::class)
        ->call('delete', $admin->id)
        ->assertSee('You cannot delete yourself.');
});

test('staff user cannot reach admin-only livewire components', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::where('email', 'admin@example.com')->first();

    $staff = User::create([
        'name' => 'Plain Staff',
        'email' => 'plainstaff@example.com',
        'password' => 'secret123',
        'company_id' => $admin->company_id,
    ]);
    $staff->assignRole('staff');
    $this->actingAs($staff);

    // The AuthorizesAdmin boot hook runs on every request (not just mount) and
    // aborts 403 — so the component itself blocks staff, independent of the
    // route-level role middleware.
    Livewire::test(App\Livewire\Settings\Users::class)->assertForbidden();
    Livewire::test(App\Livewire\Products\Discounts::class)->assertForbidden();
    Livewire::test(App\Livewire\Products\PriceLevels::class)->assertForbidden();
    Livewire::test(App\Livewire\Reports\Index::class)->assertForbidden();
});

test('admin user can upload company logo and change company profile in settings', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::where('email', 'admin@example.com')->first();
    $this->actingAs($admin);

    Storage::fake('public');
    $file = \Illuminate\Http\UploadedFile::fake()->image('company_logo.png');

    Livewire::test(App\Livewire\Settings\Index::class)
        ->set('companyName', 'New Company Name')
        ->set('logo', $file)
        ->call('save')
        ->assertHasNoErrors();

    $admin->company->refresh();
    expect($admin->company->name)->toBe('New Company Name');
    expect($admin->company->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($admin->company->logo_path);

    // Now test removing the logo
    Livewire::test(App\Livewire\Settings\Index::class)
        ->call('removeLogo')
        ->assertHasNoErrors();

    $admin->company->refresh();
    expect($admin->company->logo_path)->toBeNull();
});

test('staff user cannot access admin routes or components', function () {
    $this->seed(DatabaseSeeder::class);
    $company = \App\Models\Company::first();
    $staff = User::factory()->create([
        'company_id' => $company->id,
    ]);
    $staff->assignRole('staff');
    $this->actingAs($staff);

    // 1. Verify GET requests abort with 403
    $this->get(route('settings.index'))->assertStatus(403);
    $this->get(route('settings.users'))->assertStatus(403);
    $this->get(route('reports.index'))->assertStatus(403);
    $this->get(route('price-levels.index'))->assertStatus(403);
    $this->get(route('discounts.index'))->assertStatus(403);

    // 2. Verify Livewire direct mount aborts with 403
    Livewire::test(App\Livewire\Settings\Index::class)->assertStatus(403);
    Livewire::test(App\Livewire\Settings\Users::class)->assertStatus(403);
    Livewire::test(App\Livewire\Reports\Index::class)->assertStatus(403);
    Livewire::test(App\Livewire\Products\PriceLevels::class)->assertStatus(403);
    Livewire::test(App\Livewire\Products\Discounts::class)->assertStatus(403);
});
