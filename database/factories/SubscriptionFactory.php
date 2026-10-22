<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'plan_id' => Plan::factory(),
            'plan_version_id' => null,
            'status' => 'active',
            'source' => 'manual',
            'billing_interval' => 'monthly',
            'started_at' => now(),
            'paid_through_at' => now()->addMonth(),
            'grace_until' => null,
            'cancelled_at' => null,
            'overrides' => null,
        ];
    }
}
