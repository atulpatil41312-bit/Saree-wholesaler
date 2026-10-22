<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
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
            'invited_by' => User::factory(),
            'retailer_shop_id' => null,
            'email' => fake()->unique()->safeEmail(),
            'token_hash' => hash('sha256', fake()->uuid()),
            'term_days' => fake()->randomElement([30, 60, 90]),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
        ];
    }
}
