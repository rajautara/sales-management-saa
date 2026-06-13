<?php

namespace App\Services\MyInvois;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;

/**
 * Maps an Invoice model into the MyInvois (LHDN) UBL 2.1 JSON document
 * structure required by the Submit Documents API.
 *
 * The MyInvois JSON format wraps every value in an array of objects keyed by
 * "_" (the value) plus optional attributes, e.g. [{"_": "INV-1", "schemeID": "X"}].
 *
 * @see https://sdk.myinvois.hasil.gov.my/einvoicingapi/02-submit-documents/
 */
class InvoiceDocumentBuilder
{
    public function build(Invoice $invoice): array
    {
        $invoice->loadMissing(['company', 'customer', 'items.product']);

        return [
            '_D' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
            '_A' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
            '_B' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
            'Invoice' => [[
                'ID' => $this->v($invoice->number),
                'IssueDate' => $this->v($invoice->date->format('Y-m-d')),
                'IssueTime' => $this->v($invoice->created_at?->format('H:i:s\Z') ?? '00:00:00Z'),
                'InvoiceTypeCode' => [$this->value('01', ['listVersionID' => '1.0'])],
                'DocumentCurrencyCode' => $this->v($invoice->company->currency ?? 'MYR'),
                'AccountingSupplierParty' => [[
                    'Party' => [$this->supplierParty($invoice->company)],
                ]],
                'AccountingCustomerParty' => [[
                    'Party' => [$this->customerParty($invoice->customer)],
                ]],
                'InvoiceLine' => $this->lines($invoice),
                'TaxTotal' => [$this->taxTotal($invoice)],
                'LegalMonetaryTotal' => [$this->legalMonetaryTotal($invoice)],
            ]],
        ];
    }

    protected function supplierParty(Company $company): array
    {
        $countryCode = $company->address_country_code ?: config('myinvois.defaults.country_code');

        return [
            'IndustryClassificationCode' => [
                $this->value($company->msic_code ?? '', ['name' => $company->business_activity_desc ?? '']),
            ],
            'PartyIdentification' => $this->partyIdentifications([
                'TIN' => $company->tin,
                'BRN' => $company->registration_no,
                'SST' => $company->sst_registration_no,
            ]),
            'PostalAddress' => [[
                'CityName' => $this->v($company->address_city ?? ''),
                'PostalZone' => $this->v($company->address_postcode ?? ''),
                'CountrySubentityCode' => $this->v($company->address_state_code ?? ''),
                'AddressLine' => [['Line' => $this->v($company->address ?? '')]],
                'Country' => [['IdentificationCode' => $this->v($countryCode)]],
            ]],
            'PartyLegalEntity' => [[
                'RegistrationName' => $this->v($company->name),
            ]],
            'Contact' => [[
                'Telephone' => $this->v($company->phone ?? ''),
                'ElectronicMail' => $this->v($company->email ?? ''),
            ]],
        ];
    }

    protected function customerParty(Customer $customer): array
    {
        $countryCode = $customer->address_country_code ?: config('myinvois.defaults.country_code');

        return [
            'PartyIdentification' => $this->partyIdentifications([
                'TIN' => $customer->tin,
                $customer->registration_type ?: 'BRN' => $customer->registration_no,
                'SST' => $customer->sst_registration_no,
            ]),
            'PostalAddress' => [[
                'CityName' => $this->v($customer->address_city ?? ''),
                'PostalZone' => $this->v($customer->address_postcode ?? ''),
                'CountrySubentityCode' => $this->v($customer->address_state_code ?? ''),
                'AddressLine' => [['Line' => $this->v($customer->billing_address ?? '')]],
                'Country' => [['IdentificationCode' => $this->v($countryCode)]],
            ]],
            'PartyLegalEntity' => [[
                'RegistrationName' => $this->v($customer->name),
            ]],
            'Contact' => [[
                'Telephone' => $this->v($customer->phone ?? ''),
                'ElectronicMail' => $this->v($customer->email ?? ''),
            ]],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function lines(Invoice $invoice): array
    {
        return $invoice->items->values()->map(function (InvoiceItem $item, int $index) use ($invoice) {
            $classification = $item->product?->classification_code
                ?: config('myinvois.defaults.classification_code');
            $uom = $item->product?->uom_code ?: config('myinvois.defaults.uom_code');
            $currency = $invoice->company->currency ?? 'MYR';

            return [
                'ID' => $this->v((string) ($index + 1)),
                'InvoicedQuantity' => [$this->value($this->num($item->qty), ['unitCode' => $uom])],
                'LineExtensionAmount' => [$this->value($this->num($item->total), ['currencyID' => $currency])],
                'TaxTotal' => [[
                    'TaxAmount' => [$this->value($this->num($item->tax), ['currencyID' => $currency])],
                ]],
                'Item' => [[
                    'Description' => $this->v($item->description),
                    'CommodityClassification' => [[
                        'ItemClassificationCode' => [$this->value($classification, ['listID' => 'CLASS'])],
                    ]],
                ]],
                'Price' => [[
                    'PriceAmount' => [$this->value($this->num($item->unit_price), ['currencyID' => $currency])],
                ]],
            ];
        })->all();
    }

    protected function taxTotal(Invoice $invoice): array
    {
        $currency = $invoice->company->currency ?? 'MYR';

        return [
            'TaxAmount' => [$this->value($this->num($invoice->tax), ['currencyID' => $currency])],
        ];
    }

    protected function legalMonetaryTotal(Invoice $invoice): array
    {
        $currency = $invoice->company->currency ?? 'MYR';

        return [
            'LineExtensionAmount' => [$this->value($this->num($invoice->subtotal), ['currencyID' => $currency])],
            'TaxExclusiveAmount' => [$this->value($this->num($invoice->subtotal), ['currencyID' => $currency])],
            'TaxInclusiveAmount' => [$this->value($this->num($invoice->total), ['currencyID' => $currency])],
            'AllowanceTotalAmount' => [$this->value($this->num($invoice->discount), ['currencyID' => $currency])],
            'PayableAmount' => [$this->value($this->num($invoice->total), ['currencyID' => $currency])],
        ];
    }

    /**
     * @param  array<string, string|null>  $schemes
     * @return array<int, array<string, mixed>>
     */
    protected function partyIdentifications(array $schemes): array
    {
        $identifications = [];

        foreach ($schemes as $schemeId => $value) {
            $identifications[] = [
                'ID' => [$this->value($value ?: 'NA', ['schemeID' => $schemeId])],
            ];
        }

        return $identifications;
    }

    /**
     * Wrap a single scalar value in MyInvois format: [{"_": value}].
     *
     * @return array<int, array<string, mixed>>
     */
    protected function v(string $value): array
    {
        return [$this->value($value)];
    }

    /**
     * Build one MyInvois value object: {"_": value, ...attributes}.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function value(string $value, array $attributes = []): array
    {
        return array_merge(['_' => $value], $attributes);
    }

    protected function num(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
