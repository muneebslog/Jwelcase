<?php

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Support\Money;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component {
    public Inquiry $inquiry;

    public function mount(Inquiry $inquiry): void
    {
        $this->inquiry = $inquiry->load('items');
    }

    public function updateStatus(string $status): void
    {
        $this->inquiry->update(['status' => InquiryStatus::from($status)]);
        $this->inquiry->refresh();
        Flux::toast(variant: 'success', text: 'Status updated.');
    }

    public function title(): string
    {
        return 'Inquiry #'.$this->inquiry->id;
    }
}; ?>

<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Inquiry #{{ $inquiry->id }}</flux:heading>
        <flux:button :href="route('admin.inquiries.index')" wire:navigate>Back</flux:button>
    </div>

    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <p><span class="text-zinc-500">Customer:</span> {{ $inquiry->customer_name ?: 'Guest' }}</p>
        <p class="mt-1"><span class="text-zinc-500">Phone:</span> {{ $inquiry->phone ?: '—' }}</p>
        <p class="mt-1"><span class="text-zinc-500">Note:</span> {{ $inquiry->note ?: '—' }}</p>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <select class="rounded border border-zinc-300 bg-transparent px-2 py-1 dark:border-zinc-600" wire:change="updateStatus($event.target.value)">
                @foreach (InquiryStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($inquiry->status === $status)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <a href="{{ $inquiry->whatsapp_url }}" target="_blank" rel="noopener" class="text-sm underline">Open WhatsApp message</a>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700">
        <div class="border-b border-zinc-200 px-4 py-3 font-medium dark:border-zinc-700">Items</div>
        <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
            @foreach ($inquiry->items as $item)
                <div class="flex items-center justify-between px-4 py-3 text-sm">
                    <div>
                        <p class="font-medium">{{ $item->product_name }}</p>
                        <p class="text-zinc-500">{{ $item->variant_label }} · Qty {{ $item->qty }}</p>
                    </div>
                    <p>{{ Money::format($item->unit_price * $item->qty) }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
