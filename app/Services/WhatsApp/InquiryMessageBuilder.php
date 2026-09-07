<?php

namespace App\Services\WhatsApp;

use App\Services\Cart\Cart;
use App\Services\Cart\CartItem;
use App\Services\SiteSettings;
use Illuminate\Support\Number;

class InquiryMessageBuilder
{
    public function __construct(
        private SiteSettings $settings,
    ) {}

    /**
     * @param  array{customer_name?: string|null, phone?: string|null, note?: string|null}  $customer
     */
    public function build(Cart $cart, array $customer = []): string
    {
        $shopName = $this->settings->get('shop_name', config('shop.name'));
        $currency = $this->settings->get('currency', config('shop.currency'));
        $lines = [
            "Hello {$shopName}, I'd like to inquire about the following items:",
            '',
        ];

        foreach ($cart->items() as $item) {
            /** @var CartItem $item */
            $price = Number::currency($item->variant->price, in: $currency);
            $label = $item->variantLabel();
            $sku = $item->variant->sku ? " (SKU: {$item->variant->sku})" : '';
            $variantBit = $label !== '' ? " — {$label}" : '';

            $lines[] = "• {$item->productName()}{$variantBit}{$sku} × {$item->qty} @ {$price}";
        }

        $lines[] = '';
        $lines[] = 'Subtotal: '.Number::currency($cart->subtotal(), in: $currency);

        if (! empty($customer['customer_name'])) {
            $lines[] = 'Name: '.$customer['customer_name'];
        }

        if (! empty($customer['phone'])) {
            $lines[] = 'Phone: '.$customer['phone'];
        }

        if (! empty($customer['note'])) {
            $lines[] = 'Note: '.$customer['note'];
        }

        return implode("\n", $lines);
    }

    public function url(string $message): string
    {
        $number = preg_replace('/\D+/', '', (string) $this->settings->get(
            'whatsapp_number',
            config('shop.whatsapp_number'),
        ));

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }
}
