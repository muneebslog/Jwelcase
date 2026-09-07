<?php

use App\Enums\StockStatus;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Cart\Cart;
use Livewire\Livewire;

function makePublishedProductWithVariants(): Product
{
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'is_published' => true,
    ]);

    $metal = ProductOption::factory()->create([
        'product_id' => $product->id,
        'name' => 'Metal',
        'position' => 0,
    ]);

    $gold = ProductOptionValue::factory()->create([
        'product_option_id' => $metal->id,
        'value' => '18K Gold',
        'position' => 0,
    ]);

    $silver = ProductOptionValue::factory()->create([
        'product_option_id' => $metal->id,
        'value' => 'Sterling Silver',
        'position' => 1,
    ]);

    $goldVariant = ProductVariant::factory()->default()->create([
        'product_id' => $product->id,
        'price' => 50000,
        'stock_status' => StockStatus::InStock,
        'sku' => 'TEST-GOLD',
    ]);
    $goldVariant->optionValues()->attach($gold->id);

    $silverVariant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 35000,
        'stock_status' => StockStatus::InStock,
        'sku' => 'TEST-SILVER',
    ]);
    $silverVariant->optionValues()->attach($silver->id);

    return $product->fresh(['options.values', 'variants.optionValues']);
}

test('home page renders', function () {
    $this->get(route('home'))->assertOk();
});

test('unpublished products are hidden from the shop', function () {
    $published = Product::factory()->create(['is_published' => true, 'name' => 'Visible Ring']);
    ProductVariant::factory()->default()->create(['product_id' => $published->id]);

    $hidden = Product::factory()->unpublished()->create(['name' => 'Hidden Ring']);
    ProductVariant::factory()->default()->create(['product_id' => $hidden->id]);

    Livewire::test('pages::shop')
        ->assertSee('Visible Ring')
        ->assertDontSee('Hidden Ring');
});

test('product page resolves variants and adds to cart', function () {
    $product = makePublishedProductWithVariants();
    $goldVariant = $product->variants->firstWhere('sku', 'TEST-GOLD');

    Livewire::test('pages::product', ['product' => $product])
        ->assertSee($product->name)
        ->call('addToCart')
        ->assertHasNoErrors();

    expect(app(Cart::class)->count())->toBe(1);
    expect(app(Cart::class)->items()->first()->variant->is($goldVariant))->toBeTrue();
});

test('cart can create a whatsapp inquiry', function () {
    $product = makePublishedProductWithVariants();
    $variant = $product->variants->firstWhere('is_default', true);

    app(Cart::class)->add($variant, 2);

    Livewire::test('pages::cart')
        ->set('customer_name', 'Ayesha')
        ->set('phone', '03001234567')
        ->set('note', 'Prefer rose finish if possible')
        ->call('sendOnWhatsApp')
        ->assertRedirect();

    $this->assertDatabaseHas('inquiries', [
        'customer_name' => 'Ayesha',
        'phone' => '03001234567',
    ]);

    $this->assertDatabaseHas('inquiry_items', [
        'product_name' => $product->name,
        'qty' => 2,
        'unit_price' => $variant->price,
    ]);

    expect(app(Cart::class)->isEmpty())->toBeTrue();
});

test('admin middleware blocks non admins from products', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.products.index'))
        ->assertForbidden();
});

test('admin can open product create form', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.products.create'))
        ->assertOk();
});

test('admin can create a cms page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test('pages::admin.pages')
        ->set('title', 'Care Guide')
        ->set('slug', 'care-guide')
        ->set('body', 'Polish gently with a soft cloth.')
        ->set('is_published', true)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('pages', [
        'slug' => 'care-guide',
        'is_published' => true,
    ]);

    $this->get(route('pages.show', 'care-guide'))->assertOk()->assertSee('Care Guide');
});

test('unpublished cms pages return not found', function () {
    Page::factory()->unpublished()->create([
        'title' => 'Secret',
        'slug' => 'secret',
    ]);

    $this->get(route('pages.show', 'secret'))->assertNotFound();
});
