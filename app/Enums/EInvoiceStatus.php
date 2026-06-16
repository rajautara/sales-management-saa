<?php

namespace App\Enums;

enum EInvoiceStatus: string
{
    case PENDING = 'pending';
    case SUBMITTED = 'submitted';
    case VALID = 'valid';
    case INVALID = 'invalid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUBMITTED => 'Submitted',
            self::VALID => 'Valid',
            self::INVALID => 'Invalid',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::SUBMITTED => 'blue',
            self::VALID => 'green',
            self::INVALID => 'red',
            self::CANCELLED => 'yellow',
        };
    }
}
