<?php

namespace App\Services\Cart;

use App\Models\ProductVariant;

class CartItem
{
    public function __construct(
        public ProductVariant $variant,
        public int $qty,
    ) {}

    public function lineTotal(): int
    {
        return $this->variant->price * $this->qty;
    }

    public function productName(): string
    {
        return $this->variant->product->name;
    }

    public function variantLabel(): string
    {
        return $this->variant->label();
    }
}
