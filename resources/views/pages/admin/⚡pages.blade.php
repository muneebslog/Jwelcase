<?php

use App\Models\Page;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Pages')] class extends Component {
    public ?int $editingId = null;

    public string $title = '';

    public string $slug = '';

    public string $body = '';

    public bool $is_published = true;

    public string $seo_title = '';

    public string $seo_description = '';

    public function edit(int $id): void
    {
        $page = Page::query()->findOrFail($id);
        $this->editingId = $page->id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->body = (string) $page->body;
        $this->is_published = $page->is_published;
        $this->seo_title = (string) $page->seo_title;
        $this->seo_description = (string) $page->seo_description;
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'slug', 'body', 'seo_title', 'seo_description']);
        $this->is_published = true;
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug,'.($this->editingId ?? 'NULL')],
            'body' => ['nullable', 'string'],
            'is_published' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
        ]);

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'body' => $validated['body'] ?: null,
            'is_published' => $validated['is_published'],
            'seo_title' => $validated['seo_title'] ?: null,
            'seo_description' => $validated['seo_description'] ?: null,
        ];

        if ($this->editingId) {
            Page::query()->whereKey($this->editingId)->update($data);
        } else {
            Page::query()->create($data);
        }

        Flux::toast(variant: 'success', text: 'Page saved.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Page::query()->findOrFail($id)->delete();
        Flux::toast(variant: 'success', text: 'Page deleted.');
        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Page>
     */
    public function pages()
    {
        return Page::query()->latest()->get();
    }
}; ?>

<div class="grid gap-8 lg:grid-cols-2">
    <div>
        <flux:heading size="xl">Pages</flux:heading>
        <div class="mt-4 divide-y divide-zinc-200 rounded-xl border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
            @foreach ($this->pages() as $page)
                <div class="flex items-center justify-between gap-3 px-4 py-3" wire:key="page-{{ $page->id }}">
                    <div>
                        <p class="font-medium">{{ $page->title }}</p>
                        <p class="text-sm text-zinc-500">/{{ $page->slug }} · {{ $page->is_published ? 'Published' : 'Draft' }}</p>
                    </div>
                    <div class="space-x-3 text-sm">
                        <button type="button" wire:click="edit({{ $page->id }})" class="underline">Edit</button>
                        <button type="button" wire:click="delete({{ $page->id }})" wire:confirm="Delete page?" class="text-red-600">Delete</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:heading size="lg">{{ $editingId ? 'Edit page' : 'New page' }}</flux:heading>
        <flux:field>
            <flux:label>Title</flux:label>
            <flux:input wire:model.live="title" />
            <flux:error name="title" />
        </flux:field>
        <flux:field>
            <flux:label>Slug</flux:label>
            <flux:input wire:model="slug" />
            <flux:error name="slug" />
        </flux:field>
        <flux:field>
            <flux:label>Body</flux:label>
            <flux:textarea wire:model="body" rows="8" />
        </flux:field>
        <flux:field>
            <flux:label>SEO title</flux:label>
            <flux:input wire:model="seo_title" />
        </flux:field>
        <flux:field>
            <flux:label>SEO description</flux:label>
            <flux:input wire:model="seo_description" />
        </flux:field>
        <flux:checkbox wire:model="is_published" label="Published" />
        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">Save</flux:button>
            @if ($editingId)
                <flux:button type="button" wire:click="resetForm">Cancel</flux:button>
            @endif
        </div>
    </form>
</div>
