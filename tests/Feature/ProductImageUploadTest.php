<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('admin can upload a product image without tmpfile errors', function () {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();
    ProductVariant::factory()->default()->create(['product_id' => $product->id]);

    $this->actingAs($admin);

    $file = UploadedFile::fake()->image('ring.jpg', 800, 800);

    Livewire::test('pages::admin.product-form', ['product' => $product])
        ->set('uploads', [$file])
        ->assertHasNoErrors()
        ->assertSet('pendingImagePaths', fn (array $paths): bool => count($paths) === 1)
        ->call('save')
        ->assertHasNoErrors();

    $product->refresh();

    expect($product->images)->toHaveCount(1);
    Storage::disk('public')->assertExists($product->images->first()->path);
});

test('php temporary directory is configured for the application', function () {
    $directory = realpath(storage_path('framework/tmp'));

    expect($directory)->not->toBeFalse()
        ->and(is_writable($directory))->toBeTrue()
        ->and(realpath((string) (getenv('TMP') ?: ($_SERVER['TMP'] ?? ''))))->toBe($directory);

    $handle = tmpfile();
    expect($handle)->not->toBeFalse();

    $meta = stream_get_meta_data($handle);
    fclose($handle);

    expect(realpath(dirname($meta['uri'])))->toBe($directory);
});
