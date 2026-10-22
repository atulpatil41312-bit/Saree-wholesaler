<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanVersion>
 */
class PlanVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'version' => 1,
            'price_paise' => null,
            'billing_interval' => 'monthly',
            'limits' => [
                'category_limit' => 5,
                'product_limit' => 30,
                'product_image_limit' => 2,
                'sponsored_retailer_limit' => 0,
            ],
            'template_keys' => ['classic'],
            'active_from' => now(),
            'active_to' => null,
        ];
    }
}
