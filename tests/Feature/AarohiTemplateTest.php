<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AarohiTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_design_studio_groups_saree_and_dress_templates_without_changing_existing_ids(): void
    {
        $owner = User::factory()->create(['role' => 'retailer']);
        $shop = Shop::factory()->for($owner)->active()->create([
            'type' => 'retailer',
            'name' => 'Aarohi Test Boutique',
            'slug' => 'aarohi-test-boutique',
        ]);
        $shop->settings()->create(['template_key' => Shop::DEFAULT_TEMPLATE]);

        $this->actingAs($owner)
            ->get(route('seller.design'))
            ->assertOk()
            ->assertSeeText('Saree Templates')
            ->assertSeeText('Dress Templates')
            ->assertSeeText('AAROHI — Ethnic Fashion Boutique')
            ->assertSeeText('SHREE — Indian Ethnic Ready-to-Wear')
            ->assertSee('data-template-category="dresses"', false)
            ->assertSee('data-template-category="sarees"', false);

        $this->assertSame('sarees', Shop::templateCategories()[Shop::DEFAULT_TEMPLATE]);
        $this->assertSame('dresses', Shop::templateCategories()[Shop::AAROHI_TEMPLATE]);
        $this->assertSame('dresses', Shop::templateCategories()[Shop::SHREE_TEMPLATE]);
    }

    public function test_aarohi_renders_live_catalogue_for_retailers_and_wholesalers(): void
    {
        foreach (['retailer', 'wholesaler'] as $type) {
            $owner = User::factory()->create(['role' => $type]);
            $shop = Shop::factory()->for($owner)->active()->create([
                'type' => $type,
                'name' => 'Aarohi '.$type,
                'slug' => 'aarohi-'.$type,
                'template' => Shop::AAROHI_TEMPLATE,
            ]);
            $shop->settings()->create(['template_key' => Shop::AAROHI_TEMPLATE]);
            $category = $shop->categories()->create(['name' => 'Maxi Dresses', 'slug' => 'maxi-dresses']);
            $product = $shop->products()->create([
                'category_id' => $category->id,
                'name' => 'Gulmohar Tiered Dress',
                'slug' => 'gulmohar-tiered-dress-'.$type,
                'code' => 'AR-'.$type,
                'wholesale_price' => 1299,
                'minimum_order_quantity' => 6,
                'pack_size' => 'Set of 6',
                'availability' => 'available',
            ]);

            $this->get(route($type === 'retailer' ? 'retailer.shops.show' : 'shops.show', $shop))
                ->assertOk()
                ->assertSeeText('Gulmohar Tiered Dress')
                ->assertSeeText('Maxi Dresses')
                ->assertSeeText('AAROHI — Ethnic Fashion Boutique');

            $this->get(route($type === 'retailer' ? 'retailer.shops.products.show' : 'shops.products.show', [$shop, $product]))
                ->assertOk()
                ->assertSeeText('Gulmohar Tiered Dress')
                ->assertSeeText('Set of 6');
        }
    }

    public function test_shree_renders_live_catalogue_for_retailers_and_wholesalers(): void
    {
        foreach (['retailer', 'wholesaler'] as $type) {
            $owner = User::factory()->create(['role' => $type]);
            $shop = Shop::factory()->for($owner)->active()->create([
                'type' => $type,
                'name' => 'Shree '.$type,
                'slug' => 'shree-'.$type,
                'template' => Shop::SHREE_TEMPLATE,
            ]);
            $shop->settings()->create(['template_key' => Shop::SHREE_TEMPLATE]);
            $category = $shop->categories()->create(['name' => 'Festive Co-ords', 'slug' => 'festive-co-ords']);
            $product = $shop->products()->create([
                'category_id' => $category->id,
                'name' => 'Marigold Chanderi Set',
                'slug' => 'marigold-chanderi-set-'.$type,
                'code' => 'SH-'.$type,
                'wholesale_price' => 1599,
                'minimum_order_quantity' => 4,
                'pack_size' => 'Set of 4',
                'availability' => 'available',
            ]);

            $this->get(route($type === 'retailer' ? 'retailer.shops.show' : 'shops.show', $shop))
                ->assertOk()
                ->assertSeeText('Marigold Chanderi Set')
                ->assertSeeText('Festive Co-ords')
                ->assertSeeText('SHREE — Indian Ethnic Ready-to-Wear');

            $this->get(route($type === 'retailer' ? 'retailer.shops.products.show' : 'shops.products.show', [$shop, $product]))
                ->assertOk()
                ->assertSeeText('Marigold Chanderi Set')
                ->assertSeeText('Set of 4');
        }
    }
}
