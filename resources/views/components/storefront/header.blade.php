@php
    use App\Services\Cart\Cart;
    use App\Services\SiteSettings;

    $settings = app(SiteSettings::class);
    $shopName = $settings->get('shop_name', config('shop.name'));
    $cartCount = app(Cart::class)->count();
@endphp

<header class="sticky top-0 z-40 border-b border-line/80 bg-noir/90 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="font-display text-2xl tracking-[0.18em] text-ivory uppercase" wire:navigate>
            {{ $shopName }}
        </a>

        <nav class="hidden items-center gap-8 text-sm tracking-wide md:flex">
            <a href="{{ route('home') }}" class="storefront-nav-link" @if(request()->routeIs('home')) aria-current="page" @endif wire:navigate>Home</a>
            <a href="{{ route('shop') }}" class="storefront-nav-link" @if(request()->routeIs('shop', 'shop.category', 'product.show')) aria-current="page" @endif wire:navigate>Shop</a>
            <a href="{{ route('about') }}" class="storefront-nav-link" @if(request()->routeIs('about')) aria-current="page" @endif wire:navigate>About</a>
            <a href="{{ route('contact') }}" class="storefront-nav-link" @if(request()->routeIs('contact')) aria-current="page" @endif wire:navigate>Contact</a>
        </nav>

        <a href="{{ route('cart') }}" class="relative inline-flex items-center gap-2 text-sm text-muted transition hover:text-gold-soft" wire:navigate>
            <span>Cart</span>
            @if ($cartCount > 0)
                <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-gold px-1.5 text-xs font-medium text-noir">{{ $cartCount }}</span>
            @endif
        </a>
    </div>
</header>
