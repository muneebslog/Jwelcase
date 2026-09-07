<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\InquiryItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InquiryItem>
 */
class InquiryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inquiry_id' => Inquiry::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'product_name' => fake()->words(3, true),
            'variant_label' => 'Metal: 18K Gold',
            'unit_price' => fake()->numberBetween(15000, 100000),
            'qty' => fake()->numberBetween(1, 3),
        ];
    }
}
