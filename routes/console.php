<?php

use App\Enums\EInvoiceStatus;
use App\Enums\InvoiceStatus;
use App\Models\EInvoiceSubmission;
use App\Models\Invoice;
use App\Services\MyInvois\EInvoiceService;
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

Artisan::command('einvoice:poll-status', function (EInvoiceService $service) {
    if (! config('myinvois.enabled')) {
        $this->info('MyInvois is disabled; skipping.');

        return;
    }

    $pending = EInvoiceSubmission::withoutGlobalScopes()
        ->where('status', EInvoiceStatus::SUBMITTED)
        ->whereNotNull('uuid')
        ->get();

    foreach ($pending as $submission) {
        try {
            $service->refreshStatus($submission);
        } catch (Throwable $e) {
            $this->error("Submission #{$submission->id}: {$e->getMessage()}");
        }
    }

    $this->info("Polled {$pending->count()} pending e-Invoice submission(s).");
})->purpose('Poll MyInvois for pending e-Invoice validation status');

Schedule::command('einvoice:poll-status')->everyFifteenMinutes();
