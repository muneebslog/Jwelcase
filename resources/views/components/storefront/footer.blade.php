@php
    use App\Services\SiteSettings;

    $settings = app(SiteSettings::class);
    $shopName = $settings->get('shop_name', config('shop.name'));
    $instagram = $settings->get('instagram');
    $address = $settings->get('address');
@endphp

<footer class="mt-24 border-t border-line bg-noir-surface">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div>
            <p class="font-display text-3xl tracking-[0.16em] text-ivory uppercase">{{ $shopName }}</p>
            <p class="mt-4 max-w-sm text-sm leading-relaxed text-muted">
                Fine jewelry, crafted to be worn and remembered. Inquire on WhatsApp — we handle the rest personally.
            </p>
        </div>

        <div class="text-sm">
            <p class="mb-3 tracking-widest text-gold uppercase">Explore</p>
            <div class="flex flex-col gap-2 text-muted">
                <a href="{{ route('shop') }}" class="hover:text-gold-soft" wire:navigate>Shop</a>
                <a href="{{ route('about') }}" class="hover:text-gold-soft" wire:navigate>About</a>
                <a href="{{ route('contact') }}" class="hover:text-gold-soft" wire:navigate>Contact</a>
            </div>
        </div>

        <div class="text-sm">
            <p class="mb-3 tracking-widest text-gold uppercase">Visit</p>
            <p class="text-muted">{{ $address ?: 'By appointment' }}</p>
            @if ($instagram)
                <a href="{{ $instagram }}" class="mt-3 inline-block text-gold-soft hover:text-gold" target="_blank" rel="noopener">Instagram</a>
            @endif
        </div>
    </div>
    <div class="border-t border-line py-4 text-center text-xs text-muted">
        &copy; {{ date('Y') }} {{ $shopName }}. All rights reserved.
    </div>
</footer>
