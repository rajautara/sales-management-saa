<?php

namespace App\Services\MyInvois;

use App\Enums\EInvoiceStatus;
use App\Enums\InvoiceStatus;
use App\Models\EInvoiceSubmission;
use App\Models\Invoice;
use RuntimeException;

/**
 * Orchestrates e-Invoice (MyInvois) operations for an Invoice: readiness
 * validation and UBL document building (Phase 1), plus submission, status
 * polling and cancellation against the MyInvois API (Phase 2).
 *
 * Digital signing is a Phase 2 placeholder (DocumentSigner) — the real X.509
 * signature lands in Phase 3.
 */
class EInvoiceService
{
    public function __construct(
        protected InvoiceDocumentBuilder $builder = new InvoiceDocumentBuilder,
        protected ?MyInvoisClient $client = null,
        protected DocumentSigner $signer = new DocumentSigner,
    ) {
        $this->client ??= new MyInvoisClient;
    }

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

    /**
     * Submit an invoice to MyInvois. Validates readiness first, builds and signs
     * the document, submits it, and records the result on an EInvoiceSubmission.
     */
    public function submit(Invoice $invoice): EInvoiceSubmission
    {
        // Guard against duplicate submissions: a SUBMITTED/VALID/CANCELLED row is
        // terminal — only a rejected (INVALID) document may be resubmitted. This
        // prevents a second document being filed at LHDN on a double-click/race.
        $existing = EInvoiceSubmission::firstWhere('invoice_id', $invoice->id);

        if ($existing && $existing->status !== EInvoiceStatus::INVALID) {
            throw new RuntimeException('This invoice already has an e-Invoice submission ('.$existing->status->label().'). Resubmission is only allowed after a rejection.');
        }

        $errors = $this->validateReadiness($invoice);

        if ($errors !== []) {
            throw new RuntimeException('Invoice is not ready for e-Invoice submission: '.implode(' ', $errors));
        }

        $document = $this->signer->sign($this->buildDocument($invoice));
        $json = json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $hash = hash('sha256', $json);

        $result = $this->client->submitDocuments([[
            'format' => 'JSON',
            'documentHash' => $hash,
            'codeNumber' => $invoice->number,
            'document' => base64_encode($json),
        ]]);

        $submission = $existing ?? new EInvoiceSubmission(['invoice_id' => $invoice->id]);
        $submission->type = 'invoice';
        $submission->submission_uid = $result['submissionUid'] ?? null;
        $submission->document_hash = $hash;

        $accepted = collect($result['acceptedDocuments'] ?? []);
        $rejected = collect($result['rejectedDocuments'] ?? []);

        if ($accepted->isNotEmpty()) {
            $submission->uuid = $accepted->first()['uuid'] ?? null;
            $submission->status = EInvoiceStatus::SUBMITTED;
            $submission->submitted_at = now();
            $submission->error_log = null;
        } else {
            $submission->status = EInvoiceStatus::INVALID;
            $submission->error_log = $rejected->all();
        }

        $submission->save();

        return $submission;
    }

    /**
     * Poll MyInvois for the current validation status and update the submission.
     */
    public function refreshStatus(EInvoiceSubmission $submission): EInvoiceSubmission
    {
        if (blank($submission->uuid)) {
            return $submission;
        }

        $details = $this->client->getDocumentDetails($submission->uuid);
        $status = strtolower((string) ($details['status'] ?? ''));

        $submission->long_id = $details['longId'] ?? $submission->long_id;

        match ($status) {
            'valid' => $this->markValid($submission, $details),
            'invalid' => $this->markInvalid($submission, $details),
            'cancelled' => $submission->status = EInvoiceStatus::CANCELLED,
            default => $submission->status = EInvoiceStatus::SUBMITTED,
        };

        $submission->save();

        return $submission;
    }

    /**
     * Cancel a validated e-Invoice (within the 72-hour window enforced by LHDN).
     */
    public function cancel(EInvoiceSubmission $submission, string $reason): EInvoiceSubmission
    {
        if (blank($submission->uuid)) {
            throw new RuntimeException('Cannot cancel an e-Invoice that has no UUID.');
        }

        $this->client->cancelDocument($submission->uuid, $reason);

        $submission->status = EInvoiceStatus::CANCELLED;
        $submission->cancelled_at = now();
        $submission->cancel_reason = $reason;
        $submission->save();

        return $submission;
    }

    /**
     * @param  array<string, mixed>  $details
     */
    protected function markValid(EInvoiceSubmission $submission, array $details): void
    {
        $submission->status = EInvoiceStatus::VALID;
        $submission->validated_at = now();
        $submission->long_id = $details['longId'] ?? $submission->long_id;

        if (filled($submission->uuid) && filled($submission->long_id)) {
            $submission->validation_link = $this->client->portalUrl().'/'.$submission->uuid.'/share/'.$submission->long_id;
            $submission->qr_payload = $submission->validation_link;
        }
    }

    /**
     * @param  array<string, mixed>  $details
     */
    protected function markInvalid(EInvoiceSubmission $submission, array $details): void
    {
        $submission->status = EInvoiceStatus::INVALID;
        $submission->error_log = $details['validationResults'] ?? $details;
    }
}
