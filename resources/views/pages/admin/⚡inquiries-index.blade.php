<?php

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Inquiries')] class extends Component {
    use WithPagination;

    public function updateStatus(int $id, string $status): void
    {
        $inquiry = Inquiry::query()->findOrFail($id);
        $inquiry->update(['status' => InquiryStatus::from($status)]);
        Flux::toast(variant: 'success', text: 'Status updated.');
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Inquiry>
     */
    public function inquiries()
    {
        return Inquiry::query()->withCount('items')->latest()->paginate(20);
    }
}; ?>

<div class="space-y-6">
    <flux:heading size="xl">Inquiries</flux:heading>

    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="min-w-full text-sm">
            <thead class="bg-zinc-50 text-left dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($this->inquiries() as $inquiry)
                    <tr wire:key="inquiry-{{ $inquiry->id }}">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $inquiry->customer_name ?: 'Guest' }}</p>
                            <p class="text-zinc-500">{{ $inquiry->phone }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $inquiry->items_count }}</td>
                        <td class="px-4 py-3">
                            <select
                                class="rounded border border-zinc-300 bg-transparent px-2 py-1 dark:border-zinc-600"
                                wire:change="updateStatus({{ $inquiry->id }}, $event.target.value)"
                            >
                                @foreach (InquiryStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($inquiry->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-3">{{ $inquiry->created_at?->format('M j, Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="underline" wire:navigate>View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $this->inquiries()->links() }}
</div>
