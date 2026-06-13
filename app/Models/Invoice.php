<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_id',
        'sales_order_id',
        'number',
        'date',
        'due_date',
        'status',
        'notes',
        'subtotal',
        'discount',
        'tax',
        'total',
        'amount_paid',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('date');
    }

    public function eInvoice(): HasOne
    {
        return $this->hasOne(EInvoiceSubmission::class);
    }

    public function isEInvoiceValid(): bool
    {
        return $this->eInvoice?->isValid() ?? false;
    }

    public function amountDue(): float
    {
        return max((float) $this->total - (float) $this->amount_paid, 0);
    }

    public function recalculateStatus(): void
    {
        $totalPaid = (float) $this->payments()->sum('amount');
        $this->amount_paid = $totalPaid;

        if ($totalPaid <= 0) {
            $this->status = InvoiceStatus::SENT;
        } elseif ($totalPaid >= $this->total) {
            $this->status = InvoiceStatus::PAID;
        } else {
            $this->status = InvoiceStatus::PARTIAL;
        }

        if ($this->due_date < now()->startOfDay() && $this->status !== InvoiceStatus::PAID) {
            $this->status = InvoiceStatus::OVERDUE;
        }

        $this->save();
    }
}
