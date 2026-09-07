<?php

namespace Database\Seeders;

use App\Enums\StockStatus;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\SiteSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@jwelcase.test',
            'password' => 'password',
        ]);

        app(SiteSettings::class)->setMany([
            'shop_name' => 'Jwelcase',
            'whatsapp_number' => '923001234567',
            'currency' => 'PKR',
            'address' => 'Lahore, Pakistan — by appointment',
            'instagram' => 'https://instagram.com/jwelcase',
            'hero_headline' => 'Jewelry that holds a story',
            'hero_subline' => 'Select your piece, send on WhatsApp, and we\'ll craft the rest with you.',
            'hero_cta_label' => 'Explore the collection',
            'about_teaser' => 'Jwelcase is a modern atelier for gold and silver pieces designed for everyday elegance and celebration.',
        ]);

        $categories = collect([
            'Rings',
            'Necklaces',
            'Earrings',
            'Bracelets',
        ])->map(function (string $name, int $index): Category {
            return Category::query()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Curated {$name} for every occasion.",
                'sort_order' => $index,
                'is_published' => true,
            ]);
        });

        $catalog = [
            ['Aurora Solitaire Ring', 'Rings', 85000, true],
            ['Noir Halo Band', 'Rings', 72000, true],
            ['Lumen Pendant Necklace', 'Necklaces', 95000, true],
            ['Cascade Chain', 'Necklaces', 64000, false],
            ['Crescent Drop Earrings', 'Earrings', 48000, true],
            ['Velvet Cuff Bracelet', 'Bracelets', 56000, false],
        ];

        foreach ($catalog as [$name, $categoryName, $basePrice, $featured]) {
            /** @var Category $category */
            $category = $categories->firstWhere('name', $categoryName);

            $product = Product::query()->create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'short_description' => 'A signature Jwelcase piece finished by hand.',
                'description' => "The {$name} balances clean lines with luminous metalwork. Choose your metal and size, then inquire on WhatsApp for availability and finishing.",
                'is_featured' => $featured,
                'is_published' => true,
            ]);

            $metal = ProductOption::query()->create([
                'product_id' => $product->id,
                'name' => 'Metal',
                'position' => 0,
            ]);

            $size = ProductOption::query()->create([
                'product_id' => $product->id,
                'name' => 'Size',
                'position' => 1,
            ]);

            $metals = collect(['18K Gold', 'Rose Gold', 'Sterling Silver'])->map(
                fn (string $value, int $i): ProductOptionValue => ProductOptionValue::query()->create([
                    'product_option_id' => $metal->id,
                    'value' => $value,
                    'position' => $i,
                ]),
            );

            $sizes = collect(['6', '7', '8'])->map(
                fn (string $value, int $i): ProductOptionValue => ProductOptionValue::query()->create([
                    'product_option_id' => $size->id,
                    'value' => $value,
                    'position' => $i,
                ]),
            );

            $isFirst = true;

            foreach ($metals as $metalIndex => $metalValue) {
                foreach ($sizes as $sizeValue) {
                    $priceAdjust = $metalIndex * 8000;
                    $variant = ProductVariant::query()->create([
                        'product_id' => $product->id,
                        'sku' => strtoupper(Str::slug($name).'-'.Str::slug($metalValue->value).'-'.$sizeValue->value),
                        'price' => $basePrice + $priceAdjust,
                        'compare_at_price' => null,
                        'stock_status' => StockStatus::InStock,
                        'is_default' => $isFirst,
                    ]);

                    $variant->optionValues()->attach([$metalValue->id, $sizeValue->id]);
                    $isFirst = false;
                }
            }
        }

        Page::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'body' => "Jwelcase began as a quiet studio practice — designing jewelry meant to be worn daily and treasured for years.\n\nEvery piece can be finished in multiple metals and sizes. Browse the collection, add what you love to your cart, and send your inquiry on WhatsApp. We'll confirm availability, customizations, and delivery personally.",
            'is_published' => true,
        ]);

        Page::query()->create([
            'title' => 'Contact',
            'slug' => 'contact',
            'body' => "Prefer to talk through a piece before ordering? Message us on WhatsApp or visit by appointment.\n\nWe typically reply within a few hours during business days.",
            'is_published' => true,
        ]);
    }
}
