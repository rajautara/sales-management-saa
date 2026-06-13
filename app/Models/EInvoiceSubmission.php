<?php

namespace App\Models;

use App\Enums\EInvoiceStatus;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EInvoiceSubmission extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $table = 'einvoice_submissions';

    protected $fillable = [
        'company_id',
        'invoice_id',
        'type',
        'status',
        'uuid',
        'long_id',
        'submission_uid',
        'validation_link',
        'qr_payload',
        'document_hash',
        'submitted_at',
        'validated_at',
        'cancelled_at',
        'cancel_reason',
        'error_log',
    ];

    protected $casts = [
        'status' => EInvoiceStatus::class,
        'error_log' => 'array',
        'submitted_at' => 'datetime',
        'validated_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isValid(): bool
    {
        return $this->status === EInvoiceStatus::VALID;
    }

    public function isCancellable(): bool
    {
        return $this->status === EInvoiceStatus::VALID
            && $this->validated_at !== null
            && $this->validated_at->diffInHours(now()) < 72;
    }
}
