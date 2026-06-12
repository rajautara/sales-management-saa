<?php

namespace App\Livewire\Invoices;

use App\Enums\InvoiceStatus;
use App\Livewire\Traits\HasDocumentItems;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\DocumentNumberService;
use App\Services\PricingService;
use App\Support\TenantRule;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Form extends Component
{
    use HasDocumentItems;

    public ?int $invoiceId = null;
    public ?int $salesOrderId = null;

    public ?int $customerId = null;
    public string $date = '';
    public string $dueDate = '';
    public string $status = '';
    public string $notes = '';

    public function mount(Invoice $invoice = null, SalesOrder $salesOrder = null): void
    {
        if ($invoice && $invoice->exists) {
            $this->invoiceId = $invoice->id;
            $this->salesOrderId = $invoice->sales_order_id;
            $this->customerId = $invoice->customer_id;
            $this->date = $invoice->date->format('Y-m-d');
            $this->dueDate = $invoice->due_date->format('Y-m-d');
            $this->status = $invoice->status->value;
            $this->notes = $invoice->notes ?? '';
            $this->fillItemsFromModel($invoice->items);
        } elseif ($salesOrder && $salesOrder->exists) {
            $this->salesOrderId = $salesOrder->id;
            $this->customerId = $salesOrder->customer_id;
            $this->date = now()->format('Y-m-d');
            $this->dueDate = now()->addDays(30)->format('Y-m-d');
            $this->status = InvoiceStatus::DRAFT->value;
            $this->notes = $salesOrder->notes ?? '';
            $this->fillItemsFromModel($salesOrder->items);
        } else {
            $this->date = now()->format('Y-m-d');
            $this->dueDate = now()->addDays(30)->format('Y-m-d');
            $this->status = InvoiceStatus::DRAFT->value;
            $this->addItem();
        }
    }

    protected function rules(): array
    {
        return array_merge($this->documentItemRules(), [
            'customerId' => ['required', TenantRule::exists('customers')],
            'date' => ['required', 'date'],
            'dueDate' => ['required', 'date', 'after_or_equal:date'],
            'status' => ['required', 'in:' . implode(',', array_column(InvoiceStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string'],
        ]);
    }

    public function save(): void
    {
        $validated = $this->validate();
        $customer = Customer::findOrFail($validated['customerId']);
        $salesOrderId = $this->salesOrderId ? SalesOrder::findOrFail($this->salesOrderId)->id : null;
        $items = $this->validatedDocumentItems();
        $totals = $this->totals;

        DB::transaction(function () use ($validated, $customer, $salesOrderId, $items, $totals) {
            $data = [
                'customer_id' => $customer->id,
                'sales_order_id' => $salesOrderId,
                'date' => $validated['date'],
                'due_date' => $validated['dueDate'],
                'status' => InvoiceStatus::from($validated['status']),
                'notes' => $validated['notes'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
            ];

            if (! $this->invoiceId) {
                $data['amount_paid'] = 0;
            }

            if ($this->invoiceId) {
                $invoice = Invoice::findOrFail($this->invoiceId);
                $invoice->update($data);
            } else {
                $data['number'] = DocumentNumberService::next(DocumentNumberService::TYPE_INVOICE);
                $invoice = Invoice::create($data);
            }

            $invoice->items()->delete();

            foreach ($items as $item) {
                $line = PricingService::calculateLineTotal(
                    (float) $item['qty'],
                    (float) $item['unit_price'],
                    (float) ($item['discount'] ?? 0),
                    (float) ($item['tax_rate'] ?? 0)
                );

                $invoice->items()->create([
                    'product_id' => $item['product_id'] ?: null,
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax' => $line['tax'],
                    'total' => $line['total'],
                ]);
            }
        });

        session()->flash('success', $this->invoiceId ? 'Invoice updated successfully.' : 'Invoice created successfully.');
        $this->redirectRoute('invoices.index');
    }

    public function render()
    {
        return view('livewire.invoices.form', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'statuses' => InvoiceStatus::cases(),
        ]);
    }
}
