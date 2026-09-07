<?php

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Services\Cart\Cart;
use App\Services\WhatsApp\InquiryMessageBuilder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.storefront')] #[Title('Cart')] class extends Component {
    public string $customer_name = '';

    public string $phone = '';

    public string $note = '';

    public function updateQty(Cart $cart, int $variantId, int $qty): void
    {
        $cart->update($variantId, $qty);
    }

    public function remove(Cart $cart, int $variantId): void
    {
        $cart->remove($variantId);
    }

    public function sendOnWhatsApp(Cart $cart, InquiryMessageBuilder $builder): mixed
    {
        $this->validate([
            'customer_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($cart->isEmpty()) {
            $this->addError('cart', 'Your cart is empty.');

            return null;
        }

        $customer = [
            'customer_name' => $this->customer_name ?: null,
            'phone' => $this->phone ?: null,
            'note' => $this->note ?: null,
        ];

        $message = $builder->build($cart, $customer);
        $url = $builder->url($message);

        DB::transaction(function () use ($cart, $customer, $url): void {
            $inquiry = Inquiry::query()->create([
                'customer_name' => $customer['customer_name'],
                'phone' => $customer['phone'],
                'note' => $customer['note'],
                'whatsapp_url' => $url,
                'status' => InquiryStatus::New,
            ]);

            foreach ($cart->items() as $item) {
                $inquiry->items()->create([
                    'product_variant_id' => $item->variant->id,
                    'product_name' => $item->productName(),
                    'variant_label' => $item->variantLabel() ?: null,
                    'unit_price' => $item->variant->price,
                    'qty' => $item->qty,
                ]);
            }
        });

        $cart->clear();

        return $this->redirect($url, navigate: false);
    }
}; ?>

<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="font-display text-4xl text-ivory">Cart</h1>

    @php $cart = app(\App\Services\Cart\Cart::class); @endphp

    @if ($cart->isEmpty())
        <p class="mt-8 text-muted">Your cart is empty.</p>
        <a href="{{ route('shop') }}" class="mt-6 inline-block text-gold hover:text-gold-soft" wire:navigate>Continue shopping</a>
    @else
        <div class="mt-10 space-y-6">
            @foreach ($cart->items() as $item)
                <div class="flex flex-col gap-4 border-b border-line pb-6 sm:flex-row sm:items-center sm:justify-between" wire:key="cart-{{ $item->variant->id }}">
                    <div>
                        <p class="font-display text-xl text-ivory">{{ $item->productName() }}</p>
                        @if ($item->variantLabel() !== '')
                            <p class="mt-1 text-sm text-muted">{{ $item->variantLabel() }}</p>
                        @endif
                        <x-storefront.price :amount="$item->variant->price" class="mt-2 block text-gold" />
                    </div>
                    <div class="flex items-center gap-3">
                        <input
                            type="number"
                            min="1"
                            value="{{ $item->qty }}"
                            wire:change="updateQty({{ $item->variant->id }}, $event.target.value)"
                            class="w-20 border border-line bg-noir-elevated px-3 py-2 text-ivory focus:border-gold focus:outline-none"
                        >
                        <button type="button" wire:click="remove({{ $item->variant->id }})" class="text-sm text-muted hover:text-gold-soft">Remove</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-between text-lg">
            <span class="text-muted">Subtotal</span>
            <x-storefront.price :amount="$cart->subtotal()" class="text-gold" />
        </div>

        <form wire:submit="sendOnWhatsApp" class="mt-10 space-y-4 border border-line bg-noir-surface p-6">
            <p class="text-sm text-muted">Share a few details, then continue on WhatsApp. No online payment — we'll arrange everything with you.</p>

            <div>
                <label class="mb-1 block text-xs tracking-widest text-muted uppercase">Name</label>
                <input type="text" wire:model="customer_name" class="w-full border border-line bg-noir-elevated px-3 py-2 text-ivory focus:border-gold focus:outline-none">
                @error('customer_name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs tracking-widest text-muted uppercase">Phone</label>
                <input type="text" wire:model="phone" class="w-full border border-line bg-noir-elevated px-3 py-2 text-ivory focus:border-gold focus:outline-none">
                @error('phone') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs tracking-widest text-muted uppercase">Note</label>
                <textarea wire:model="note" rows="3" class="w-full border border-line bg-noir-elevated px-3 py-2 text-ivory focus:border-gold focus:outline-none"></textarea>
                @error('note') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            @error('cart') <p class="text-sm text-red-400">{{ $message }}</p> @enderror

            <button type="submit" class="w-full border border-gold bg-gold px-6 py-3 text-sm tracking-widest text-noir uppercase transition hover:bg-gold-soft">
                Send on WhatsApp
            </button>
        </form>
    @endif
</div>
