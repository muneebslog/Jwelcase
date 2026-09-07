<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.storefront')] #[Title('Shop')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = 'newest';

    public ?int $categoryId = null;

    public function mount(?Category $category = null): void
    {
        if ($category?->exists) {
            abort_unless($category->is_published, 404);
            $this->categoryId = $category->id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Product>
     */
    public function products()
    {
        return Product::query()
            ->published()
            ->with(['category', 'images', 'variants'])
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->sort === 'price_asc', function ($q) {
                $q->orderBy(
                    \App\Models\ProductVariant::query()
                        ->select('price')
                        ->whereColumn('product_variants.product_id', 'products.id')
                        ->orderBy('price')
                        ->limit(1)
                );
            })
            ->when($this->sort === 'price_desc', function ($q) {
                $q->orderByDesc(
                    \App\Models\ProductVariant::query()
                        ->select('price')
                        ->whereColumn('product_variants.product_id', 'products.id')
                        ->orderByDesc('price')
                        ->limit(1)
                );
            })
            ->when($this->sort === 'newest', fn ($q) => $q->latest())
            ->paginate(12);
    }

    #[Computed]
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    public function categories()
    {
        return Category::query()->published()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function activeCategory(): ?Category
    {
        if (! $this->categoryId) {
            return null;
        }

        return Category::query()->find($this->categoryId);
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-10">
        <p class="text-xs tracking-[0.25em] text-gold uppercase">Collection</p>
        <h1 class="mt-2 font-display text-4xl text-ivory">
            {{ $this->activeCategory?->name ?? 'Shop' }}
        </h1>
    </div>

    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('shop') }}" class="border px-3 py-1.5 text-xs tracking-wide uppercase {{ ! $this->categoryId ? 'border-gold text-gold' : 'border-line text-muted hover:border-gold' }}" wire:navigate>All</a>
            @foreach ($this->categories as $category)
                <a href="{{ route('shop.category', $category) }}" class="border px-3 py-1.5 text-xs tracking-wide uppercase {{ $this->categoryId === $category->id ? 'border-gold text-gold' : 'border-line text-muted hover:border-gold' }}" wire:navigate>
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Search pieces"
                class="border border-line bg-noir-elevated px-3 py-2 text-sm text-ivory placeholder:text-muted focus:border-gold focus:outline-none"
            >
            <select wire:model.live="sort" class="border border-line bg-noir-elevated px-3 py-2 text-sm text-ivory focus:border-gold focus:outline-none">
                <option value="newest">Newest</option>
                <option value="price_asc">Price: Low to high</option>
                <option value="price_desc">Price: High to low</option>
            </select>
        </div>
    </div>

    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($this->products as $product)
            <x-storefront.product-card :product="$product" />
        @empty
            <p class="col-span-full text-muted">No pieces match your filters.</p>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $this->products->links() }}
    </div>
</div>
