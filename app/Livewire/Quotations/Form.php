<?php

namespace App\Livewire\Quotations;

use App\Enums\QuotationStatus;
use App\Livewire\Traits\HasDocumentItems;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
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

    public ?int $quotationId = null;

    public ?int $customerId = null;
    public string $date = '';
    public string $validUntil = '';
    public string $notes = '';

    public function mount(Quotation $quotation = null): void
    {
        if ($quotation && $quotation->exists) {
            $this->quotationId = $quotation->id;
            $this->customerId = $quotation->customer_id;
            $this->date = $quotation->date->format('Y-m-d');
            $this->validUntil = $quotation->valid_until?->format('Y-m-d');
            $this->notes = $quotation->notes ?? '';
            $this->fillItemsFromModel($quotation->items);
        } else {
            $this->date = now()->format('Y-m-d');
            $this->validUntil = now()->addDays(7)->format('Y-m-d');
            $this->addItem();
        }
    }

    protected function rules(): array
    {
        return array_merge($this->documentItemRules(), [
            'customerId' => ['required', TenantRule::exists('customers')],
            'date' => ['required', 'date'],
            'validUntil' => ['nullable', 'date', 'after_or_equal:date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    public function save(): void
    {
        $validated = $this->validate();
        $customer = Customer::findOrFail($validated['customerId']);
        $items = $this->validatedDocumentItems();
        $totals = $this->totals;

        DB::transaction(function () use ($validated, $customer, $items, $totals) {
            $data = [
                'customer_id' => $customer->id,
                'date' => $validated['date'],
                'valid_until' => $validated['validUntil'] ?: null,
                'notes' => $validated['notes'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'tax' => $totals['tax'],
                'total' => $totals['total'],
            ];

            if ($this->quotationId) {
                $quotation = Quotation::findOrFail($this->quotationId);
                $quotation->update($data);
            } else {
                $data['number'] = DocumentNumberService::next(DocumentNumberService::TYPE_QUOTATION);
                $data['status'] = QuotationStatus::DRAFT;
                $quotation = Quotation::create($data);
            }

            $quotation->items()->delete();

            foreach ($items as $item) {
                $line = PricingService::calculateLineTotal(
                    (float) $item['qty'],
                    (float) $item['unit_price'],
                    (float) ($item['discount'] ?? 0),
                    (float) ($item['tax_rate'] ?? 0)
                );

                $quotation->items()->create([
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

        session()->flash('success', $this->quotationId ? 'Quotation updated successfully.' : 'Quotation created successfully.');
        $this->redirectRoute('quotations.index');
    }

    public function render()
    {
        return view('livewire.quotations.form', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }
}
