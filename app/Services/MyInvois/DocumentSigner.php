<?php

namespace App\Services\MyInvois;

use RuntimeException;

/**
 * Applies the MyInvois document digital signature (XAdES-in-JSON enveloped
 * signature) using the configured X.509 certificate.
 *
 * If no certificate is configured the document is returned unchanged, so the
 * submission pipeline can still be exercised against the sandbox structurally.
 *
 * Note: MyInvois validates the signature against a byte-exact canonical form of
 * the document. The canonical() helper is isolated so it can be tuned against
 * the LHDN sandbox validator if needed.
 *
 * @see https://sdk.myinvois.hasil.gov.my/signature/
 */
class DocumentSigner
{
    private const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';

    private const RSA_SHA256 = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    public function sign(array $document): array
    {
        $certPath = config('myinvois.cert_path');

        if (blank($certPath)) {
            return $document; // No certificate configured — leave unsigned.
        }

        if (! is_file($certPath)) {
            throw new RuntimeException("MyInvois signing certificate not found at: {$certPath}");
        }

        if (! openssl_pkcs12_read((string) file_get_contents($certPath), $certs, (string) config('myinvois.cert_password'))) {
            throw new RuntimeException('Unable to read the MyInvois signing certificate (.p12/.pfx). Check MYINVOIS_CERT_PASSWORD.');
        }

        $certPem = $certs['cert'];
        $privateKey = $certs['pkey'];
        $parsed = openssl_x509_parse($certPem) ?: [];

        $certDer = $this->pemToDer($certPem);
        $certBase64 = base64_encode($certDer);
        $certDigest = base64_encode(hash('sha256', $certDer, true));
        $issuerName = $this->distinguishedName($parsed['issuer'] ?? []);
        $subjectName = $this->distinguishedName($parsed['subject'] ?? []);
        $serialNumber = (string) ($parsed['serialNumber'] ?? '');

        // 1. Digest + signature over the document (excluding UBLExtensions/Signature,
        //    which are not present at this point).
        $docCanonical = $this->canonical($document);
        $docDigest = base64_encode(hash('sha256', $docCanonical, true));

        openssl_sign($docCanonical, $signatureRaw, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureValue = base64_encode($signatureRaw);

        // 2. SignedProperties + its digest.
        $signedProperties = [[
            'Id' => 'id-xades-signed-props',
            'SignedSignatureProperties' => [[
                'SigningTime' => [['_' => now()->utc()->format('Y-m-d\TH:i:s\Z')]],
                'SigningCertificate' => [[
                    'Cert' => [[
                        'CertDigest' => [[
                            'DigestMethod' => [['_' => '', 'Algorithm' => self::SHA256]],
                            'DigestValue' => [['_' => $certDigest]],
                        ]],
                        'IssuerSerial' => [[
                            'X509IssuerName' => [['_' => $issuerName]],
                            'X509SerialNumber' => [['_' => $serialNumber]],
                        ]],
                    ]],
                ]],
            ]],
        ]];

        $propsDigest = base64_encode(hash('sha256', $this->canonical($signedProperties), true));

        // 3. Assemble the enveloped signature extension + top-level Signature pointer.
        $document['Invoice'][0]['UBLExtensions'] = [[
            'UBLExtension' => [[
                'ExtensionURI' => [['_' => 'urn:oasis:names:specification:ubl:dsig:enveloped:xades']],
                'ExtensionContent' => [[
                    'UBLDocumentSignatures' => [[
                        'SignatureInformation' => [[
                            'ID' => [['_' => 'urn:oasis:names:specification:ubl:signature:1']],
                            'ReferencedSignatureID' => [['_' => 'urn:oasis:names:specification:ubl:signature:Invoice']],
                            'Signature' => [[
                                'ID' => [['_' => 'signature']],
                                'SignedInfo' => [[
                                    'SignatureMethod' => [['_' => '', 'Algorithm' => self::RSA_SHA256]],
                                    'Reference' => [
                                        [
                                            'Id' => 'id-doc-signed-data',
                                            'URI' => '',
                                            'DigestMethod' => [['_' => '', 'Algorithm' => self::SHA256]],
                                            'DigestValue' => [['_' => $docDigest]],
                                        ],
                                        [
                                            'Id' => 'id-xades-signed-props',
                                            'Type' => 'http://uri.etsi.org/01903/v1.3.2#SignedProperties',
                                            'URI' => '#id-xades-signed-props',
                                            'DigestMethod' => [['_' => '', 'Algorithm' => self::SHA256]],
                                            'DigestValue' => [['_' => $propsDigest]],
                                        ],
                                    ],
                                ]],
                                'SignatureValue' => [['_' => $signatureValue]],
                                'KeyInfo' => [[
                                    'X509Data' => [[
                                        'X509Certificate' => [['_' => $certBase64]],
                                        'X509SubjectName' => [['_' => $subjectName]],
                                        'X509IssuerSerial' => [[
                                            'X509IssuerName' => [['_' => $issuerName]],
                                            'X509SerialNumber' => [['_' => $serialNumber]],
                                        ]],
                                    ]],
                                ]],
                                'Object' => [[
                                    'QualifyingProperties' => [[
                                        'Target' => 'signature',
                                        'SignedProperties' => $signedProperties,
                                    ]],
                                ]],
                            ]],
                        ]],
                    ]],
                ]],
            ]],
        ]];

        $document['Invoice'][0]['Signature'] = [[
            'ID' => [['_' => 'urn:oasis:names:specification:ubl:signature:Invoice']],
            'SignatureMethod' => [['_' => 'urn:oasis:names:specification:ubl:dsig:enveloped:rsa-sha256']],
        ]];

        return $document;
    }

    /**
     * Canonical (minified) JSON used for hashing and signing.
     *
     * @param  array<mixed>  $value
     */
    protected function canonical(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function pemToDer(string $pem): string
    {
        $body = preg_replace('/-----[^-]+-----|\s+/', '', $pem);

        return (string) base64_decode((string) $body);
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    protected function distinguishedName(array $parts): string
    {
        $order = ['CN', 'OU', 'O', 'L', 'ST', 'C'];
        $segments = [];

        foreach ($order as $key) {
            if (! empty($parts[$key])) {
                $value = is_array($parts[$key]) ? implode('+', $parts[$key]) : $parts[$key];
                $segments[] = "{$key}={$value}";
            }
        }

        return implode(', ', $segments);
    }
}
