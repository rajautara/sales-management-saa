<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case DRAFT = 'draft';
    case ORDERED = 'ordered';
    case PARTIAL = 'partial';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ORDERED => 'Ordered',
            self::PARTIAL => 'Partial',
            self::RECEIVED => 'Received',
            self::CANCELLED => 'Cancelled',
        };
    }
}
