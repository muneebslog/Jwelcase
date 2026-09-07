<?php

namespace App\Services\Cart;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class Cart
{
    private const SESSION_KEY = 'shop_cart';

    /**
     * @return Collection<int, CartItem>
     */
    public function items(): Collection
    {
        /** @var array<string, array{variant_id: int, qty: int}> $raw */
        $raw = Session::get(self::SESSION_KEY, []);

        if ($raw === []) {
            return collect();
        }

        $variants = ProductVariant::query()
            ->with(['product', 'optionValues.option'])
            ->whereIn('id', collect($raw)->pluck('variant_id'))
            ->get()
            ->keyBy('id');

        return collect($raw)
            ->map(function (array $line) use ($variants): ?CartItem {
                $variant = $variants->get($line['variant_id']);

                if (! $variant instanceof ProductVariant) {
                    return null;
                }

                return new CartItem($variant, (int) $line['qty']);
            })
            ->filter()
            ->values();
    }

    public function count(): int
    {
        /** @var array<string, array{variant_id: int, qty: int}> $raw */
        $raw = Session::get(self::SESSION_KEY, []);

        return (int) collect($raw)->sum('qty');
    }

    public function add(ProductVariant $variant, int $qty = 1): void
    {
        $qty = max(1, $qty);
        $items = $this->raw();
        $key = (string) $variant->id;

        if (isset($items[$key])) {
            $items[$key]['qty'] += $qty;
        } else {
            $items[$key] = [
                'variant_id' => $variant->id,
                'qty' => $qty,
            ];
        }

        Session::put(self::SESSION_KEY, $items);
    }

    public function update(int $variantId, int $qty): void
    {
        $items = $this->raw();
        $key = (string) $variantId;

        if (! isset($items[$key])) {
            return;
        }

        if ($qty < 1) {
            unset($items[$key]);
        } else {
            $items[$key]['qty'] = $qty;
        }

        Session::put(self::SESSION_KEY, $items);
    }

    public function remove(int $variantId): void
    {
        $items = $this->raw();
        unset($items[(string) $variantId]);
        Session::put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    public function subtotal(): int
    {
        return (int) $this->items()->sum(fn (CartItem $item): int => $item->lineTotal());
    }

    /**
     * @return array<string, array{variant_id: int, qty: int}>
     */
    private function raw(): array
    {
        /** @var array<string, array{variant_id: int, qty: int}> */
        return Session::get(self::SESSION_KEY, []);
    }
}
