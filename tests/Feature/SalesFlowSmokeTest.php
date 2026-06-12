<?php

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Enums\ProductType;
use App\Models\SalesOrder;
use App\Enums\SalesOrderStatus;
use App\Models\DeliveryOrder;
use App\Enums\DeliveryOrderStatus;
use App\Services\StockService;
use Database\Seeders\DatabaseSeeder;

test('sales flow index pages render for authenticated user', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::where('email', 'admin@example.com')->first();

    $this->actingAs($user);

    $this->get(route('quotations.index'))->assertOk();
    $this->get(route('quotations.create'))->assertOk();
    $this->get(route('sales-orders.index'))->assertOk();
    $this->get(route('sales-orders.create'))->assertOk();
    $this->get(route('delivery-orders.index'))->assertOk();
    $this->get(route('invoices.index'))->assertOk();
    $this->get(route('invoices.create'))->assertOk();
    $this->get(route('payments.index'))->assertOk();
    $this->get(route('receipts.index'))->assertOk();
    $this->get(route('reports.index'))->assertOk();
});

test('reports page can export excel', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::where('email', 'admin@example.com')->first();
    $this->actingAs($user);

    Livewire\Livewire::test(App\Livewire\Reports\Index::class)
        ->call('export');
});

test('delivery order status transition and reversal logic works', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::where('email', 'admin@example.com')->first();
    $this->actingAs($user);

    // 1. Create a customer
    $customer = Customer::create([
        'name' => 'Test Customer',
        'is_active' => true,
    ]);

    // 2. Create a product with stock tracking
    $product = Product::create([
        'sku' => 'PROD-100',
        'name' => 'Test Product',
        'type' => ProductType::PRODUCT,
        'unit' => 'pcs',
        'cost_price' => 10.00,
        'sell_price' => 20.00,
        'tax_rate' => 0.00,
        'track_stock' => true,
        'quantity_on_hand' => 0,
        'low_stock_threshold' => 1,
        'is_active' => true,
    ]);

    // Add initial stock
    StockService::recordIn($product, 10, $product, 'Initial stock');
    $product->refresh();
    expect((float) $product->quantity_on_hand)->toBe(10.0);

    // 3. Create a Sales Order
    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'number' => 'SO-TEST-001',
        'date' => now()->format('Y-m-d'),
        'status' => SalesOrderStatus::CONFIRMED,
        'subtotal' => 100.00,
        'discount' => 0.00,
        'tax' => 0.00,
        'total' => 100.00,
    ]);

    $soItem = $salesOrder->items()->create([
        'product_id' => $product->id,
        'description' => 'Test Product Item',
        'qty' => 5,
        'unit_price' => 20.00,
        'discount' => 0.00,
        'tax_rate' => 0.00,
        'tax' => 0.00,
        'total' => 100.00,
        'delivered_qty' => 0,
    ]);

    // 4. Create a Delivery Order
    $deliveryOrder = DeliveryOrder::create([
        'sales_order_id' => $salesOrder->id,
        'number' => 'DO-TEST-001',
        'date' => now()->format('Y-m-d'),
        'status' => DeliveryOrderStatus::PENDING,
    ]);

    $doItem = $deliveryOrder->items()->create([
        'sales_order_item_id' => $soItem->id,
        'product_id' => $product->id,
        'qty' => 5,
    ]);

    // 5. Test markDelivered
    Livewire\Livewire::test(App\Livewire\DeliveryOrders\Show::class, ['deliveryOrder' => $deliveryOrder])
        ->call('markDelivered');

    $deliveryOrder->refresh();
    $salesOrder->refresh();
    $soItem->refresh();
    $product->refresh();

    expect($deliveryOrder->status)->toBe(DeliveryOrderStatus::DELIVERED);
    expect($salesOrder->status)->toBe(SalesOrderStatus::DELIVERED);
    expect((float) $soItem->delivered_qty)->toBe(5.0);
    expect((float) $product->quantity_on_hand)->toBe(5.0);

    // 6. Test markReturned (reversal)
    Livewire\Livewire::test(App\Livewire\DeliveryOrders\Show::class, ['deliveryOrder' => $deliveryOrder])
        ->call('markReturned');

    $deliveryOrder->refresh();
    $salesOrder->refresh();
    $soItem->refresh();
    $product->refresh();

    expect($deliveryOrder->status)->toBe(DeliveryOrderStatus::RETURNED);
    expect($salesOrder->status)->toBe(SalesOrderStatus::PROCESSING); // status should be reset
    expect((float) $soItem->delivered_qty)->toBe(0.0); // quantities should be rolled back
    expect((float) $product->quantity_on_hand)->toBe(10.0); // stock should be restored
});
