<?php

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('invoices:check-overdue', function () {
    $updated = Invoice::withoutGlobalScopes()
        ->where('due_date', '<', now()->startOfDay())
        ->whereNotIn('status', [InvoiceStatus::PAID, InvoiceStatus::VOID, InvoiceStatus::OVERDUE])
        ->update(['status' => InvoiceStatus::OVERDUE]);

    $this->info("Successfully marked {$updated} invoices as OVERDUE.");
})->purpose('Check and mark overdue invoices');

Schedule::command('invoices:check-overdue')->daily();
