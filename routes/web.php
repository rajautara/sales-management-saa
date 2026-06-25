<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'company.active'])->group(function () {
    Route::get('dashboard', App\Livewire\Dashboard\Index::class)
        ->middleware(['verified'])
        ->name('dashboard');

    Route::view('profile', 'profile')->name('profile');


    // Master Data
    Route::get('customers', App\Livewire\Customers\Index::class)->name('customers.index');
    Route::get('customers/create', App\Livewire\Customers\Form::class)->name('customers.create');
    Route::get('customers/{customer}/edit', App\Livewire\Customers\Form::class)->name('customers.edit');

    Route::get('products', App\Livewire\Products\Index::class)->name('products.index');
    Route::get('products/create', App\Livewire\Products\Form::class)->name('products.create');
    Route::get('products/{product}/edit', App\Livewire\Products\Form::class)->name('products.edit');
    Route::get('categories', App\Livewire\Products\Categories::class)->name('categories.index');

    Route::get('suppliers', App\Livewire\Suppliers\Index::class)->name('suppliers.index');
    Route::get('suppliers/create', App\Livewire\Suppliers\Form::class)->name('suppliers.create');
    Route::get('suppliers/{supplier}/edit', App\Livewire\Suppliers\Form::class)->name('suppliers.edit');


    // Sales Flow
    Route::get('quotations', App\Livewire\Quotations\Index::class)->name('quotations.index');
    Route::get('quotations/create', App\Livewire\Quotations\Form::class)->name('quotations.create');
    Route::get('quotations/{quotation}/edit', App\Livewire\Quotations\Form::class)->name('quotations.edit');
    Route::get('quotations/{quotation}', App\Livewire\Quotations\Show::class)->name('quotations.show');

    Route::get('sales-orders', App\Livewire\SalesOrders\Index::class)->name('sales-orders.index');
    Route::get('sales-orders/create/{quotation?}', App\Livewire\SalesOrders\Form::class)->name('sales-orders.create');
    Route::get('sales-orders/{salesOrder}/edit', App\Livewire\SalesOrders\Form::class)->name('sales-orders.edit');
    Route::get('sales-orders/{salesOrder}', App\Livewire\SalesOrders\Show::class)->name('sales-orders.show');

    Route::get('delivery-orders', App\Livewire\DeliveryOrders\Index::class)->name('delivery-orders.index');
    Route::get('delivery-orders/create/{salesOrder?}', App\Livewire\DeliveryOrders\Form::class)->name('delivery-orders.create');
    Route::get('delivery-orders/{deliveryOrder}', App\Livewire\DeliveryOrders\Show::class)->name('delivery-orders.show');

    Route::get('invoices', App\Livewire\Invoices\Index::class)->name('invoices.index');
    Route::get('invoices/create/{salesOrder?}', App\Livewire\Invoices\Form::class)->name('invoices.create');
    Route::get('invoices/{invoice}/edit', App\Livewire\Invoices\Form::class)->name('invoices.edit');
    Route::get('invoices/{invoice}', App\Livewire\Invoices\Show::class)->name('invoices.show');

    Route::get('payments', App\Livewire\Payments\Index::class)->name('payments.index');
    Route::get('payments/create/{invoice}', App\Livewire\Payments\Form::class)->name('payments.create');

    Route::get('receipts', App\Livewire\Receipts\Index::class)->name('receipts.index');
    Route::get('receipts/{receipt}', App\Livewire\Receipts\Show::class)->name('receipts.show');

    Route::get('rebates', App\Livewire\Rebates\Index::class)->name('rebates.index');
    Route::get('rebates/create/{invoice}', App\Livewire\Rebates\Form::class)->name('rebates.create');

    // Inventory & Purchasing
    Route::get('purchase-orders', App\Livewire\PurchaseOrders\Index::class)->name('purchase-orders.index');
    Route::get('purchase-orders/create', App\Livewire\PurchaseOrders\Form::class)->name('purchase-orders.create');
    Route::get('purchase-orders/{purchaseOrder}', App\Livewire\PurchaseOrders\Show::class)->name('purchase-orders.show');

    Route::get('inventory', App\Livewire\Inventory\Index::class)->name('inventory.index');
    Route::get('inventory/{product}', App\Livewire\Inventory\Show::class)->name('inventory.show');

    Route::get('expenses', App\Livewire\Expenses\Index::class)->name('expenses.index');
    Route::get('expenses/create', App\Livewire\Expenses\Form::class)->name('expenses.create');
    Route::get('expenses/{expense}/edit', App\Livewire\Expenses\Form::class)->name('expenses.edit');
    Route::get('expense-categories', App\Livewire\Expenses\Categories::class)->name('expense-categories.index');

    // Document PDF Export Routes
    Route::get('quotations/{quotation}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'quotation'])->name('quotations.pdf');
    Route::get('sales-orders/{salesOrder}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'salesOrder'])->name('sales-orders.pdf');
    Route::get('delivery-orders/{deliveryOrder}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'deliveryOrder'])->name('delivery-orders.pdf');
    Route::get('invoices/{invoice}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'invoice'])->name('invoices.pdf');
    Route::get('receipts/{receipt}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'receipt'])->name('receipts.pdf');

    // Admin & Super-Admin Only Routes
    Route::middleware(['role:admin|super-admin'])->group(function () {
        Route::get('settings', App\Livewire\Settings\Index::class)->name('settings.index');
        Route::get('settings/users', App\Livewire\Settings\Users::class)->name('settings.users');
        Route::get('reports', App\Livewire\Reports\Index::class)->name('reports.index');
        Route::get('advisor', App\Livewire\Advisor\Index::class)->name('advisor.index');
        Route::get('price-levels', App\Livewire\Products\PriceLevels::class)->name('price-levels.index');
        Route::get('discounts', App\Livewire\Products\Discounts::class)->name('discounts.index');
    });
});

// Public Signed Routes (No Authentication Required)
Route::prefix('public')->group(function () {
    Route::get('quotations/{quotation}', [App\Http\Controllers\PublicDocumentController::class, 'quotation'])
        ->name('public.quotations.show')
        ->middleware('signed');

    Route::get('sales-orders/{salesOrder}', [App\Http\Controllers\PublicDocumentController::class, 'salesOrder'])
        ->name('public.sales-orders.show')
        ->middleware('signed');

    Route::get('delivery-orders/{deliveryOrder}', [App\Http\Controllers\PublicDocumentController::class, 'deliveryOrder'])
        ->name('public.delivery-orders.show')
        ->middleware('signed');

    Route::get('invoices/{invoice}', [App\Http\Controllers\PublicDocumentController::class, 'invoice'])
        ->name('public.invoices.show')
        ->middleware('signed');

    Route::get('receipts/{receipt}', [App\Http\Controllers\PublicDocumentController::class, 'receipt'])
        ->name('public.receipts.show')
        ->middleware('signed');

    Route::get('quotations/{quotation}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'quotationPublic'])
        ->name('public.quotations.pdf')
        ->middleware('signed');

    Route::get('sales-orders/{salesOrder}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'salesOrderPublic'])
        ->name('public.sales-orders.pdf')
        ->middleware('signed');

    Route::get('delivery-orders/{deliveryOrder}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'deliveryOrderPublic'])
        ->name('public.delivery-orders.pdf')
        ->middleware('signed');

    Route::get('invoices/{invoice}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'invoicePublic'])
        ->name('public.invoices.pdf')
        ->middleware('signed');

    Route::get('receipts/{receipt}/pdf', [App\Http\Controllers\DocumentPdfController::class, 'receiptPublic'])
        ->name('public.receipts.pdf')
        ->middleware('signed');
});

require __DIR__.'/auth.php';
