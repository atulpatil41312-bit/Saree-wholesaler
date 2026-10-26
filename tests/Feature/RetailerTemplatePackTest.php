<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanVersion;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetailerTemplatePackTest extends TestCase
{
    use RefreshDatabase;

    public function test_retailer_design_studio_lists_the_imported_templates_but_wholesalers_do_not(): void
    {
        [$retailer, $owner] = $this->makeRetailer('pack-options', 'Pack Options Retailer');

        $this->actingAs($owner)
            ->get(route('seller.design'))
            ->assertOk()
            ->assertSeeText('Royal Drape')
            ->assertSeeText('Silk Atelier')
            ->assertSeeText('Heritage Weaves')
            ->assertSeeText('Preview design');

        [$wholesaler, $wholesalerOwner] = $this->makeWholesaler('pack-wholesaler', 'Pack Wholesaler');
        $this->actingAs($wholesalerOwner)
            ->get(route('seller.design'))
            ->assertOk()
            ->assertDontSeeText('Royal Drape')
            ->assertDontSeeText('Silk Atelier')
            ->assertDontSeeText('Heritage Weaves');
    }

    public function test_every_registered_template_has_a_model_worn_saree_preview_asset(): void
    {
        foreach (Shop::templateDetails() as $details) {
            $asset = public_path($details['asset_path'].'/'.$details['hero_image']);

            $this->assertFileExists($asset);
            $this->assertMatchesRegularExpression('/\.(?:jpe?g|png|webp)$/i', $asset);
        }
    }

    public function test_each_retailer_template_uses_live_shop_data_and_configured_whatsapp(): void
    {
        foreach ([Shop::ROYAL_DRAPE_TEMPLATE, Shop::SILK_ATELIER_TEMPLATE, Shop::HERITAGE_WEAVES_TEMPLATE] as $template) {
            [$shop] = $this->makeRetailer($template, 'Retailer '.$template, $template);
            $category = $shop->categories()->create(['name' => 'Wedding Edit', 'slug' => 'wedding-edit']);
            $product = $shop->products()->create([
                'category_id' => $category->id,
                'name' => 'Live '.$template.' Saree',
                'slug' => 'live-'.$template,
                'code' => 'RT-'.strtoupper(substr(str_replace('-', '', $template), 0, 3)),
                'wholesale_price' => 1999,
                'minimum_order_quantity' => 3,
                'blouse_piece' => 'not_specified',
                'availability' => 'available',
            ]);

            $response = $this->get(route('retailer.shops.show', $shop));

            $response->assertOk()
                ->assertSeeText(Shop::templateDetails()[$template]['label'])
                ->assertSeeText($shop->name)
                ->assertSeeText($category->name)
                ->assertSeeText($product->name)
                ->assertSee('https://wa.me/919876543210', false)
                ->assertSee('/templates/retailer-pack/models/look-3.jpg', false);

            $this->get(route('retailer.shops.products.show', [$shop, $product]))
                ->assertOk()
                ->assertSeeText($product->name)
                ->assertSee('https://wa.me/919876543210', false);
        }
    }

    public function test_retailer_preview_is_read_only_and_apply_persists_without_changing_catalogue(): void
    {
        [$shop, $owner] = $this->makeRetailer('switching-retailer', 'Switching Retailer');
        $category = $shop->categories()->create(['name' => 'Daily', 'slug' => 'daily']);
        $product = $shop->products()->create([
            'category_id' => $category->id,
            'name' => 'Daily Cotton',
            'slug' => 'daily-cotton',
            'code' => 'DC-001',
            'minimum_order_quantity' => 1,
            'blouse_piece' => 'not_specified',
            'availability' => 'on_enquiry',
        ]);

        $this->actingAs($owner)
            ->get(route('seller.design.preview', Shop::SILK_ATELIER_TEMPLATE))
            ->assertOk()
            ->assertSeeText('Silk Atelier')
            ->assertSeeText($product->name)
            ->assertSeeText('Previewing Silk Atelier');

        $this->assertSame(Shop::DEFAULT_TEMPLATE, $shop->fresh()->storefrontTemplate());

        $this->actingAs($owner)
            ->put(route('seller.design.update'), ['template' => Shop::HERITAGE_WEAVES_TEMPLATE])
            ->assertRedirect(route('seller.design'));

        $shop->refresh();
        $this->assertSame(Shop::HERITAGE_WEAVES_TEMPLATE, $shop->storefrontTemplate());
        $this->assertSame(1, $shop->categories()->count());
        $this->assertSame(1, $shop->products()->count());
        $this->assertModelExists($product);
    }

    public function test_retailer_template_hides_unconfigured_contact_links(): void
    {
        [$shop] = $this->makeRetailer('contactless-retailer', 'Contactless Retailer', Shop::ROYAL_DRAPE_TEMPLATE);
        $shop->update(['whatsapp' => null, 'email' => null, 'phone' => null]);

        $this->get(route('retailer.shops.show', $shop))
            ->assertOk()
            ->assertDontSee('wa.me/', false)
            ->assertDontSeeText('WhatsApp the retailer')
            ->assertDontSeeText('Email the retailer')
            ->assertDontSeeText('Call the retailer');
    }

    public function test_retailer_template_entitlement_and_public_visibility_remain_enforced(): void
    {
        [$shop, $owner] = $this->makeRetailer('restricted-retailer', 'Restricted Retailer', Shop::DEFAULT_TEMPLATE, [Shop::DEFAULT_TEMPLATE]);

        $this->actingAs($owner)
            ->from(route('seller.design'))
            ->put(route('seller.design.update'), ['template' => Shop::ROYAL_DRAPE_TEMPLATE])
            ->assertRedirect(route('seller.design'))
            ->assertSessionHasErrors('template');

        $shop->update(['suspended_at' => now()]);
        $this->get(route('retailer.shops.show', $shop))->assertNotFound();
        $this->actingAs($owner)
            ->get(route('seller.design.preview', Shop::ROYAL_DRAPE_TEMPLATE))
            ->assertForbidden();
    }

    /**
     * @param  array<int, string>|null  $templateKeys
     * @return array{0: Shop, 1: User}
     */
    private function makeRetailer(string $slug, string $name, string $template = Shop::DEFAULT_TEMPLATE, ?array $templateKeys = null): array
    {
        $owner = User::factory()->create(['role' => 'retailer']);
        $shop = Shop::factory()->for($owner)->active()->create([
            'type' => 'retailer',
            'slug' => $slug,
            'name' => $name,
            'template' => $template,
            'whatsapp' => '+91 (98765) 43210',
        ]);
        $shop->memberships()->create([
            'user_id' => $owner->id,
            'role' => 'owner',
            'capabilities' => ['manage_catalogue', 'manage_branding', 'manage_billing'],
        ]);
        $shop->settings()->create(['template_key' => $template]);
        $plan = Plan::factory()->create(['key' => 'retailer-pack-'.$slug, 'shop_type' => 'retailer']);
        $version = PlanVersion::factory()->for($plan)->create([
            'template_keys' => $templateKeys ?? [
                Shop::DEFAULT_TEMPLATE,
                Shop::WHOLESALE_ATELIER_TEMPLATE,
                Shop::ROYAL_DRAPE_TEMPLATE,
                Shop::SILK_ATELIER_TEMPLATE,
                Shop::HERITAGE_WEAVES_TEMPLATE,
            ],
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

        return [$shop->fresh(['owner', 'settings', 'subscriptions.planVersion']), $owner];
    }

    /** @return array{0: Shop, 1: User} */
    private function makeWholesaler(string $slug, string $name): array
    {
        $owner = User::factory()->create(['role' => 'seller']);
        $shop = Shop::factory()->for($owner)->active()->create([
            'type' => 'wholesaler',
            'slug' => $slug,
            'name' => $name,
        ]);
        $shop->memberships()->create([
            'user_id' => $owner->id,
            'role' => 'owner',
            'capabilities' => ['manage_catalogue', 'manage_branding', 'manage_billing'],
        ]);

        return [$shop, $owner];
    }
}
