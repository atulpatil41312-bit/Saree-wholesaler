<?php

namespace Database\Factories;

use App\Models\NotificationDelivery;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDelivery>
 */
class NotificationDeliveryFactory extends Factory
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
            'shop_id' => Shop::factory(),
            'channel' => 'mail',
            'notification_type' => 'sponsorship.expiry-reminder',
            'dedupe_key' => fake()->unique()->uuid(),
            'scheduled_for' => now(),
            'sent_at' => null,
            'failed_at' => null,
            'metadata' => [],
        ];
    }
}
