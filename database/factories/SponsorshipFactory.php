<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\Sponsorship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sponsorship>
 */
class SponsorshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sponsor_shop_id' => Shop::factory(),
            'retailer_shop_id' => Shop::factory(),
            'invitation_id' => null,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
            'grace_until' => null,
            'independently_paid_at' => null,
            'ended_at' => null,
            'reason' => null,
        ];
    }
}
