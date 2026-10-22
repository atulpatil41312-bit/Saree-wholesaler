<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'shop_id' => Shop::factory(),
            'amount_paise' => 0,
            'status' => 'recorded',
            'method' => 'manual',
            'reference' => fake()->unique()->uuid(),
            'recorded_by' => User::factory(),
            'paid_at' => now(),
            'metadata' => [],
        ];
    }
}
