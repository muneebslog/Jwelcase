<?php

use App\Models\Category;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Categories')] class extends Component {
    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public int $sort_order = 0;

    public bool $is_published = true;

    public function edit(int $id): void
    {
        $category = Category::query()->findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = (string) $category->description;
        $this->sort_order = $category->sort_order;
        $this->is_published = $category->is_published;
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'slug', 'description', 'sort_order']);
        $this->is_published = true;
        $this->sort_order = 0;
    }

    public function updatedName(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:categories,slug,'.($this->editingId ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'sort_order' => ['integer', 'min:0'],
            'is_published' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?: null,
            'sort_order' => $validated['sort_order'],
            'is_published' => $validated['is_published'],
        ];

        if ($this->editingId) {
            Category::query()->whereKey($this->editingId)->update($data);
        } else {
            Category::query()->create($data);
        }

        Flux::toast(variant: 'success', text: 'Category saved.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Category::query()->findOrFail($id)->delete();
        Flux::toast(variant: 'success', text: 'Category deleted.');
        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    public function categories()
    {
        return Category::query()->orderBy('sort_order')->get();
    }
}; ?>

<div class="grid gap-8 lg:grid-cols-2">
    <div>
        <flux:heading size="xl">Categories</flux:heading>
        <div class="mt-4 divide-y divide-zinc-200 rounded-xl border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
            @foreach ($this->categories() as $category)
                <div class="flex items-center justify-between gap-3 px-4 py-3" wire:key="cat-{{ $category->id }}">
                    <div>
                        <p class="font-medium">{{ $category->name }}</p>
                        <p class="text-sm text-zinc-500">{{ $category->is_published ? 'Published' : 'Draft' }}</p>
                    </div>
                    <div class="space-x-3 text-sm">
                        <button type="button" wire:click="edit({{ $category->id }})" class="underline">Edit</button>
                        <button type="button" wire:click="delete({{ $category->id }})" wire:confirm="Delete category?" class="text-red-600">Delete</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <flux:heading size="lg">{{ $editingId ? 'Edit category' : 'New category' }}</flux:heading>
        <flux:field>
            <flux:label>Name</flux:label>
            <flux:input wire:model.live="name" />
            <flux:error name="name" />
        </flux:field>
        <flux:field>
            <flux:label>Slug</flux:label>
            <flux:input wire:model="slug" />
            <flux:error name="slug" />
        </flux:field>
        <flux:field>
            <flux:label>Description</flux:label>
            <flux:textarea wire:model="description" rows="3" />
        </flux:field>
        <flux:field>
            <flux:label>Sort order</flux:label>
            <flux:input type="number" wire:model="sort_order" />
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
