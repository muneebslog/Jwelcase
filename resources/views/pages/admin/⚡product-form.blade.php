<?php

use App\Enums\StockStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public ?Product $product = null;

    public string $name = '';

    public string $slug = '';

    public ?int $category_id = null;

    public string $short_description = '';

    public string $description = '';

    public bool $is_featured = false;

    public bool $is_published = false;

    public string $seo_title = '';

    public string $seo_description = '';

    /** @var array<int, array{name: string, values: string}> */
    public array $options = [];

    /** @var array<int, array{sku: string, price: string, compare_at_price: string, stock_status: string, is_default: bool, option_value_ids: array<int>}> */
    public array $variants = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploads = [];

    /** @var array<int, string> */
    public array $pendingImagePaths = [];

    public function mount(?Product $product = null): void
    {
        if ($product?->exists) {
            $this->product = $product->load(['options.values', 'variants.optionValues', 'images']);
            $this->name = $product->name;
            $this->slug = $product->slug;
            $this->category_id = $product->category_id;
            $this->short_description = (string) $product->short_description;
            $this->description = (string) $product->description;
            $this->is_featured = $product->is_featured;
            $this->is_published = $product->is_published;
            $this->seo_title = (string) $product->seo_title;
            $this->seo_description = (string) $product->seo_description;

            $this->options = $product->options->map(fn (ProductOption $option): array => [
                'name' => $option->name,
                'values' => $option->values->pluck('value')->implode(', '),
            ])->values()->all();

            $this->variants = $product->variants->map(fn (ProductVariant $variant): array => [
                'sku' => (string) $variant->sku,
                'price' => (string) $variant->price,
                'compare_at_price' => (string) ($variant->compare_at_price ?? ''),
                'stock_status' => $variant->stock_status->value,
                'is_default' => $variant->is_default,
                'option_value_ids' => $variant->optionValues->pluck('id')->all(),
                'label' => $variant->label(),
            ])->values()->all();
        } else {
            $this->options = [
                ['name' => 'Metal', 'values' => '18K Gold, Rose Gold, Sterling Silver'],
                ['name' => 'Size', 'values' => '6, 7, 8'],
            ];
        }
    }

    public function updatedUploads(): void
    {
        if ($this->uploads === []) {
            return;
        }

        $this->validate([
            'uploads.*' => ['required', 'image', 'max:5120'],
        ]);

        foreach ($this->uploads as $upload) {
            $this->pendingImagePaths[] = $upload->store('products', 'public');
        }

        $this->uploads = [];
    }

    public function removePendingImage(int $index): void
    {
        if (! isset($this->pendingImagePaths[$index])) {
            return;
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($this->pendingImagePaths[$index]);
        unset($this->pendingImagePaths[$index]);
        $this->pendingImagePaths = array_values($this->pendingImagePaths);
    }

    public function removeSavedImage(int $imageId): void
    {
        if (! $this->product) {
            return;
        }

        $image = $this->product->images()->whereKey($imageId)->first();

        if (! $image) {
            return;
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
        $image->delete();
        $this->product->load('images');
    }

    public function updatedName(string $value): void
    {
        if (! $this->product) {
            $this->slug = Str::slug($value);
        }
    }

    public function addOption(): void
    {
        $this->options[] = ['name' => '', 'values' => ''];
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function generateVariants(): void
    {
        $parsed = [];

        foreach ($this->options as $option) {
            $name = trim($option['name']);
            $values = collect(explode(',', $option['values']))
                ->map(fn (string $v): string => trim($v))
                ->filter()
                ->values()
                ->all();

            if ($name === '' || $values === []) {
                continue;
            }

            $parsed[] = ['name' => $name, 'values' => $values];
        }

        if ($parsed === []) {
            Flux::toast(variant: 'danger', text: 'Add at least one option with values.');

            return;
        }

        $combinations = [[]];

        foreach ($parsed as $option) {
            $next = [];
            foreach ($combinations as $combo) {
                foreach ($option['values'] as $value) {
                    $next[] = array_merge($combo, [$option['name'].': '.$value]);
                }
            }
            $combinations = $next;
        }

        $basePrice = $this->variants[0]['price'] ?? '50000';

        $this->variants = collect($combinations)->values()->map(function (array $labels, int $index) use ($basePrice): array {
            return [
                'sku' => strtoupper(Str::slug($this->name ?: 'item')).'-'.($index + 1),
                'price' => (string) $basePrice,
                'compare_at_price' => '',
                'stock_status' => StockStatus::InStock->value,
                'is_default' => $index === 0,
                'option_value_ids' => [],
                'label' => implode(', ', $labels),
            ];
        })->all();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug,'.($this->product?->id ?? 'NULL')],
            'category_id' => ['nullable', 'exists:categories,id'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'options' => ['array'],
            'options.*.name' => ['required', 'string', 'max:100'],
            'options.*.values' => ['required', 'string'],
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['required', 'integer', 'min:0'],
            'variants.*.compare_at_price' => ['nullable', 'integer', 'min:0'],
            'variants.*.stock_status' => ['required', 'in:in_stock,made_to_order,out_of_stock'],
            'variants.*.is_default' => ['boolean'],
            'pendingImagePaths' => ['array'],
            'pendingImagePaths.*' => ['string'],
        ]);

        DB::transaction(function () use ($validated): void {
            $product = $this->product ?? new Product;
            $product->fill([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'category_id' => $validated['category_id'],
                'short_description' => $validated['short_description'] ?: null,
                'description' => $validated['description'] ?: null,
                'is_featured' => $validated['is_featured'],
                'is_published' => $validated['is_published'],
                'seo_title' => $validated['seo_title'] ?: null,
                'seo_description' => $validated['seo_description'] ?: null,
            ]);
            $product->save();

            $product->variants()->each(function (ProductVariant $variant): void {
                $variant->optionValues()->detach();
                $variant->delete();
            });
            $product->options()->each(function (ProductOption $option): void {
                $option->values()->delete();
                $option->delete();
            });

            $valueMap = [];

            foreach ($this->options as $optionIndex => $optionData) {
                $option = $product->options()->create([
                    'name' => trim($optionData['name']),
                    'position' => $optionIndex,
                ]);

                $values = collect(explode(',', $optionData['values']))
                    ->map(fn (string $v): string => trim($v))
                    ->filter()
                    ->values();

                foreach ($values as $valueIndex => $value) {
                    $created = $option->values()->create([
                        'value' => $value,
                        'position' => $valueIndex,
                    ]);
                    $valueMap[$option->name.': '.$value] = $created->id;
                }
            }

            $hasDefault = false;

            foreach ($this->variants as $variantData) {
                $isDefault = (bool) ($variantData['is_default'] ?? false) && ! $hasDefault;
                if ($isDefault) {
                    $hasDefault = true;
                }

                $variant = $product->variants()->create([
                    'sku' => $variantData['sku'] ?: null,
                    'price' => (int) $variantData['price'],
                    'compare_at_price' => $variantData['compare_at_price'] !== '' ? (int) $variantData['compare_at_price'] : null,
                    'stock_status' => $variantData['stock_status'],
                    'is_default' => $isDefault,
                ]);

                $ids = [];
                if (! empty($variantData['label'])) {
                    foreach (explode(', ', $variantData['label']) as $labelPart) {
                        if (isset($valueMap[$labelPart])) {
                            $ids[] = $valueMap[$labelPart];
                        }
                    }
                }

                if ($ids !== []) {
                    $variant->optionValues()->attach($ids);
                }
            }

            if (! $hasDefault && $product->variants()->exists()) {
                $product->variants()->first()?->update(['is_default' => true]);
            }

            foreach ($this->pendingImagePaths as $index => $path) {
                $product->images()->create([
                    'path' => $path,
                    'alt' => $product->name,
                    'position' => $product->images()->count() + $index,
                ]);
            }

            $this->pendingImagePaths = [];
            $this->product = $product->fresh(['images', 'options.values', 'variants.optionValues']);
        });

        Flux::toast(variant: 'success', text: 'Product saved.');

        $this->redirect(route('admin.products.edit', $this->product), navigate: true);
    }

    public function title(): string
    {
        return $this->product ? 'Edit product' : 'New product';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    public function categories()
    {
        return Category::query()->orderBy('sort_order')->get();
    }
}; ?>

<div class="mx-auto flex w-full max-w-4xl flex-col gap-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ $this->product ? 'Edit product' : 'New product' }}</flux:heading>
        <flux:button :href="route('admin.products.index')" wire:navigate>Back</flux:button>
    </div>

    <form wire:submit="save" class="space-y-8">
        <div class="grid gap-4 md:grid-cols-2">
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
                <flux:label>Category</flux:label>
                <flux:select wire:model="category_id">
                    <option value="">None</option>
                    @foreach ($this->categories() as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>
            </flux:field>
            <div class="flex items-center gap-6 pt-6">
                <flux:checkbox wire:model="is_published" label="Published" />
                <flux:checkbox wire:model="is_featured" label="Featured" />
            </div>
        </div>

        <flux:field>
            <flux:label>Short description</flux:label>
            <flux:textarea wire:model="short_description" rows="2" />
        </flux:field>

        <flux:field>
            <flux:label>Description</flux:label>
            <flux:textarea wire:model="description" rows="5" />
        </flux:field>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Options</flux:heading>
                <flux:button type="button" wire:click="addOption" size="sm">Add option</flux:button>
            </div>
            @foreach ($options as $index => $option)
                <div class="grid gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 md:grid-cols-[1fr_2fr_auto]" wire:key="option-{{ $index }}">
                    <flux:input wire:model="options.{{ $index }}.name" placeholder="Option name (Metal)" />
                    <flux:input wire:model="options.{{ $index }}.values" placeholder="Values comma-separated" />
                    <flux:button type="button" wire:click="removeOption({{ $index }})" variant="danger" size="sm">Remove</flux:button>
                </div>
            @endforeach
            <flux:button type="button" wire:click="generateVariants">Generate variants from options</flux:button>
        </div>

        <div class="space-y-4">
            <flux:heading size="lg">Variants</flux:heading>
            @error('variants') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
            @foreach ($variants as $index => $variant)
                <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" wire:key="variant-{{ $index }}">
                    <p class="text-sm font-medium">{{ $variant['label'] ?? 'Variant '.($index + 1) }}</p>
                    <div class="grid gap-3 md:grid-cols-4">
                        <flux:input wire:model="variants.{{ $index }}.sku" placeholder="SKU" />
                        <flux:input type="number" wire:model="variants.{{ $index }}.price" placeholder="Price" />
                        <flux:input type="number" wire:model="variants.{{ $index }}.compare_at_price" placeholder="Compare at" />
                        <flux:select wire:model="variants.{{ $index }}.stock_status">
                            @foreach (\App\Enums\StockStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    <flux:checkbox wire:model="variants.{{ $index }}.is_default" label="Default variant" />
                    <input type="hidden" wire:model="variants.{{ $index }}.label">
                </div>
            @endforeach
        </div>

        <div class="space-y-3">
            <flux:heading size="lg">Images</flux:heading>
            <div class="grid grid-cols-4 gap-3">
                @if ($this->product)
                    @foreach ($this->product->images as $image)
                        <div class="relative" wire:key="saved-image-{{ $image->id }}">
                            <img src="{{ $image->url() }}" alt="" class="aspect-square rounded object-cover">
                            <button type="button" wire:click="removeSavedImage({{ $image->id }})" class="absolute top-1 right-1 rounded bg-black/70 px-2 py-0.5 text-xs text-white">Remove</button>
                        </div>
                    @endforeach
                @endif
                @foreach ($pendingImagePaths as $index => $path)
                    <div class="relative" wire:key="pending-image-{{ $index }}">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" alt="" class="aspect-square rounded object-cover">
                        <button type="button" wire:click="removePendingImage({{ $index }})" class="absolute top-1 right-1 rounded bg-black/70 px-2 py-0.5 text-xs text-white">Remove</button>
                    </div>
                @endforeach
            </div>
            <input type="file" wire:model="uploads" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="block w-full text-sm">
            <div wire:loading wire:target="uploads" class="text-sm text-zinc-500">Uploading…</div>
            @error('uploads.*') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
            @error('uploads') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>SEO title</flux:label>
                <flux:input wire:model="seo_title" />
            </flux:field>
            <flux:field>
                <flux:label>SEO description</flux:label>
                <flux:input wire:model="seo_description" />
            </flux:field>
        </div>

        <flux:button type="submit" variant="primary">Save product</flux:button>
    </form>
</div>
