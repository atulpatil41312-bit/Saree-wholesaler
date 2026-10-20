<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company().' Sarees',
            'slug' => fake()->unique()->slug(3),
            'status' => 'pending',
            'email' => fake()->unique()->safeEmail(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'active',
        ]);
    }
}
