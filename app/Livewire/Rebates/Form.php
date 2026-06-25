<?php

namespace App\Livewire\Rebates;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Rebate;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public Invoice $invoice;

    public string $date = '';
    public string $amount = '';
    public string $method = '';
    public string $referenceNo = '';
    public string $notes = '';

    public function mount(Invoice $invoice): void
    {
        abort_unless($invoice->status === InvoiceStatus::PAID, 403, 'Rebate is only allowed on fully paid invoices.');

        $this->invoice = $invoice;
        $this->date = now()->format('Y-m-d');
        $this->method = PaymentMethod::CASH->value;
    }

    protected function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:' . implode(',', array_column(PaymentMethod::cases(), 'value'))],
            'referenceNo' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        abort_unless($this->invoice->status === InvoiceStatus::PAID, 403);

        $validated = $this->validate();

        $cap = (float) $this->invoice->amount_paid - $this->invoice->totalRebated();

        if ((float) $validated['amount'] > $cap) {
            $this->addError('amount', 'Rebate cannot exceed the remaining paid amount (RM ' . number_format(max($cap, 0), 2) . ').');

            return;
        }

        DB::transaction(function () use ($validated) {
            Rebate::create([
                'invoice_id' => $this->invoice->id,
                'customer_id' => $this->invoice->customer_id,
                'date' => $validated['date'],
                'amount' => $validated['amount'],
                'method' => PaymentMethod::from($validated['method']),
                'reference_no' => $validated['referenceNo'] ?: null,
                'notes' => $validated['notes'],
            ]);
            // Note: deliberately not calling recalculateStatus() — a rebate is a
            // separate cash outflow and must not change the invoice's paid status.
        });

        session()->flash('success', 'Rebate recorded.');
        $this->redirectRoute('invoices.show', $this->invoice);
    }

    public function render()
    {
        return view('livewire.rebates.form', [
            'methods' => PaymentMethod::cases(),
            'maxRebate' => max((float) $this->invoice->amount_paid - $this->invoice->totalRebated(), 0),
        ]);
    }
}
