<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\Receipt;

class PublicDocumentController extends Controller
{
    /**
     * Public routes are unauthenticated, so the BelongsToCompany global scope
     * adds no filter — the signed URL is the access control. As defence in
     * depth, stop resolving documents for companies that are disabled.
     */
    private function ensureCompanyActive($model): void
    {
        abort_unless($model->company?->is_active, 404);
    }

    public function quotation(Quotation $quotation)
    {
        $this->ensureCompanyActive($quotation);
        $quotation->load(['company', 'customer', 'items.product']);
        return view('public.quotations.show', compact('quotation'));
    }

    public function salesOrder(SalesOrder $salesOrder)
    {
        $this->ensureCompanyActive($salesOrder);
        $salesOrder->load(['company', 'customer', 'items.product']);
        return view('public.sales-orders.show', compact('salesOrder'));
    }

    public function deliveryOrder(DeliveryOrder $deliveryOrder)
    {
        $this->ensureCompanyActive($deliveryOrder);
        $deliveryOrder->load(['company', 'salesOrder.customer', 'items.product', 'items.salesOrderItem']);
        return view('public.delivery-orders.show', compact('deliveryOrder'));
    }

    public function invoice(Invoice $invoice)
    {
        $this->ensureCompanyActive($invoice);
        $invoice->load(['company', 'customer', 'items.product', 'payments']);
        return view('public.invoices.show', compact('invoice'));
    }

    public function receipt(Receipt $receipt)
    {
        $this->ensureCompanyActive($receipt);
        $receipt->load(['company', 'payment.invoice.customer', 'payment.invoice.items.product']);
        return view('public.receipts.show', compact('receipt'));
    }
}
