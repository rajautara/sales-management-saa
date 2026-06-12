<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public const TYPE_QUOTATION = 'QT';
    public const TYPE_SALES_ORDER = 'SO';
    public const TYPE_DELIVERY_ORDER = 'DO';
    public const TYPE_INVOICE = 'INV';
    public const TYPE_RECEIPT = 'RC';
    public const TYPE_PURCHASE_ORDER = 'PO';

    public static function next(string $type, ?int $year = null, ?int $companyId = null): string
    {
        $year ??= now()->year;
        $companyId ??= auth()->user()?->company_id;

        if (! $companyId) {
            throw new \RuntimeException('Company context is required for sequence generation.');
        }

        $sequence = DB::transaction(function () use ($companyId, $type, $year) {
            $sequence = DocumentSequence::lockForUpdate()
                ->firstOrCreate(
                    ['company_id' => $companyId, 'type' => $type, 'year' => $year],
                    ['last_number' => 0]
                );

            $sequence->increment('last_number');
            $sequence->refresh();

            return $sequence;
        });

        return sprintf('%s-%d-%04d', $type, $year, $sequence->last_number);
    }
}
