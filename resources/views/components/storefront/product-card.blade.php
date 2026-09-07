@props([
    'product',
])

@php
    use App\Support\Money;

    $image = $product->primaryImage();
    $variant = $product->defaultVariant();
@endphp

<a href="{{ route('product.show', $product) }}" class="group block storefront-reveal" wire:navigate>
    <div class="storefront-image-zoom aspect-[4/5] bg-noir-elevated">
        @if ($image)
            <img src="{{ $image->url() }}" alt="{{ $image->alt ?: $product->name }}" class="h-full w-full object-cover">
        @else
            <div class="flex h-full items-center justify-center text-sm text-muted">No image</div>
        @endif
    </div>
    <div class="mt-4 space-y-1">
        <h3 class="font-display text-xl text-ivory transition group-hover:text-gold-soft">{{ $product->name }}</h3>
        @if ($product->category)
            <p class="text-xs tracking-widest text-muted uppercase">{{ $product->category->name }}</p>
        @endif
        @if ($variant)
            <p class="text-sm text-gold">{{ Money::format($variant->price) }}</p>
        @endif
    </div>
</a>
