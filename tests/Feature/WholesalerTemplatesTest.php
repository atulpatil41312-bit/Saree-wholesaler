<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Plan;
use App\Models\PlanVersion;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WholesalerTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_wholesaler_can_see_wholesale_atelier_in_design_options(): void
    {
        $owner = User::factory()->create(['role' => 'wholesaler']);
        $shop = Shop::factory()->for($owner)->active()->create([
            'name' => 'Atelier Option Sarees',
            'slug' => 'atelier-option-sarees',
        ]);
        $plan = Plan::factory()->create(['key' => 'atelier-option-plan']);
        $version = PlanVersion::factory()->for($plan)->create([
            'template_keys' => [Shop::DEFAULT_TEMPLATE, Shop::WHOLESALE_ATELIER_TEMPLATE],
        ]);
        $shop->subscriptions()->create([
            'plan_id' => $plan->id,
            'plan_version_id' => $version->id,
            'status' => 'active',
            'source' => 'manual',
            'billing_interval' => 'manual',
            'started_at' => now(),
            'paid_through_at' => now()->addMonth(),
        ]);

        $this->actingAs($owner)
            ->get(route('seller.design'))
            ->assertOk()
            ->assertSeeText('Wholesale Atelier')
            ->assertSeeText('Apply Wholesale Atelier');
    }

    public function test_wholesale_atelier_preserves_catalogue_and_uses_shop_data(): void
    {
        $owner = User::factory()->create(['role' => 'wholesaler']);
        $shop = Shop::factory()->for($owner)->active()->create([
            'name' => 'Meera Wholesale Sarees',
            'slug' => 'meera-wholesale',
            'whatsapp' => '+91 (98765) 43210',
            'email' => 'orders@meera.test',
            'description' => 'Reliable festive and daily-wear saree sourcing.',
        ]);
        $shop->settings()->create(['template_key' => Shop::DEFAULT_TEMPLATE]);
        $category = Category::factory()->for($shop)->create([
            'name' => 'Festive Sarees',
            'slug' => 'festive-sarees',
        ]);
        $product = $shop->products()->create([
            'category_id' => $category->id,
            'name' => 'Ruby Woven Saree',
            'slug' => 'ruby-woven-saree',
            'code' => 'RW-101',
            'colour' => 'Ruby',
            'wholesale_price' => 1499,
            'minimum_order_quantity' => 6,
            'pack_size' => 12,
            'blouse_piece' => 'not_specified',
            'availability' => 'available',
        ]);

        $this->actingAs($owner)->put(route('seller.design.settings.update'), [
            'colour_key' => 'ruby',
            'font_key' => 'modern',
            'hero_title' => 'Curated sarees for thoughtful stores',
            'hero_subtitle' => 'A tested, retailer-friendly wholesale collection.',
            'hero_cta_label' => 'Browse the edit',
            'section_order' => 'story-featured-contact',
            'show_featured' => true,
            'show_story' => true,
            'show_contact' => true,
            'show_prices' => false,
        ])->assertRedirect(route('seller.design'));

        $this->actingAs($owner)->put(route('seller.design.update'), [
            'template' => Shop::WHOLESALE_ATELIER_TEMPLATE,
        ])->assertRedirect(route('seller.design'));

        $shop->refresh();
        $this->assertSame(Shop::WHOLESALE_ATELIER_TEMPLATE, $shop->storefrontTemplate());
        $this->assertSame(1, $shop->products()->count());
        $this->assertSame(['story', 'featured', 'contact'], $shop->settings()->value('sections')['order']);
        $this->assertFalse((bool) $shop->settings()->value('show_prices'));

        $this->get(route('shops.show', $shop))
            ->assertOk()
            ->assertSeeText('WHOLESALE ATELIER')
            ->assertSeeText('Curated sarees for thoughtful stores')
            ->assertSeeText($product->name)
            ->assertSeeText('Price on request')
            ->assertSee('https://wa.me/919876543210', false)
            ->assertSee('/templates/wholesaler-templates/placeholder-product.svg', false);

        $this->get(route('shops.products.show', [$shop, $product]))
            ->assertOk()
            ->assertSeeText($product->name)
            ->assertSeeText('Pack size')
            ->assertSeeText('12 pieces')
            ->assertSee('Product+code%3A+RW-101', false);
    }

    public function test_wholesale_atelier_supports_retailer_urls_and_hides_missing_contacts(): void
    {
        $owner = User::factory()->create(['role' => 'retailer']);
        $shop = Shop::factory()->for($owner)->active()->create([
            'type' => 'retailer',
            'name' => 'Nila Retail Sarees',
            'slug' => 'nila-retail',
            'whatsapp' => null,
            'email' => null,
            'phone' => null,
            'template' => Shop::WHOLESALE_ATELIER_TEMPLATE,
        ]);
        $shop->settings()->create([
            'template_key' => Shop::WHOLESALE_ATELIER_TEMPLATE,
            'show_prices' => true,
        ]);
        $category = Category::factory()->for($shop)->create([
            'name' => 'Everyday Sarees',
            'slug' => 'everyday-sarees',
        ]);
        $product = $shop->products()->create([
            'category_id' => $category->id,
            'name' => 'Indigo Cotton Saree',
            'slug' => 'indigo-cotton-saree',
            'code' => 'IC-201',
            'wholesale_price' => 999,
            'minimum_order_quantity' => 2,
            'blouse_piece' => 'not_specified',
            'availability' => 'on_enquiry',
        ]);

        $this->get(route('retailer.shops.show', $shop))
            ->assertOk()
            ->assertSeeText('Nila Retail Sarees')
            ->assertDontSeeText('Talk on WhatsApp')
            ->assertDontSee('wa.me/', false);

        $this->get(route('retailer.shops.products.show', [$shop, $product]))
            ->assertOk()
            ->assertSeeText('Indigo Cotton Saree')
            ->assertDontSeeText('Enquire on WhatsApp');
    }

    public function test_plan_template_restrictions_and_safe_branding_uploads_are_enforced(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'wholesaler']);
        $shop = Shop::factory()->for($owner)->active()->create([
            'name' => 'Restricted Sarees',
            'slug' => 'restricted-sarees',
        ]);
        $shop->settings()->create(['template_key' => Shop::DEFAULT_TEMPLATE]);
        $plan = Plan::factory()->create(['key' => 'restricted-template-plan']);
        $version = PlanVersion::factory()->for($plan)->create(['template_keys' => [Shop::DEFAULT_TEMPLATE]]);
        $shop->subscriptions()->create([
            'plan_id' => $plan->id,
            'plan_version_id' => $version->id,
            'status' => 'active',
            'source' => 'manual',
            'billing_interval' => 'manual',
            'started_at' => now(),
            'paid_through_at' => now()->addMonth(),
        ]);

        $this->actingAs($owner)
            ->from(route('seller.design'))
            ->put(route('seller.design.update'), ['template' => Shop::WHOLESALE_ATELIER_TEMPLATE])
            ->assertRedirect(route('seller.design'))
            ->assertSessionHasErrors('template');

        $this->assertSame(Shop::DEFAULT_TEMPLATE, $shop->fresh()->storefrontTemplate());

        $this->actingAs($owner)->put(route('seller.profile.update'), [
            'name' => $shop->name,
            'slug' => $shop->slug,
            'logo' => UploadedFile::fake()->image('logo.png', 240, 240),
            'banner' => UploadedFile::fake()->image('banner.jpg', 1600, 800),
        ])->assertRedirect(route('seller.profile.edit'));

        $shop->refresh();
        $this->assertNotNull($shop->logo_path);
        $this->assertNotNull($shop->banner_path);
        Storage::disk('public')->assertExists($shop->logo_path);
        Storage::disk('public')->assertExists($shop->banner_path);
    }
}
