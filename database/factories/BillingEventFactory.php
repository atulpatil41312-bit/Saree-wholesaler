<?php

namespace Database\Factories;

use App\Models\BillingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingEvent>
 */
class BillingEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'manual',
            'provider_event_id' => fake()->unique()->uuid(),
            'event_type' => 'subscription.renewed',
            'status' => 'received',
            'payload' => [],
            'processed_at' => null,
        ];
    }
}
