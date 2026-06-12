<?php

namespace App\Enums;

enum ProductType: string
{
    case PRODUCT = 'product';
    case SERVICE = 'service';

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT => 'Product',
            self::SERVICE => 'Service',
        };
    }
}
