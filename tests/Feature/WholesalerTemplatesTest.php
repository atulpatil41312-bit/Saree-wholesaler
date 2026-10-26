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

    public function test_wholesaler_can_create_a_retailer_account_without_an_invitation(): void
    {
        $owner = User::factory()->create(['role' => 'wholesaler']);
        $shop = Shop::factory()->for($owner)->active()->create([
            'name' => 'Saanvi Wholesale',
            'slug' => 'saanvi-wholesale',
        ]);
        $pro = Plan::factory()->create([
            'key' => Plan::Pro,
            'shop_type' => 'wholesaler',
        ]);
        $proVersion = PlanVersion::factory()->for($pro)->create([
            'limits' => [
                'category_limit' => 50,
                'product_limit' => 1000,
                'product_image_limit' => 6,
                'sponsored_retailer_limit' => 2,
            ],
            'template_keys' => 'all',
        ]);
        $shop->subscriptions()->create([
            'plan_id' => $pro->id,
            'plan_version_id' => $proVersion->id,
            'status' => 'active',
            'source' => 'manual',
            'billing_interval' => 'manual',
            'started_at' => now(),
            'paid_through_at' => now()->addMonth(),
        ]);
        $retailerPlan = Plan::factory()->create([
            'key' => Plan::RetailerBasic,
            'shop_type' => 'retailer',
        ]);
        $retailerVersion = PlanVersion::factory()->for($retailerPlan)->create([
            'limits' => [
                'category_limit' => 5,
                'product_limit' => 20,
                'product_image_limit' => 2,
                'sponsored_retailer_limit' => 0,
            ],
            'template_keys' => [
                Shop::DEFAULT_TEMPLATE,
                Shop::WHOLESALE_ATELIER_TEMPLATE,
                Shop::ROYAL_DRAPE_TEMPLATE,
                Shop::SILK_ATELIER_TEMPLATE,
                Shop::HERITAGE_WEAVES_TEMPLATE,
            ],
        ]);

        $this->actingAs($owner)
            ->get(route('seller.sponsorships.index'))
            ->assertOk()
            ->assertSeeText('Create a retailer account')
            ->assertSee('name="retailer_phone"', false)
            ->assertSee('name="shop_name"', false)
            ->assertSee(route('seller.sponsorships.retailers.store'), false);

        $response = $this->actingAs($owner)
            ->from(route('seller.sponsorships.index'))
            ->post(route('seller.sponsorships.retailers.store'), [
                'retailer_name' => 'Nila Shah',
                'retailer_phone' => '+91 98765 43210',
                'retailer_email' => 'nila@example.test',
                'retailer_password' => 'a-secure-password',
                'retailer_password_confirmation' => 'a-secure-password',
                'shop_name' => 'Nila Retail Sarees',
                'slug' => 'nila-retail-sarees',
                'term_days' => 30,
                'template' => Shop::ROYAL_DRAPE_TEMPLATE,
            ]);

        $response->assertRedirect(route('seller.sponsorships.index'))
            ->assertSessionHas('status', 'Nila Retail Sarees retailer account created. Share the login details securely with the retailer.');

        $retailer = Shop::query()->where('slug', 'nila-retail-sarees')->firstOrFail();
        $this->assertSame('retailer', $retailer->type);
        $this->assertSame(Shop::ROYAL_DRAPE_TEMPLATE, $retailer->storefrontTemplate());
        $this->assertSame('+91 98765 43210', $retailer->phone);
        $this->assertSame('+91 98765 43210', $retailer->whatsapp);
        $this->assertSame(5, $retailer->category_limit);
        $this->assertSame(20, $retailer->product_limit);
        $this->assertDatabaseHas('users', [
            'email' => 'nila@example.test',
            'role' => 'retailer',
        ]);
        $this->assertDatabaseHas('sponsorships', [
            'sponsor_shop_id' => $shop->id,
            'retailer_shop_id' => $retailer->id,
            'invitation_id' => null,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'shop_id' => $retailer->id,
            'plan_id' => $retailerPlan->id,
            'source' => 'sponsorship',
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('invitations', 0);
        $this->assertSame($retailerVersion->id, $retailer->subscriptions()->value('plan_version_id'));
    }

    public function test_expired_subscription_shows_locked_design_actions_instead_of_broken_preview_links(): void
    {
        $owner = User::factory()->create(['role' => 'wholesaler']);
        $shop = Shop::factory()->for($owner)->active()->create([
            'name' => 'Renewal Sarees',
            'slug' => 'renewal-sarees',
        ]);
        $plan = Plan::factory()->create(['key' => 'expired-design-plan']);
        $version = PlanVersion::factory()->for($plan)->create([
            'template_keys' => [Shop::DEFAULT_TEMPLATE, Shop::ROYAL_NOIR_TEMPLATE],
        ]);
        $shop->subscriptions()->create([
            'plan_id' => $plan->id,
            'plan_version_id' => $version->id,
            'status' => 'active',
            'source' => 'manual',
            'billing_interval' => 'manual',
            'started_at' => now()->subMonth(),
            'paid_through_at' => now()->subDay(),
        ]);

        $this->actingAs($owner)
            ->get(route('seller.design'))
            ->assertOk()
            ->assertSeeText('Design access is paused')
            ->assertSeeText('Preview locked')
            ->assertSeeText('Renew plan to apply')
            ->assertDontSee(route('seller.design.preview', Shop::ROYAL_NOIR_TEMPLATE), false);
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
            ->assertSee('wm-whatsapp-float', false)
            ->assertSee('/templates/wholesaler-templates/models/look-2.jpg', false);

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
            'whatsapp' => '+91 (98765) 43210',
            'instagram_url' => 'https://instagram.com/restricted-demo',
            'facebook_url' => 'https://facebook.com/restricted-demo',
            'logo' => UploadedFile::fake()->image('logo.png', 240, 240),
            'banner' => UploadedFile::fake()->image('banner.jpg', 1600, 800),
        ])->assertRedirect(route('seller.profile.edit'));

        $shop->refresh();
        $this->assertNotNull($shop->logo_path);
        $this->assertNotNull($shop->banner_path);
        $this->assertSame('+91 (98765) 43210', $shop->whatsapp);
        $this->assertSame('https://instagram.com/restricted-demo', $shop->instagram_url);
        $this->assertSame('https://facebook.com/restricted-demo', $shop->facebook_url);
        Storage::disk('public')->assertExists($shop->logo_path);
        Storage::disk('public')->assertExists($shop->banner_path);
    }
}
