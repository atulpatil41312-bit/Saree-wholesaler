<?php

namespace Database\Seeders;

use App\Models\Fabric;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SareeType;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoCatalogueSeeder extends Seeder
{
    /**
     * Seed a small, reusable local catalogue without touching existing products.
     *
     * The production seeder never calls this class. These records are deliberately
     * fictional and exist only to make every storefront direction easy to preview.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $this->seedRetailerShop();

        Shop::query()
            ->whereIn('type', ['wholesaler', 'retailer'])
            ->orderBy('id')
            ->get()
            ->each(fn (Shop $shop): int => $this->seedShopCatalogue($shop));
    }

    private function seedRetailerShop(): void
    {
        $owner = User::query()->firstOrCreate(
            ['email' => 'retailer-demo@wholemarket.test'],
            [
                'name' => 'Aarohi Retail Studio',
                'password' => Hash::make('ChangeMe123!'),
                'role' => 'retailer',
            ],
        );

        $shop = Shop::query()->firstOrCreate(
            ['slug' => 'aarohi-retail-studio'],
            [
                'user_id' => $owner->id,
                'type' => 'retailer',
                'name' => 'Aarohi Retail Studio',
                'template' => Shop::ROYAL_DRAPE_TEMPLATE,
                'status' => 'active',
                'approved_at' => now(),
                'email' => $owner->email,
                'phone' => '+1 202 555 0100',
                'whatsapp' => '+1 202 555 0100',
                'instagram_url' => 'https://example.com/aarohi-instagram',
                'facebook_url' => 'https://example.com/aarohi-facebook',
                'description' => 'A welcoming retail edit of everyday silk, festive and handwoven sarees.',
                'market_category' => 'Contemporary sarees',
            ],
        );

        $shop->memberships()->firstOrCreate(
            ['user_id' => $owner->id],
            ['role' => 'owner', 'capabilities' => ['manage_catalogue', 'manage_branding']],
        );
        $shop->settings()->firstOrCreate([], ['template_key' => Shop::ROYAL_DRAPE_TEMPLATE]);

        $plan = Plan::query()->where('key', Plan::RetailerBasic)->first();
        $version = $plan?->currentVersion()->first();
        if ($plan !== null && ! $shop->subscriptions()->exists()) {
            $shop->subscriptions()->create([
                'plan_id' => $plan->id,
                'plan_version_id' => $version?->id,
                'status' => 'active',
                'source' => 'seed',
                'billing_interval' => 'monthly',
                'started_at' => now(),
                'paid_through_at' => now()->addYear(),
            ]);
        }
    }

    private function seedShopCatalogue(Shop $shop): int
    {
        if ($shop->products()->exists()) {
            return 0;
        }

        $fabrics = Fabric::query()->orderBy('id')->get()->values();
        $sareeTypes = SareeType::query()->orderBy('id')->get()->values();
        $definitions = [
            ['name' => 'Kanjivaram Heritage Silk', 'code' => 'WM-001', 'colour' => 'Ruby red', 'price' => 2850, 'minimum' => 6, 'fabric' => 'Silk', 'category' => 'Festive Edit', 'description' => 'A lustrous silk saree with a traditional zari border for wedding and festive assortments.'],
            ['name' => 'Mysore Soft Silk Draped Saree', 'code' => 'WM-002', 'colour' => 'Emerald green', 'price' => 2190, 'minimum' => 6, 'fabric' => 'Silk', 'category' => 'Silk Classics', 'description' => 'Soft, fluid silk with a graceful fall and a refined contrast pallu.'],
            ['name' => 'Handloom Cotton Checks', 'code' => 'WM-003', 'colour' => 'Indigo blue', 'price' => 1180, 'minimum' => 10, 'fabric' => 'Cotton', 'category' => 'Daily Weaves', 'description' => 'Breathable handloom cotton with an easy checked texture for everyday dressing.'],
            ['name' => 'Organza Pearl Border Saree', 'code' => 'WM-004', 'colour' => 'Blush pink', 'price' => 1640, 'minimum' => 6, 'fabric' => 'Georgette', 'category' => 'Pastel Stories', 'description' => 'Lightweight organza-inspired texture finished with a delicate pearl border.'],
            ['name' => 'Banarasi Gold Zari Edit', 'code' => 'WM-005', 'colour' => 'Antique gold', 'price' => 3420, 'minimum' => 4, 'fabric' => 'Silk', 'category' => 'Bridal Gold', 'description' => 'A richly woven Banarasi-inspired edit for elevated bridal and occasion collections.'],
        ];

        $imageSources = [
            'templates/retailer-pack/models/hero.jpg',
            'templates/retailer-pack/models/look-1.jpg',
            'templates/retailer-pack/models/look-2.jpg',
            'templates/retailer-pack/models/look-3.jpg',
            'templates/vastra-emerald/saree.jpg',
        ];

        foreach ($definitions as $index => $definition) {
            $category = $shop->categories()->firstOrCreate(
                ['slug' => Str::slug($definition['category'])],
                ['name' => $definition['category'], 'sort_order' => $index],
            );
            $fabric = $fabrics->first(fn (Fabric $item): bool => $item->name === $definition['fabric']);
            $sareeType = $sareeTypes->get($index % max(1, $sareeTypes->count()));
            $product = Product::query()->firstOrCreate(
                ['shop_id' => $shop->id, 'code' => $definition['code']],
                [
                    'category_id' => $category->id,
                    'saree_type_id' => $sareeType?->id,
                    'fabric_id' => $fabric?->id,
                    'name' => $definition['name'],
                    'slug' => Str::slug($definition['name']),
                    'colour' => $definition['colour'],
                    'description' => $definition['description'],
                    'wholesale_price' => $definition['price'],
                    'price_paise' => $definition['price'] * 100,
                    'minimum_order_quantity' => $definition['minimum'],
                    'pack_size' => $definition['minimum'],
                    'blouse_piece' => 'included',
                    'availability' => 'available',
                ],
            );

            $path = $this->copyDemoImage($shop, $product, $imageSources[$index]);
            if ($path !== null) {
                ProductImage::query()->firstOrCreate(
                    ['product_id' => $product->id, 'path' => $path],
                    ['alt_text' => $definition['name'].' model photograph', 'sort_order' => 0],
                );
            }
        }

        return count($definitions);
    }

    private function copyDemoImage(Shop $shop, Product $product, string $source): ?string
    {
        $sourcePath = public_path($source);
        if (! File::exists($sourcePath)) {
            return null;
        }

        $path = 'demo-sarees/'.$shop->slug.'/'.$product->slug.'.jpg';
        $destination = storage_path('app/public/'.$path);
        File::ensureDirectoryExists(dirname($destination));
        if (! File::exists($destination)) {
            File::copy($sourcePath, $destination);
        }

        return $path;
    }
}
