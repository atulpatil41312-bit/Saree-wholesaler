<?php

namespace Database\Factories;

use App\Models\GeneratedTemplateDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeneratedTemplateDraft>
 */
class GeneratedTemplateDraftFactory extends Factory
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
            'stitch_project_id' => '4044680601076201931',
            'screen_id' => fake()->uuid(),
            'title' => fake()->sentence(3),
            'prompt' => fake()->sentence(12),
            'device_type' => 'DESKTOP',
            'status' => 'ready',
            'html_path' => null,
            'image_path' => null,
            'error_message' => null,
        ];
    }
}
