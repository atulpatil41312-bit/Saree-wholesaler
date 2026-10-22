<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\ShopSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShopSetting>
 */
class ShopSettingFactory extends Factory
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
            'template_key' => 'classic',
            'colour_key' => null,
            'font_key' => null,
            'sections' => ['hero', 'catalogue', 'contact'],
            'show_prices' => true,
        ];
    }
}
