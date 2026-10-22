<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\ShopMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShopMembership>
 */
class ShopMembershipFactory extends Factory
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
            'user_id' => User::factory(),
            'role' => 'owner',
            'capabilities' => ['manage_catalogue', 'manage_branding'],
        ];
    }
}
