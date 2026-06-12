<?php

namespace App\Livewire\SalesOrders;

use App\Enums\SalesOrderStatus;
use App\Livewire\Traits\HasDocumentItems;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
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

    public ?int $salesOrderId = null;
    public ?int $quotationId = null;

    public ?int $customerId = null;
    public string $date = '';
    public string $status = '';
    public string $notes = '';

    public function mount(SalesOrder $salesOrder = null, Quotation $quotation = null): void
    {
        if ($salesOrder && $salesOrder->exists) {
            $this->salesOrderId = $salesOrder->id;
            $this->quotationId = $salesOrder->quotation_id;
            $this->customerId = $salesOrder->customer_id;
            $this->date = $salesOrder->date->format('Y-m-d');
            $this->status = $salesOrder->status->value;
            $this->notes = $salesOrder->notes ?? '';
            $this->fillItemsFromModel($salesOrder->items);
        } elseif ($quotation && $quotation->exists) {
            $this->quotationId = $quotation->id;
            $this->customerId = $quotation->customer_id;
            $this->date = now()->format('Y-m-d');
            $this->status = SalesOrderStatus::DRAFT->value;
            $this->notes = $quotation->notes ?? '';
            $this->fillItemsFromModel($quotation->items);
        } else {
            $this->date = now()->format('Y-m-d');
            $this->status = SalesOrderStatus::DRAFT->value;
            $this->addItem();
        }
    }

    protected function rules(): array
    {
        return array_merge($this->documentItemRules(), [
            'customerId' => ['required', TenantRule::exists('customers')],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:' . implode(',', array_column(SalesOrderStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string'],
        ]);
    }

    public function save(): void
    {
        $validated = $this->validate();
        $customer = Customer::findOrFail($validated['customerId']);
        $quotationId = $this->quotationId ? Quotation::findOrFail($this->quotationId)->id : null;
        $items = $this->validatedDocumentItems();
        $totals = $this->totals;

        DB::transaction(function () use ($validated, $customer, $quotationId, $items, $totals) {
            $data = [
                'customer_id' => $customer->id,
                'quotation_id' => $quotationId,
                'date' => $validated['date'],
                'status' => SalesOrderStatus::from($validated['status']),
                'notes' => $validated['notes'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
            ];

            if ($this->salesOrderId) {
                $salesOrder = SalesOrder::findOrFail($this->salesOrderId);
                $salesOrder->update($data);
            } else {
                $data['number'] = DocumentNumberService::next(DocumentNumberService::TYPE_SALES_ORDER);
                $salesOrder = SalesOrder::create($data);
            }

            $salesOrder->items()->delete();

            foreach ($items as $item) {
                $line = PricingService::calculateLineTotal(
                    (float) $item['qty'],
                    (float) $item['unit_price'],
                    (float) ($item['discount'] ?? 0),
                    (float) ($item['tax_rate'] ?? 0)
                );

                $salesOrder->items()->create([
                    'product_id' => $item['product_id'] ?: null,
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax' => $line['tax'],
                    'total' => $line['total'],
                    'delivered_qty' => 0,
                ]);
            }
        });

        session()->flash('success', $this->salesOrderId ? 'Sales order updated successfully.' : 'Sales order created successfully.');
        $this->redirectRoute('sales-orders.index');
    }

    public function render()
    {
        return view('livewire.sales-orders.form', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'statuses' => SalesOrderStatus::cases(),
        ]);
    }
}
