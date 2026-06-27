<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Receipt;
use App\Models\SalesOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentPdfController extends Controller
{
    private function checkCompany($model): void
    {
        if ($model->company_id !== auth()->user()?->company_id) {
            abort(403);
        }
    }

    /**
     * Public PDF routes are unauthenticated (guarded only by the signed URL).
     * As defence in depth, refuse to render documents for disabled companies.
     */
    private function ensureCompanyActive($model): void
    {
        abort_unless($model->company?->is_active, 404);
    }

    private function getBase64Logo($company): ?string
    {
        if ($company && $company->logo_path) {
            $path = storage_path('app/public/'.$company->logo_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);

                return 'data:image/'.$type.';base64,'.base64_encode($data);
            }
        }

        return null;
    }

    // --- Quotation ---
    public function quotation(Quotation $quotation)
    {
        $this->checkCompany($quotation);

        return $this->generateQuotationPdf($quotation);
    }

    public function quotationPublic(Quotation $quotation)
    {
        $this->ensureCompanyActive($quotation);

        return $this->generateQuotationPdf($quotation);
    }

    private function generateQuotationPdf(Quotation $quotation)
    {
        $quotation->load(['company', 'customer', 'items.product']);
        $logo = $this->getBase64Logo($quotation->company);

        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'logo' => $logo,
        ]);

        return $pdf->download('Quotation-'.$quotation->number.'.pdf');
    }

    // --- Sales Order ---
    public function salesOrder(SalesOrder $salesOrder)
    {
        $this->checkCompany($salesOrder);

        return $this->generateSalesOrderPdf($salesOrder);
    }

    public function salesOrderPublic(SalesOrder $salesOrder)
    {
        $this->ensureCompanyActive($salesOrder);

        return $this->generateSalesOrderPdf($salesOrder);
    }

    private function generateSalesOrderPdf(SalesOrder $salesOrder)
    {
        $salesOrder->load(['company', 'customer', 'items.product']);
        $logo = $this->getBase64Logo($salesOrder->company);

        $pdf = Pdf::loadView('pdf.sales-order', [
            'salesOrder' => $salesOrder,
            'logo' => $logo,
        ]);

        return $pdf->download('SalesOrder-'.$salesOrder->number.'.pdf');
    }

    // --- Delivery Order ---
    public function deliveryOrder(DeliveryOrder $deliveryOrder)
    {
        $this->checkCompany($deliveryOrder);

        return $this->generateDeliveryOrderPdf($deliveryOrder);
    }

    public function deliveryOrderPublic(DeliveryOrder $deliveryOrder)
    {
        $this->ensureCompanyActive($deliveryOrder);

        return $this->generateDeliveryOrderPdf($deliveryOrder);
    }

    private function generateDeliveryOrderPdf(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load(['company', 'salesOrder.customer', 'items.product']);
        $logo = $this->getBase64Logo($deliveryOrder->company);

        $pdf = Pdf::loadView('pdf.delivery-order', [
            'deliveryOrder' => $deliveryOrder,
            'logo' => $logo,
        ]);

        return $pdf->download('DeliveryOrder-'.$deliveryOrder->number.'.pdf');
    }

    // --- Invoice ---
    public function invoice(Invoice $invoice)
    {
        $this->checkCompany($invoice);

        return $this->generateInvoicePdf($invoice);
    }

    public function invoicePublic(Invoice $invoice)
    {
        $this->ensureCompanyActive($invoice);

        return $this->generateInvoicePdf($invoice);
    }

    private function generateInvoicePdf(Invoice $invoice)
    {
        $invoice->load(['company', 'customer', 'items.product', 'payments', 'eInvoice']);
        $logo = $this->getBase64Logo($invoice->company);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'amountDue' => $invoice->amountDue(),
            'logo' => $logo,
            'einvoice' => $invoice->eInvoice,
            'einvoiceQr' => $this->eInvoiceQr($invoice),
        ]);

        return $pdf->download('Invoice-'.$invoice->number.'.pdf');
    }

    private function eInvoiceQr(Invoice $invoice): ?string
    {
        $ei = $invoice->eInvoice;

        if (! $ei || ! $ei->isValid() || blank($ei->validation_link)) {
            return null;
        }

        $svg = QrCode::format('svg')
            ->size(120)
            ->margin(0)
            ->generate($ei->validation_link);

        return 'data:image/svg+xml;base64,'.base64_encode((string) $svg);
    }

    // --- Receipt ---
    public function receipt(Receipt $receipt)
    {
        $this->checkCompany($receipt);

        return $this->generateReceiptPdf($receipt);
    }

    public function receiptPublic(Receipt $receipt)
    {
        $this->ensureCompanyActive($receipt);

        return $this->generateReceiptPdf($receipt);
    }

    private function generateReceiptPdf(Receipt $receipt)
    {
        $receipt->load(['company', 'payment.invoice.customer', 'payment.invoice.items.product']);
        $logo = $this->getBase64Logo($receipt->company);

        $pdf = Pdf::loadView('pdf.receipt', [
            'receipt' => $receipt,
            'logo' => $logo,
        ]);

        return $pdf->download('Receipt-'.$receipt->number.'.pdf');
    }
}
