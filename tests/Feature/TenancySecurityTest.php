<?php

use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use App\Enums\ProductType;
use App\Enums\SalesOrderStatus;
use App\Livewire\Customers\Form as CustomerForm;
use App\Livewire\DeliveryOrders\Form as DeliveryOrderForm;
use App\Livewire\Expenses\Form as ExpenseForm;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Livewire\Products\Discounts;
use App\Livewire\Products\Form as ProductForm;
use App\Livewire\Products\PriceLevels;
use App\Livewire\PurchaseOrders\Form as PurchaseOrderForm;
use App\Livewire\Quotations\Form as QuotationForm;
use App\Livewire\SalesOrders\Form as SalesOrderForm;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\PriceLevel;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

function tenantSecurityFixture(): array
{
    $companyA = Company::create(['name' => 'Tenant A', 'currency' => 'MYR', 'is_active' => true]);
    $companyB = Company::create(['name' => 'Tenant B', 'currency' => 'MYR', 'is_active' => true]);

    $adminA = User::factory()->create(['company_id' => $companyA->id]);
    $adminA->assignRole('admin');
    $adminB = User::factory()->create(['company_id' => $companyB->id]);
    $adminB->assignRole('admin');

    $customerA = Customer::create(['company_id' => $companyA->id, 'name' => 'Customer A', 'is_active' => true]);
    $customerB = Customer::create(['company_id' => $companyB->id, 'name' => 'Customer B', 'is_active' => true]);
    $categoryB = Category::create(['company_id' => $companyB->id, 'name' => 'Category B']);
    $priceLevelB = PriceLevel::create(['company_id' => $companyB->id, 'name' => 'Wholesale B']);
    $supplierB = Supplier::create(['company_id' => $companyB->id, 'name' => 'Supplier B', 'is_active' => true]);
    $expenseCategoryB = ExpenseCategory::create(['company_id' => $companyB->id, 'name' => 'Expense Category B']);

    $productA = Product::create([
        'company_id' => $companyA->id,
        'name' => 'Product A',
        'type' => ProductType::PRODUCT,
        'cost_price' => 5,
        'sell_price' => 10,
        'tax_rate' => 0,
        'track_stock' => false,
        'quantity_on_hand' => 0,
        'low_stock_threshold' => 0,
        'is_active' => true,
    ]);

    $productB = Product::create([
        'company_id' => $companyB->id,
        'name' => 'Product B',
        'type' => ProductType::PRODUCT,
        'cost_price' => 7,
        'sell_price' => 14,
        'tax_rate' => 0,
        'track_stock' => false,
        'quantity_on_hand' => 0,
        'low_stock_threshold' => 0,
        'is_active' => true,
    ]);

    $salesOrderA = SalesOrder::create([
        'company_id' => $companyA->id,
        'customer_id' => $customerA->id,
        'number' => 'SO-A-001',
        'date' => now()->format('Y-m-d'),
        'status' => SalesOrderStatus::CONFIRMED,
        'subtotal' => 10,
        'discount' => 0,
        'tax' => 0,
        'total' => 10,
    ]);
    $salesOrderItemA = $salesOrderA->items()->create([
        'company_id' => $companyA->id,
        'product_id' => $productA->id,
        'description' => 'Product A',
        'qty' => 5,
        'unit_price' => 10,
        'discount' => 0,
        'tax_rate' => 0,
        'tax' => 0,
        'total' => 50,
        'delivered_qty' => 2,
    ]);

    $otherSalesOrderA = SalesOrder::create([
        'company_id' => $companyA->id,
        'customer_id' => $customerA->id,
        'number' => 'SO-A-002',
        'date' => now()->format('Y-m-d'),
        'status' => SalesOrderStatus::CONFIRMED,
        'subtotal' => 10,
        'discount' => 0,
        'tax' => 0,
        'total' => 10,
    ]);
    $otherSalesOrderItemA = $otherSalesOrderA->items()->create([
        'company_id' => $companyA->id,
        'product_id' => $productA->id,
        'description' => 'Other Product A',
        'qty' => 5,
        'unit_price' => 10,
        'discount' => 0,
        'tax_rate' => 0,
        'tax' => 0,
        'total' => 50,
        'delivered_qty' => 0,
    ]);

    $salesOrderB = SalesOrder::create([
        'company_id' => $companyB->id,
        'customer_id' => $customerB->id,
        'number' => 'SO-B-001',
        'date' => now()->format('Y-m-d'),
        'status' => SalesOrderStatus::CONFIRMED,
        'subtotal' => 14,
        'discount' => 0,
        'tax' => 0,
        'total' => 14,
    ]);
    $salesOrderItemB = $salesOrderB->items()->create([
        'company_id' => $companyB->id,
        'product_id' => $productB->id,
        'description' => 'Product B',
        'qty' => 5,
        'unit_price' => 14,
        'discount' => 0,
        'tax_rate' => 0,
        'tax' => 0,
        'total' => 70,
        'delivered_qty' => 0,
    ]);

    $invoiceA = Invoice::create([
        'company_id' => $companyA->id,
        'customer_id' => $customerA->id,
        'sales_order_id' => $salesOrderA->id,
        'number' => 'INV-A-001',
        'date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => InvoiceStatus::DRAFT,
        'subtotal' => 10,
        'discount' => 0,
        'tax' => 0,
        'total' => 10,
        'amount_paid' => 0,
    ]);

    return compact(
        'adminA',
        'adminB',
        'customerA',
        'customerB',
        'categoryB',
        'priceLevelB',
        'supplierB',
        'expenseCategoryB',
        'productA',
        'productB',
        'salesOrderA',
        'salesOrderItemA',
        'otherSalesOrderItemA',
        'salesOrderItemB',
        'invoiceA'
    );
}

