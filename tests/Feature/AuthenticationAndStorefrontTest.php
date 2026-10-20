<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationAndStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_sign_in_and_reach_the_dashboard(): void
    {
        $seller = User::factory()->create([
            'email' => 'seller@example.test',
            'password' => 'password',
            'role' => 'seller',
        ]);
        Shop::factory()->for($seller)->create();

        $response = $this->followingRedirects()->post('/login', [
            'email' => 'seller@example.test',
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertSeeText('Your catalogue');
        $this->assertAuthenticatedAs($seller);
    }

    public function test_active_shop_page_renders(): void
    {
        $shop = Shop::factory()->active()->create([
            'name' => 'Test Sarees',
            'slug' => 'test-sarees',
        ]);

        $response = $this->get(route('shops.show', $shop));

        $response->assertOk();
        $response->assertSeeText('TEST SAREES');
    }

    public function test_authenticated_seller_sees_a_dashboard_link_on_the_marketplace(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $response = $this->actingAs($seller)->get('/');

        $response->assertOk();
        $response->assertSeeText('Seller dashboard');
    }

    public function test_seller_can_apply_for_a_pending_shop(): void
    {
        $response = $this->post(route('seller.signup.store'), [
            'seller_name' => 'Aarohi Shah',
            'seller_email' => 'aarohi@example.test',
            'seller_password' => 'a-secure-password',
            'seller_password_confirmation' => 'a-secure-password',
            'name' => 'Aarohi Sarees',
            'slug' => 'aarohi-sarees',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $seller = User::query()->where('email', 'aarohi@example.test')->sole();

        $this->assertModelExists($seller);
        $this->assertSame('seller', $seller->role);
        $this->assertDatabaseHas('shops', [
            'user_id' => $seller->id,
            'name' => 'Aarohi Sarees',
            'slug' => 'aarohi-sarees',
            'status' => 'pending',
        ]);
        $this->get('/shops/aarohi-sarees')->assertNotFound();
    }

    public function test_seller_signup_validates_required_details(): void
    {
        $response = $this->from(route('seller.signup'))->post(route('seller.signup.store'), []);

        $response->assertRedirect(route('seller.signup'));
        $response->assertSessionHasErrors([
            'seller_name',
            'seller_email',
            'seller_password',
            'name',
            'slug',
        ]);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('shops', 0);
    }

    #[DataProvider('storefrontTemplates')]
    public function test_seller_can_switch_between_storefront_templates_without_changing_catalogue_data(string $template, string $pageMarker, string $assetPath): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $shop = Shop::factory()->for($seller)->active()->create(['template' => Shop::DEFAULT_TEMPLATE]);
        $category = $shop->categories()->create(['name' => 'Festive', 'slug' => 'festive', 'sort_order' => 1]);
        $product = $shop->products()->create([
            'category_id' => $category->id,
            'name' => 'Royal Emerald Saree',
            'slug' => 'royal-emerald-saree',
            'code' => 'VE-001',
            'wholesale_price' => 1250,
            'minimum_order_quantity' => 6,
            'blouse_piece' => 'not_specified',
            'availability' => 'available',
        ]);
        $otherSeller = User::factory()->create(['role' => 'seller']);
        $otherShop = Shop::factory()->for($otherSeller)->active()->create(['template' => Shop::DEFAULT_TEMPLATE]);

        $this->actingAs($seller)->get(route('seller.design'))
            ->assertOk()
            ->assertSeeText('WholeMarket Classic')
            ->assertSeeText('Vastra Emerald')
            ->assertSeeText('Royal Noir')
            ->assertSeeText('Rose Atelier')
            ->assertSeeText('Indigo Edit');

        $this->actingAs($seller)->put(route('seller.design.update'), ['template' => $template, 'shop_id' => $otherShop->id])
            ->assertRedirect(route('seller.design'))
            ->assertSessionHas('status');

        $shop->refresh();
        $otherShop->refresh();

        $this->assertSame($template, $shop->template);
        $this->assertSame(Shop::DEFAULT_TEMPLATE, $otherShop->template);
        $this->assertSame(1, $shop->products()->count());
        $this->assertModelExists($product);

        $this->get(route('shops.show', $shop).'?q=Royal')
            ->assertOk()
            ->assertSeeText($pageMarker)
            ->assertSeeText('Royal Emerald Saree')
            ->assertSee($assetPath, false);
        $this->get(route('shops.products.show', [$shop, $product]))
            ->assertOk()
            ->assertSeeText('Royal Emerald Saree');

    }

    public static function storefrontTemplates(): array
    {
        return [
            'classic' => [Shop::DEFAULT_TEMPLATE, 'EXPLORE OUR SAREES', '/storefront/saree.jpg'],
            'vastra emerald' => [Shop::VASTRA_EMERALD_TEMPLATE, 'The considered collection.', '/templates/vastra-emerald/saree.jpg'],
            'royal noir' => [Shop::ROYAL_NOIR_TEMPLATE, 'For collections with', '/templates/royal-noir/saree.jpg'],
            'rose atelier' => [Shop::ROSE_ATELIER_TEMPLATE, 'A little colour. A lot of', '/templates/rose-atelier/saree2.jpg'],
            'indigo edit' => [Shop::INDIGO_EDIT_TEMPLATE, 'Your next collection,', '/templates/indigo-edit/saree1.jpg'],
        ];
    }

    public function test_template_catalogue_filters_real_shop_data_and_hides_missing_contact_methods(): void
    {
        $shop = Shop::factory()->active()->create(['template' => Shop::ROYAL_NOIR_TEMPLATE]);
        $category = $shop->categories()->create(['name' => 'Wedding', 'slug' => 'wedding', 'sort_order' => 1]);
        $matchingProduct = $shop->products()->create([
            'category_id' => $category->id,
            'name' => 'Azure Silk Saree',
            'slug' => 'azure-silk-saree',
            'code' => 'RN-001',
            'colour' => 'Blue',
            'wholesale_price' => 1250,
            'minimum_order_quantity' => 6,
            'blouse_piece' => 'not_specified',
            'availability' => 'available',
        ]);
        $shop->products()->create([
            'category_id' => $category->id,
            'name' => 'Budget Blue Saree',
            'slug' => 'budget-blue-saree',
            'code' => 'RN-002',
            'colour' => 'Blue',
            'wholesale_price' => 800,
            'minimum_order_quantity' => 6,
            'blouse_piece' => 'not_specified',
            'availability' => 'available',
        ]);
        $shop->products()->create([
            'category_id' => $category->id,
            'name' => 'Crimson Silk Saree',
            'slug' => 'crimson-silk-saree',
            'code' => 'RN-003',
            'colour' => 'Red',
            'wholesale_price' => 1700,
            'minimum_order_quantity' => 6,
            'blouse_piece' => 'not_specified',
            'availability' => 'available',
        ]);

        $response = $this->get(route('shops.show', $shop).'?colour=Blue&min_price=1000&max_price=1500&sort=price_asc');

        $response->assertOk();
        $response->assertSeeText($matchingProduct->name);
        $response->assertDontSeeText('Budget Blue Saree');
        $response->assertDontSeeText('Crimson Silk Saree');
        $response->assertDontSeeText('WhatsApp enquiry');
    }

    public function test_template_catalogue_uses_the_sellers_whatsapp_number(): void
    {
        $shop = Shop::factory()->active()->create([
            'template' => Shop::ROSE_ATELIER_TEMPLATE,
            'whatsapp' => '+91 (98765) 43210',
        ]);

        $response = $this->get(route('shops.show', $shop));

        $response->assertOk();
        $response->assertSee('https://wa.me/919876543210', false);
    }

    public function test_template_catalogue_paginates_large_collections(): void
    {
        $shop = Shop::factory()->active()->create(['template' => Shop::INDIGO_EDIT_TEMPLATE]);
        $category = $shop->categories()->create(['name' => 'Daily', 'slug' => 'daily', 'sort_order' => 1]);

        foreach (range(1, 13) as $number) {
            $shop->products()->create([
                'category_id' => $category->id,
                'name' => 'Catalogue Saree '.$number,
                'slug' => 'catalogue-saree-'.$number,
                'code' => 'IE-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'wholesale_price' => 1000 + $number,
                'minimum_order_quantity' => 6,
                'blouse_piece' => 'not_specified',
                'availability' => 'available',
            ]);
        }

        $response = $this->get(route('shops.show', $shop));

        $response->assertOk();
        $response->assertSeeText('Page 1 of 2');
        $response->assertSeeText('Next →');
    }

    public function test_seller_design_rejects_unknown_template_keys_and_requires_authentication(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $shop = Shop::factory()->for($seller)->create(['template' => Shop::DEFAULT_TEMPLATE]);

        $this->get(route('seller.design'))->assertRedirect(route('login'));

        $this->actingAs($seller)->from(route('seller.design'))->put(route('seller.design.update'), [
            'template' => 'untrusted-view-path',
        ])->assertRedirect(route('seller.design'))->assertSessionHasErrors('template');

        $this->assertSame(Shop::DEFAULT_TEMPLATE, $shop->fresh()->template);
        $this->actingAs($seller)->get(route('seller.design.preview', 'untrusted-view-path'))->assertNotFound();
    }
}
