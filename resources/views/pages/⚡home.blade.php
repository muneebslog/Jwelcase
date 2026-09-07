<?php

use App\Models\Category;
use App\Models\Product;
use App\Services\SiteSettings;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.storefront')] #[Title('Home')] class extends Component {
    #[Computed]
    public function settings(): SiteSettings
    {
        return app(SiteSettings::class);
    }

    #[Computed]
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    public function featuredProducts()
    {
        return Product::query()
            ->published()
            ->featured()
            ->with(['category', 'images', 'variants'])
            ->latest()
            ->take(4)
            ->get();
    }

    #[Computed]
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    public function categories()
    {
        return Category::query()
            ->published()
            ->orderBy('sort_order')
            ->get();
    }
}; ?>

<div>
    <section class="relative isolate min-h-[88vh] overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_#1c1c1c_0%,_#0b0b0b_55%)]"></div>
        <div class="absolute inset-0 opacity-40" style="background-image: linear-gradient(120deg, transparent 40%, rgba(198,167,94,0.12)), repeating-linear-gradient(90deg, transparent, transparent 80px, rgba(42,42,42,0.35) 81px);"></div>

        <div class="relative mx-auto flex min-h-[88vh] max-w-7xl flex-col justify-end px-4 pb-20 pt-28 sm:px-6 lg:px-8">
            <p class="font-display text-5xl tracking-[0.22em] text-gold-soft uppercase sm:text-6xl lg:text-7xl storefront-reveal">
                {{ $this->settings->get('shop_name', config('shop.name')) }}
            </p>
            <h1 class="mt-8 max-w-2xl font-display text-3xl leading-tight text-ivory sm:text-4xl storefront-reveal" style="animation-delay: 0.12s">
                {{ $this->settings->get('hero_headline', 'Jewelry that holds a story') }}
            </h1>
            <p class="mt-4 max-w-xl text-base text-muted storefront-reveal" style="animation-delay: 0.22s">
                {{ $this->settings->get('hero_subline', 'Select your piece, send on WhatsApp, and we\'ll craft the rest with you.') }}
            </p>
            <div class="mt-10 storefront-reveal" style="animation-delay: 0.32s">
                <a href="{{ route('shop') }}" class="inline-flex items-center border border-gold px-8 py-3 text-sm tracking-widest text-gold uppercase transition hover:bg-gold hover:text-noir" wire:navigate>
                    {{ $this->settings->get('hero_cta_label', 'Explore the collection') }}
                </a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mb-10 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs tracking-[0.25em] text-gold uppercase">Featured</p>
                <h2 class="mt-2 font-display text-3xl text-ivory">Selected pieces</h2>
            </div>
            <a href="{{ route('shop') }}" class="text-sm text-muted hover:text-gold-soft" wire:navigate>View all</a>
        </div>

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($this->featuredProducts as $product)
                <x-storefront.product-card :product="$product" />
            @empty
                <p class="text-muted col-span-full">New pieces are coming soon.</p>
            @endforelse
        </div>
    </section>

    <section class="border-y border-line bg-noir-surface">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-xs tracking-[0.25em] text-gold uppercase">Collections</p>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($this->categories as $category)
                    <a href="{{ route('shop.category', $category) }}" class="border border-line px-6 py-8 transition hover:border-gold" wire:navigate>
                        <h3 class="font-display text-2xl text-ivory">{{ $category->name }}</h3>
                        <p class="mt-2 text-sm text-muted">{{ $category->description }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 lg:px-8">
        <p class="text-xs tracking-[0.25em] text-gold uppercase">Atelier</p>
        <p class="mt-6 font-display text-3xl leading-snug text-ivory">
            {{ $this->settings->get('about_teaser', 'Fine jewelry, crafted to be worn and remembered.') }}
        </p>
        <a href="{{ route('about') }}" class="mt-8 inline-block text-sm tracking-widest text-gold uppercase hover:text-gold-soft" wire:navigate>
            Our story
        </a>
    </section>
</div>
