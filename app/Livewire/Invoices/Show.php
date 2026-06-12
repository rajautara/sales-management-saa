<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\Attributes\Layout;

use App\Livewire\Traits\HasWhatsappShare;

#[Layout('layouts.app')]
class Show extends Component
{
    use HasWhatsappShare;

    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['customer', 'salesOrder', 'items.product', 'payments.receipt']);
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

    public function render()
    {
        return view('livewire.invoices.show', [
            'amountDue' => $this->invoice->amountDue(),
        ]);
    }
}
