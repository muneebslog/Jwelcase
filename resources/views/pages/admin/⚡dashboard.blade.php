<?php

use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Admin')] class extends Component {
    public function productsCount(): int
    {
        return Product::query()->count();
    }

    public function publishedCount(): int
    {
        return Product::query()->published()->count();
    }

    public function categoriesCount(): int
    {
        return Category::query()->count();
    }

    public function newInquiriesCount(): int
    {
        return Inquiry::query()->where('status', 'new')->count();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Inquiry>
     */
    public function recentInquiries()
    {
        return Inquiry::query()->latest()->take(5)->get();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <div>
        <flux:heading size="xl">{{ __('Shop admin') }}</flux:heading>
        <flux:subheading>{{ __('Manage catalog, pages, and WhatsApp inquiries.') }}</flux:subheading>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <p class="text-sm text-zinc-500">Products</p>
            <p class="mt-2 text-3xl font-semibold">{{ $this->productsCount() }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <p class="text-sm text-zinc-500">Published</p>
            <p class="mt-2 text-3xl font-semibold">{{ $this->publishedCount() }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <p class="text-sm text-zinc-500">Categories</p>
            <p class="mt-2 text-3xl font-semibold">{{ $this->categoriesCount() }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <p class="text-sm text-zinc-500">New inquiries</p>
            <p class="mt-2 text-3xl font-semibold">{{ $this->newInquiriesCount() }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:heading size="lg">{{ __('Recent inquiries') }}</flux:heading>
        <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
            @forelse ($this->recentInquiries() as $inquiry)
                <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="flex items-center justify-between py-3 hover:opacity-80" wire:navigate>
                    <div>
                        <p class="font-medium">{{ $inquiry->customer_name ?: 'Guest' }}</p>
                        <p class="text-sm text-zinc-500">{{ $inquiry->created_at?->diffForHumans() }}</p>
                    </div>
                    <flux:badge>{{ $inquiry->status->label() }}</flux:badge>
                </a>
            @empty
                <p class="py-4 text-sm text-zinc-500">No inquiries yet.</p>
            @endforelse
        </div>
    </div>
</div>
