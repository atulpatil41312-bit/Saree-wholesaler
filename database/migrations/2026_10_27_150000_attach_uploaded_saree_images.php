<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Attach the five supplied model photographs to the demo catalogues.
     * The same shop data is used by every storefront template, so one image
     * assignment updates both wholesaler and retailer presentations.
     */
    public function up(): void
    {
        $shopSlugs = [
            'aarohi-retail-studio',
            'divya-saree',
            'krishna-sarees',
            'patel-textiles',
            'royal-silk',
            'savita-saree',
        ];
        $imageDefinitions = [
            'Kanjivaram Heritage Silk' => [
                'path' => 'catalogue/demo-products/01-heavy-beads.jpg',
                'alt_text' => 'Model wearing a full heavy beads and sequence saree with a pearl border',
            ],
            'Mysore Soft Silk Draped Saree' => [
                'path' => 'catalogue/demo-products/02-black-3550.jpg',
                'alt_text' => 'Model wearing the black collection saree design 3550',
            ],
            'Handloom Cotton Checks' => [
                'path' => 'catalogue/demo-products/03-design-1862.jpg',
                'alt_text' => 'Model wearing design collection saree 1862',
            ],
            'Organza Pearl Border Saree' => [
                'path' => 'catalogue/demo-products/04-bala-wedding.jpg',
                'alt_text' => 'Model wearing the BALA wedding season saree',
            ],
            'Banarasi Gold Zari Edit' => [
                'path' => 'catalogue/demo-products/05-janki-party.jpg',
                'alt_text' => 'Model wearing the JANKI party wear saree',
            ],
        ];

        $shops = DB::table('shops')->whereIn('slug', $shopSlugs)->get(['id']);

        foreach ($shops as $shop) {
            $products = DB::table('products')
                ->where('shop_id', $shop->id)
                ->whereIn('name', array_keys($imageDefinitions))
                ->get(['id', 'name']);

            foreach ($products as $product) {
                $definition = $imageDefinitions[$product->name];
                $alreadyAttached = DB::table('product_images')
                    ->where('product_id', $product->id)
                    ->where('path', $definition['path'])
                    ->exists();

                if ($alreadyAttached) {
                    continue;
                }

                DB::table('product_images')
                    ->where('product_id', $product->id)
                    ->increment('sort_order');

                DB::table('product_images')->insert([
                    'product_id' => $product->id,
                    'path' => $definition['path'],
                    'alt_text' => $definition['alt_text'],
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('product_images')
            ->whereIn('path', [
                'catalogue/demo-products/01-heavy-beads.jpg',
                'catalogue/demo-products/02-black-3550.jpg',
                'catalogue/demo-products/03-design-1862.jpg',
                'catalogue/demo-products/04-bala-wedding.jpg',
                'catalogue/demo-products/05-janki-party.jpg',
            ])
            ->delete();
    }
};
