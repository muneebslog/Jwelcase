<?php

namespace App\Enums;

enum StockStatus: string
{
    case InStock = 'in_stock';
    case MadeToOrder = 'made_to_order';
    case OutOfStock = 'out_of_stock';

    public function label(): string
    {
        return match ($this) {
            self::InStock => 'In stock',
            self::MadeToOrder => 'Made to order',
            self::OutOfStock => 'Out of stock',
        };
    }

    public function isPurchasable(): bool
    {
        return $this !== self::OutOfStock;
    }
}
