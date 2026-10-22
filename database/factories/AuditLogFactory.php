<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
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
            'action' => 'shop.updated',
            'subject_type' => null,
            'subject_id' => null,
            'context' => [],
            'ip_address' => '127.0.0.1',
        ];
    }
}
