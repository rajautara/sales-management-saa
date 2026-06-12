<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\Receipt;

class PublicDocumentController extends Controller
{
    public function quotation(Quotation $quotation)
    {
        $quotation->load(['company', 'customer', 'items.product']);
        return view('public.quotations.show', compact('quotation'));
    }

    public function salesOrder(SalesOrder $salesOrder)
    {
        $salesOrder->load(['company', 'customer', 'items.product']);
        return view('public.sales-orders.show', compact('salesOrder'));
    }

    public function deliveryOrder(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load(['company', 'salesOrder.customer', 'items.product', 'items.salesOrderItem']);
        return view('public.delivery-orders.show', compact('deliveryOrder'));
    }

    public function invoice(Invoice $invoice)
    {
        $invoice->load(['company', 'customer', 'items.product', 'payments']);
        return view('public.invoices.show', compact('invoice'));
    }

    public function receipt(Receipt $receipt)
    {
        $receipt->load(['company', 'payment.invoice.customer', 'payment.invoice.items.product']);
        return view('public.receipts.show', compact('receipt'));
    }
}
