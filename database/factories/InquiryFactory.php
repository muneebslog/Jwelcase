<?php

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'note' => fake()->optional()->sentence(),
            'whatsapp_url' => 'https://wa.me/923001234567?text=hello',
            'status' => InquiryStatus::New,
        ];
    }
}
