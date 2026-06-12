<?php

namespace App\Livewire\Quotations;

use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Services\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

use App\Livewire\Traits\HasWhatsappShare;

#[Layout('layouts.app')]
class Show extends Component
{
    use HasWhatsappShare;

    public Quotation $quotation;

    public function mount(Quotation $quotation): void
    {
        $this->quotation = $quotation->load(['customer', 'items.product']);
    }

    public function send(): void
    {
        $this->quotation->update(['status' => QuotationStatus::SENT]);
        $this->quotation->refresh();
        session()->flash('success', 'Quotation marked as sent.');
    }

    public function accept(): void
    {
        $this->quotation->update(['status' => QuotationStatus::ACCEPTED]);
        $this->quotation->refresh();
        session()->flash('success', 'Quotation accepted.');
    }

    public function reject(): void
    {
        $this->quotation->update(['status' => QuotationStatus::REJECTED]);
        $this->quotation->refresh();
        session()->flash('success', 'Quotation rejected.');
    }

    public function markExpired(): void
    {
        $this->quotation->update(['status' => QuotationStatus::EXPIRED]);
        $this->quotation->refresh();
        session()->flash('success', 'Quotation marked as expired.');
    }

    public function convertToSalesOrder(): void
    {
        $salesOrder = DB::transaction(function () {
            $quotation = $this->quotation;

            $salesOrder = SalesOrder::create([
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'number' => DocumentNumberService::next(DocumentNumberService::TYPE_SALES_ORDER),
                'date' => now()->format('Y-m-d'),
                'status' => SalesOrderStatus::DRAFT,
                'notes' => $quotation->notes,
                'subtotal' => $quotation->subtotal,
                'discount' => $quotation->discount,
                'tax' => $quotation->tax,
                'total' => $quotation->total,
            ]);

            foreach ($quotation->items as $item) {
                $salesOrder->items()->create([
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'qty' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax_rate' => $item->tax_rate,
                    'tax' => $item->tax,
                    'total' => $item->total,
                    'delivered_qty' => 0,
                ]);
            }

            $quotation->update(['status' => QuotationStatus::ACCEPTED]);

            return $salesOrder;
        });

        session()->flash('success', 'Quotation converted to sales order ' . $salesOrder->number . '.');
        $this->redirectRoute('sales-orders.show', $salesOrder);
    }

    public function render()
    {
        return view('livewire.quotations.show');
    }
}
