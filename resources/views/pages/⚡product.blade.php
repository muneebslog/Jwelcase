<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\Cart;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component {
    public Product $product;

    /** @var array<int, int> option_id => option_value_id */
    public array $selectedOptions = [];

    public int $qty = 1;

    public function mount(Product $product): void
    {
        abort_unless($product->is_published, 404);

        $this->product = $product->load([
            'category',
            'images',
            'options.values',
            'variants.optionValues',
            'variants.images',
        ]);

        $default = $this->product->defaultVariant();

        if ($default) {
            foreach ($default->optionValues as $value) {
                $this->selectedOptions[$value->product_option_id] = $value->id;
            }
        } else {
            foreach ($this->product->options as $option) {
                $first = $option->values->first();
                if ($first) {
                    $this->selectedOptions[$option->id] = $first->id;
                }
            }
        }
    }

    #[Computed]
    public function resolvedVariant(): ?ProductVariant
    {
        $selected = collect($this->selectedOptions)->values()->sort()->values();

        return $this->product->variants->first(function (ProductVariant $variant) use ($selected): bool {
            $ids = $variant->optionValues->pluck('id')->sort()->values();

            return $ids->all() === $selected->all();
        });
    }

    #[Computed]
    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\ProductImage>
     */
    public function gallery()
    {
        $variant = $this->resolvedVariant;

        if ($variant && $variant->images->isNotEmpty()) {
            return $variant->images;
        }

        return $this->product->images->whereNull('product_variant_id')->values();
    }

    public function selectOption(int $optionId, int $valueId): void
    {
        $this->selectedOptions[$optionId] = $valueId;
        unset($this->resolvedVariant);
        unset($this->gallery);
    }

    public function addToCart(Cart $cart): void
    {
        $variant = $this->resolvedVariant;

        if (! $variant || ! $variant->isPurchasable()) {
            Flux::toast(variant: 'danger', text: 'This combination is unavailable.');

            return;
        }

        $cart->add($variant, $this->qty);

        Flux::toast(variant: 'success', text: 'Added to cart.');
    }

    public function title(): string
    {
        return $this->product->seo_title ?: $this->product->name;
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="grid gap-12 lg:grid-cols-2">
        <div>
            @php $main = $this->gallery->first(); @endphp
            <div class="storefront-image-zoom aspect-square bg-noir-elevated">
                @if ($main)
                    <img src="{{ $main->url() }}" alt="{{ $main->alt ?: $product->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center text-muted">No image</div>
                @endif
            </div>
            @if ($this->gallery->count() > 1)
                <div class="mt-3 grid grid-cols-4 gap-3">
                    @foreach ($this->gallery as $image)
                        <div class="aspect-square overflow-hidden bg-noir-elevated">
                            <img src="{{ $image->url() }}" alt="" class="h-full w-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if ($product->category)
                <p class="text-xs tracking-[0.25em] text-gold uppercase">{{ $product->category->name }}</p>
            @endif
            <h1 class="mt-3 font-display text-4xl text-ivory">{{ $product->name }}</h1>
            <p class="mt-4 text-muted">{{ $product->short_description }}</p>

            @if ($this->resolvedVariant)
                <div class="mt-6 flex items-baseline gap-3">
                    <x-storefront.price :amount="$this->resolvedVariant->price" class="text-2xl text-gold" />
                    @if ($this->resolvedVariant->compare_at_price)
                        <x-storefront.price :amount="$this->resolvedVariant->compare_at_price" class="text-sm text-muted line-through" />
                    @endif
                </div>
                <p class="mt-2 text-xs text-muted">{{ $this->resolvedVariant->stock_status->label() }}</p>
            @else
                <p class="mt-6 text-sm text-muted">Select options to see price.</p>
            @endif

            <div class="mt-8 space-y-6">
                @foreach ($product->options as $option)
                    <div>
                        <p class="mb-3 text-xs tracking-widest text-muted uppercase">{{ $option->name }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($option->values as $value)
                                <button
                                    type="button"
                                    wire:click="selectOption({{ $option->id }}, {{ $value->id }})"
                                    class="border px-4 py-2 text-sm transition {{ ($selectedOptions[$option->id] ?? null) === $value->id ? 'border-gold text-gold' : 'border-line text-ivory hover:border-gold' }}"
                                >
                                    {{ $value->value }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-4">
                <input type="number" min="1" wire:model="qty" class="w-20 border border-line bg-noir-elevated px-3 py-3 text-ivory focus:border-gold focus:outline-none">
                <button
                    type="button"
                    wire:click="addToCart"
                    class="border border-gold bg-gold px-8 py-3 text-sm tracking-widest text-noir uppercase transition hover:bg-gold-soft"
                    @disabled(! $this->resolvedVariant || ! $this->resolvedVariant->isPurchasable())
                >
                    Add to cart
                </button>
            </div>

            <div class="prose prose-invert mt-10 max-w-none text-sm text-muted">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>
    </div>
</div>