it('rejects cross tenant ids in sales document forms', function () {
    $fixture = tenantSecurityFixture();
    $this->actingAs($fixture['adminA']);

    Livewire::test(QuotationForm::class)
        ->set('customerId', $fixture['customerB']->id)
        ->set('items.0.product_id', $fixture['productB']->id)
        ->set('items.0.description', 'Tampered')
        ->call('save')
        ->assertHasErrors(['customerId', 'items.0.product_id']);

    Livewire::test(SalesOrderForm::class)
        ->set('customerId', $fixture['customerB']->id)
        ->set('items.0.product_id', $fixture['productB']->id)
        ->set('items.0.description', 'Tampered')
        ->call('save')
        ->assertHasErrors(['customerId', 'items.0.product_id']);

    Livewire::test(InvoiceForm::class)
        ->set('customerId', $fixture['customerB']->id)
        ->set('items.0.product_id', $fixture['productB']->id)
        ->set('items.0.description', 'Tampered')
        ->call('save')
        ->assertHasErrors(['customerId', 'items.0.product_id']);
});

it('rejects cross tenant ids in master and purchasing forms', function () {
    $fixture = tenantSecurityFixture();
    $this->actingAs($fixture['adminA']);

    Livewire::test(ProductForm::class)
        ->set('name', 'Product With Bad Category')
        ->set('type', ProductType::PRODUCT->value)
        ->set('categoryId', $fixture['categoryB']->id)
        ->call('save')
        ->assertHasErrors(['categoryId']);

    Livewire::test(CustomerForm::class)
        ->set('name', 'Customer With Bad Price Level')
        ->set('priceLevelId', $fixture['priceLevelB']->id)
        ->call('save')
        ->assertHasErrors(['priceLevelId']);

    Livewire::test(PurchaseOrderForm::class)
        ->set('supplierId', $fixture['supplierB']->id)
        ->set('items.0.product_id', $fixture['productB']->id)
        ->set('items.0.description', 'Tampered')
        ->call('save')
        ->assertHasErrors(['supplierId', 'items.0.product_id']);

    Livewire::test(ExpenseForm::class)
        ->set('expenseCategoryId', $fixture['expenseCategoryB']->id)
        ->set('supplierId', $fixture['supplierB']->id)
        ->set('description', 'Tampered expense')
        ->set('amount', '10')
        ->call('save')
        ->assertHasErrors(['expenseCategoryId', 'supplierId']);
});

it('rejects cross tenant ids in admin pricing forms', function () {
    $fixture = tenantSecurityFixture();
    $this->actingAs($fixture['adminA']);

    Livewire::test(Discounts::class)
        ->set('name', 'Bad Product Discount')
        ->set('type', DiscountType::PERCENT->value)
        ->set('value', '10')
        ->set('appliesTo', 'product')
        ->set('productId', $fixture['productB']->id)
        ->call('save')
        ->assertHasErrors(['productId']);

    Livewire::test(PriceLevels::class)
        ->set("newProductId.{$fixture['priceLevelB']->id}", $fixture['productB']->id)
        ->set("newPrice.{$fixture['priceLevelB']->id}", '10')
        ->call('addProductPrice', $fixture['priceLevelB']->id)
        ->assertHasErrors(["newProductId.{$fixture['priceLevelB']->id}"]);

    Livewire::test(PriceLevels::class)
        ->set("newProductId.{$fixture['priceLevelB']->id}", $fixture['productA']->id)
        ->set("newPrice.{$fixture['priceLevelB']->id}", '10')
        ->call('addProductPrice', $fixture['priceLevelB']->id)
        ->assertHasErrors(["newProductId.{$fixture['priceLevelB']->id}"]);
});

it('rejects delivery order item tampering', function () {
    $fixture = tenantSecurityFixture();
    $this->actingAs($fixture['adminA']);

    Livewire::test(DeliveryOrderForm::class, ['salesOrder' => $fixture['salesOrderA']])
        ->set('items.0.sales_order_item_id', $fixture['salesOrderItemB']->id)
        ->call('save')
        ->assertHasErrors(['items.0.sales_order_item_id']);

    Livewire::test(DeliveryOrderForm::class, ['salesOrder' => $fixture['salesOrderA']])
        ->set('items.0.sales_order_item_id', $fixture['otherSalesOrderItemA']->id)
        ->call('save')
        ->assertHasErrors(['items.0.sales_order_item_id']);

    Livewire::test(DeliveryOrderForm::class, ['salesOrder' => $fixture['salesOrderA']])
        ->set('items.0.qty', 4)
        ->set('items.0.max_qty', 999)
        ->call('save')
        ->assertHasErrors(['items.0.qty']);

    Livewire::test(DeliveryOrderForm::class, ['salesOrder' => $fixture['salesOrderA']])
        ->set('items.0.product_id', $fixture['productB']->id)
        ->call('save')
        ->assertHasErrors(['items.0.product_id']);
});
