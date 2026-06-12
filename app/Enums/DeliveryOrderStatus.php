<?php

namespace App\Enums;

enum DeliveryOrderStatus: string
{
    case PENDING = 'pending';
    case DELIVERED = 'delivered';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::DELIVERED => 'Delivered',
            self::RETURNED => 'Returned',
        };
    }
}
