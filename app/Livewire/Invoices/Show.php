<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Livewire\Traits\HasWhatsappShare;
use App\Models\Invoice;
use App\Services\MyInvois\EInvoiceService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    use HasWhatsappShare;

    public Invoice $invoice;

    public string $cancelReason = '';

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['customer', 'salesOrder', 'items.product', 'payments.receipt', 'eInvoice']);
    }

    public function send(): void
    {
        $this->invoice->update(['status' => InvoiceStatus::SENT]);
        $this->invoice->refresh();
        session()->flash('success', 'Invoice marked as sent.');
    }

    public function markVoid(): void
    {
        $this->invoice->update(['status' => InvoiceStatus::VOID]);
        $this->invoice->refresh();
        session()->flash('success', 'Invoice marked as void.');
    }

    public function submitEInvoice(EInvoiceService $service): void
    {
        try {
            $service->submit($this->invoice);
            session()->flash('success', 'Invoice submitted to MyInvois.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->invoice->load('eInvoice');
    }

    public function refreshEInvoiceStatus(EInvoiceService $service): void
    {
        if ($this->invoice->eInvoice) {
            try {
                $service->refreshStatus($this->invoice->eInvoice);
                session()->flash('success', 'e-Invoice status updated.');
            } catch (\Throwable $e) {
                session()->flash('error', $e->getMessage());
            }

            $this->invoice->load('eInvoice');
        }
    }

    public function cancelEInvoice(EInvoiceService $service): void
    {
        if (trim($this->cancelReason) === '') {
            session()->flash('error', 'A cancellation reason is required.');

            return;
        }

        try {
            $service->cancel($this->invoice->eInvoice, trim($this->cancelReason));
            $this->cancelReason = '';
            session()->flash('success', 'e-Invoice cancelled.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->invoice->load('eInvoice');
    }

    public function render()
    {
        return view('livewire.invoices.show', [
            'amountDue' => $this->invoice->amountDue(),
        ]);
    }
}
