<?php

namespace App\Services\MyInvois;

/**
 * Applies the MyInvois document digital signature.
 *
 * Phase 2 placeholder: returns the document unchanged so the submission
 * pipeline can run against the sandbox. Phase 3 will implement the real
 * X.509 (SHA-256) UBL signature using the configured certificate.
 */
class DocumentSigner
{
    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    public function sign(array $document): array
    {
        return $document;
    }
}
