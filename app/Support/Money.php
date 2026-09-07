<?php

namespace App\Support;

use Illuminate\Support\Number;

class Money
{
    public static function format(int $amount, ?string $currency = null): string
    {
        return Number::currency(
            $amount,
            in: $currency ?? config('shop.currency', 'PKR'),
        );
    }
}
