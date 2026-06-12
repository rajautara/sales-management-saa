<?php

namespace App\Livewire\Payments;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

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
        $this->invoice = $invoice;
        $this->date = now()->format('Y-m-d');
        $this->amount = number_format($invoice->amountDue(), 2, '.', '');
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
        $validated = $this->validate();

        if ((float) $validated['amount'] > $this->invoice->amountDue()) {
            $this->addError('amount', 'Payment amount cannot exceed the amount due.');
            return;
        }

        $payment = DB::transaction(function () use ($validated) {
            $payment = Payment::create([
                'invoice_id' => $this->invoice->id,
                'date' => $validated['date'],
                'amount' => $validated['amount'],
                'method' => PaymentMethod::from($validated['method']),
                'reference_no' => $validated['referenceNo'] ?: null,
                'notes' => $validated['notes'],
            ]);

            $this->invoice->recalculateStatus();

            Receipt::create([
                'payment_id' => $payment->id,
                'number' => DocumentNumberService::next(DocumentNumberService::TYPE_RECEIPT),
                'date' => $validated['date'],
            ]);

            return $payment;
        });

        session()->flash('success', 'Payment recorded and receipt generated.');
        $this->redirectRoute('receipts.show', $payment->receipt);
    }

    public function render()
    {
        return view('livewire.payments.form', [
            'methods' => PaymentMethod::cases(),
            'amountDue' => $this->invoice->amountDue(),
        ]);
    }
}
