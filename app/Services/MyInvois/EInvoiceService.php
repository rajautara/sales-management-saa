<?php

namespace App\Services\MyInvois;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

/**
 * Orchestrates e-Invoice (MyInvois) operations for an Invoice.
 *
 * Phase 1 scope: pre-submission readiness validation and UBL document
 * building. API submission, signing and status polling are layered on top
 * in later phases via MyInvoisClient / DocumentSigner.
 */
class EInvoiceService
{
    public function __construct(
        protected InvoiceDocumentBuilder $builder = new InvoiceDocumentBuilder,
    ) {}

    /**
     * Validate that an invoice has the mandatory data MyInvois requires before
     * submission. Returns a list of human-readable error messages; an empty
     * array means the invoice is ready to submit.
     *
     * @return array<int, string>
     */
    public function validateReadiness(Invoice $invoice): array
    {
        $invoice->loadMissing(['company', 'customer', 'items']);

        $errors = [];
        $company = $invoice->company;
        $customer = $invoice->customer;

        if ($invoice->status === InvoiceStatus::VOID) {
            $errors[] = 'Invoice is void and cannot be submitted as an e-Invoice.';
        }

        // Supplier (company) mandatory fields.
        if (blank($company?->tin)) {
            $errors[] = 'Company TIN is required (set it in Settings).';
        }
        if (blank($company?->address_state_code)) {
            $errors[] = 'Company state code is required.';
        }
        if (blank($company?->address_city)) {
            $errors[] = 'Company city is required.';
        }
        if (blank($company?->address_postcode)) {
            $errors[] = 'Company postcode is required.';
        }

        // Buyer (customer) mandatory fields.
        if (blank($customer?->tin)) {
            $errors[] = 'Customer TIN is required.';
        }
        if (blank($customer?->registration_no) && blank($customer?->tin)) {
            $errors[] = 'Customer registration number (BRN/NRIC/Passport) is required.';
        }

        // Line items.
        if ($invoice->items->isEmpty()) {
            $errors[] = 'Invoice has no line items.';
        }
        foreach ($invoice->items as $index => $item) {
            if (blank($item->description)) {
                $errors[] = 'Line '.($index + 1).' is missing a description.';
            }
        }

        if ((float) $invoice->total <= 0) {
            $errors[] = 'Invoice total must be greater than zero.';
        }

        return $errors;
    }

    public function isReady(Invoice $invoice): bool
    {
        return $this->validateReadiness($invoice) === [];
    }

    /**
     * Build the MyInvois UBL 2.1 JSON document for an invoice.
     *
     * @return array<string, mixed>
     */
    public function buildDocument(Invoice $invoice): array
    {
        return $this->builder->build($invoice);
    }
}
