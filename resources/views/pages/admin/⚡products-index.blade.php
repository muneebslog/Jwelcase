<?php

use App\Models\Product;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Products')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function togglePublished(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);
        $product->update(['is_published' => ! $product->is_published]);
        Flux::toast(variant: 'success', text: 'Product updated.');
    }

    public function delete(int $productId): void
    {
        Product::query()->findOrFail($productId)->delete();
        Flux::toast(variant: 'success', text: 'Product deleted.');
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Product>
     */
    public function products()
    {
        return Product::query()
            ->with('category')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(15);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">Products</flux:heading>
            <flux:subheading>Catalog and variants</flux:subheading>
        </div>
        <flux:button variant="primary" :href="route('admin.products.create')" wire:navigate>New product</flux:button>
    </div>

    <flux:input wire:model.live.debounce.300ms="search" placeholder="Search products" />

    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full text-sm">
            <thead class="bg-zinc-50 text-left dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($this->products() as $product)
                    <tr wire:key="product-{{ $product->id }}">
                        <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <button type="button" wire:click="togglePublished({{ $product->id }})" class="underline">
                                {{ $product->is_published ? 'Published' : 'Draft' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 dark:text-blue-400" wire:navigate>Edit</a>
                            <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="Delete this product?" class="text-red-600">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $this->products()->links() }}
</div>
