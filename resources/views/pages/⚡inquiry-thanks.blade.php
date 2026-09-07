<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.storefront')] #[Title('Thank you')] class extends Component {
}; ?>

<div class="mx-auto max-w-xl px-4 py-24 text-center sm:px-6 lg:px-8">
    <p class="text-xs tracking-[0.25em] text-gold uppercase">Inquiry sent</p>
    <h1 class="mt-4 font-display text-4xl text-ivory">We'll take it from here</h1>
    <p class="mt-4 text-muted">WhatsApp should have opened with your cart details. If it didn't, return to your cart and try again.</p>
    <a href="{{ route('shop') }}" class="mt-10 inline-block text-gold hover:text-gold-soft" wire:navigate>Back to shop</a>
</div>
