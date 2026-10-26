<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanVersion;
use App\Models\Shop;
use App\Models\Sponsorship;
use App\Models\User;
use App\Services\ManualBillingService;
use App\Services\SponsorshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WholeMarketPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_shops_use_type_scoped_canonical_urls_and_enforce_visibility(): void
    {
        $wholesalerPlan = $this->makePlan('basic', 'wholesaler', ['classic']);
        $retailerPlan = $this->makePlan('retailer-basic', 'retailer', ['classic']);
        $wholesaler = $this->makeEntitledShop('wholesaler', 'shared-sarees', 'Wholesaler Shared Sarees', $wholesalerPlan);
        $retailer = $this->makeEntitledShop('retailer', 'shared-sarees', 'Retailer Shared Sarees', $retailerPlan);

        $this->get('/wholesaler/shared-sarees')
            ->assertOk()
            ->assertSeeText('WHOLESALER SHARED SAREES');
        $this->get('/retailer/shared-sarees')
            ->assertOk()
            ->assertSeeText('RETAILER SHARED SAREES');
        $this->assertSame('/wholesaler/shared-sarees', parse_url($wholesaler->publicUrl(), PHP_URL_PATH));
        $this->assertSame('/retailer/shared-sarees', parse_url($retailer->publicUrl(), PHP_URL_PATH));

        $retailer->update(['suspended_at' => now(), 'suspension_reason' => 'Test suspension']);
        $this->get('/retailer/shared-sarees')
            ->assertNotFound()
            ->assertSeeText('currently unavailable')
            ->assertDontSeeText('Retailer Shared Sarees');
    }

    public function test_plan_template_entitlement_does_not_change_catalogue_data(): void
    {
        $plan = $this->makePlan('basic', 'wholesaler', ['classic'], ['product_limit' => 1, 'category_limit' => 1]);
        $shop = $this->makeEntitledShop('wholesaler', 'entitled-sarees', 'Entitled Sarees', $plan);
        $category = $shop->categories()->create(['name' => 'Festive', 'slug' => 'festive']);
        $product = $shop->products()->create([
            'category_id' => $category->id,
            'name' => 'Festival Silk Saree',
            'slug' => 'festival-silk-saree',
            'code' => 'FS-001',
            'price_paise' => 125000,
            'wholesale_price' => 1250,
            'minimum_order_quantity' => 1,
            'blouse_piece' => 'not_specified',
            'availability' => 'available',
        ]);

        $this->actingAs($shop->owner)
            ->from(route('seller.design'))
            ->put(route('seller.design.update'), ['template' => Shop::INDIGO_EDIT_TEMPLATE])
            ->assertRedirect(route('seller.design'))
            ->assertSessionHasErrors('template');
        $this->assertSame(Shop::DEFAULT_TEMPLATE, $shop->fresh()->storefrontTemplate());
        $this->assertModelExists($product);

        $this->actingAs($shop->owner)
            ->put(route('seller.design.update'), ['template' => Shop::DEFAULT_TEMPLATE])
            ->assertRedirect(route('seller.design'));
        $this->assertSame(1, $shop->fresh()->products()->count());
    }

    public function test_sponsorship_acceptance_is_quota_safe_and_expires_after_grace(): void
    {
        $proPlan = $this->makePlan('pro', 'wholesaler', 'all', ['sponsored_retailer_limit' => 1]);
        $this->makePlan('retailer-basic', 'retailer', ['classic']);
        $sponsor = $this->makeEntitledShop('wholesaler', 'pro-sponsor', 'Pro Sponsor', $proPlan);
        $service = app(SponsorshipService::class);

        [$invitation, $token] = $service->invite($sponsor, $sponsor->owner, 'retailer@example.test', 30);
        $this->assertSame($invitation->id, $service->invitationForToken($token)?->id);
        $retailerOwner = User::factory()->create(['email' => 'retailer@example.test', 'role' => 'retailer']);
        $retailer = $service->accept($invitation, $retailerOwner, [
            'name' => 'Retailer Sarees',
            'slug' => 'retailer-sarees',
            'template' => 'classic',
        ]);

        $this->assertSame('retailer', $retailer->type);
        $this->assertTrue($retailer->isPubliclyVisible());
        $this->assertDatabaseHas('sponsorships', ['sponsor_shop_id' => $sponsor->id, 'retailer_shop_id' => $retailer->id, 'status' => 'active']);

        [$secondInvitation] = $service->invite($sponsor, $sponsor->owner, 'other-retailer@example.test', 30);
        try {
            $service->accept($secondInvitation, User::factory()->create(['email' => 'other-retailer@example.test', 'role' => 'retailer']), [
                'name' => 'Other Retailer',
                'slug' => 'other-retailer',
                'template' => 'classic',
            ]);
            $this->fail('A sponsor must not exceed its active retailer slot limit.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $sponsorship = Sponsorship::query()->where('retailer_shop_id', $retailer->id)->sole();
        $sponsorship->update(['status' => 'grace', 'grace_until' => now()->subMinute()]);
        $service->expireDue(now());
        $this->assertSame('expired', $sponsorship->fresh()->status);
        $this->assertSame('expired', $retailer->subscriptions()->sole()->fresh()->status);
    }

    public function test_billing_event_recording_is_idempotent_and_pwa_does_not_cache_auth_or_dashboard_html(): void
    {
        $billing = app(ManualBillingService::class);
        $first = $billing->recordProviderEvent('test-provider', 'event-123', 'payment.received', ['amount' => 1]);
        $second = $billing->recordProviderEvent('test-provider', 'event-123', 'payment.received', ['amount' => 1]);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('billing_events', 1);

        $worker = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString('(admin|api|billing|dashboard', $worker);
        $this->assertStringContainsString('isProtectedOrMutablePath(url.pathname)', $worker);
        $this->assertStringContainsString('STATIC_CACHE', $worker);
        $this->assertStringContainsString('/storefront/contact-icons.css', $worker);
        $this->assertStringContainsString('isStaticAsset(url.pathname)', $worker);
    }

    /**
     * @param  array<int, string>|string  $templateKeys
     * @param  array<string, int>  $limitOverrides
     */
    private function makePlan(string $key, string $shopType, array|string $templateKeys, array $limitOverrides = []): Plan
    {
        $plan = Plan::factory()->create(['key' => $key, 'shop_type' => $shopType]);
        PlanVersion::factory()->for($plan)->create([
            'limits' => array_replace([
                'product_limit' => 20,
                'category_limit' => 5,
                'product_image_limit' => 2,
                'staff_limit' => 0,
                'sponsored_retailer_limit' => 0,
            ], $limitOverrides),
            'template_keys' => $templateKeys,
        ]);

        return $plan->fresh('currentVersion');
    }

    private function makeEntitledShop(string $type, string $slug, string $name, Plan $plan): Shop
    {
        $owner = User::factory()->create(['role' => $type]);
        $shop = Shop::factory()->for($owner)->active()->create([
            'type' => $type,
            'slug' => $slug,
            'name' => $name,
            'template' => Shop::DEFAULT_TEMPLATE,
        ]);
        $shop->memberships()->create([
            'user_id' => $owner->id,
            'role' => 'owner',
            'capabilities' => ['manage_catalogue', 'manage_branding', 'manage_billing'],
        ]);
        $shop->settings()->create(['template_key' => Shop::DEFAULT_TEMPLATE]);
        $shop->subscriptions()->create([
            'plan_id' => $plan->id,
            'plan_version_id' => $plan->currentVersion->id,
            'status' => 'active',
            'source' => 'manual',
            'billing_interval' => 'manual',
            'started_at' => now(),
            'paid_through_at' => now()->addMonth(),
        ]);

        return $shop->fresh(['owner', 'settings', 'subscriptions.planVersion']);
    }
}
