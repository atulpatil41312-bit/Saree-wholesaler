<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Fill the existing Savita demo retailer catalogue to five products.
     * Existing products are preserved and no other retailer is changed.
     */
    public function up(): void
    {
        $shop = DB::table('shops')->where('slug', 'savita-saree')->where('type', 'retailer')->first();

        if ($shop === null) {
            return;
        }

        $currentProductCount = DB::table('products')->where('shop_id', $shop->id)->count();

        if ($currentProductCount >= 5) {
            return;
        }

        $fabrics = DB::table('fabrics')->pluck('id', 'slug');
        $sareeTypes = DB::table('saree_types')->pluck('id', 'slug');
        $definitions = [
            ['name' => 'Kanjivaram Heritage Silk', 'code' => 'SV-001', 'colour' => 'Ruby red', 'price' => 2850, 'minimum' => 1, 'fabric' => 'silk', 'type' => 'festive'],
            ['name' => 'Mysore Soft Silk Draped Saree', 'code' => 'SV-002', 'colour' => 'Emerald green', 'price' => 2190, 'minimum' => 1, 'fabric' => 'silk', 'type' => 'classic'],
            ['name' => 'Handloom Cotton Checks', 'code' => 'SV-003', 'colour' => 'Indigo blue', 'price' => 1180, 'minimum' => 1, 'fabric' => 'cotton', 'type' => 'classic'],
            ['name' => 'Organza Pearl Border Saree', 'code' => 'SV-004', 'colour' => 'Blush pink', 'price' => 1640, 'minimum' => 1, 'fabric' => 'georgette', 'type' => 'pastel'],
            ['name' => 'Banarasi Gold Zari Edit', 'code' => 'SV-005', 'colour' => 'Antique gold', 'price' => 3420, 'minimum' => 1, 'fabric' => 'silk', 'type' => 'festive'],
        ];

        foreach ($definitions as $index => $definition) {
            if ($currentProductCount >= 5) {
                break;
            }

            if (DB::table('products')->where('shop_id', $shop->id)->where('code', $definition['code'])->exists()) {
                continue;
            }

            $categoryName = match ($index) {
                0 => 'Festive Edit',
                1 => 'Silk Classics',
                2 => 'Daily Weaves',
                3 => 'Pastel Stories',
                default => 'Bridal Gold',
            };
            $categorySlug = Str::slug($categoryName);
            $category = DB::table('categories')
                ->where('shop_id', $shop->id)
                ->where('slug', $categorySlug)
                ->first();
            $categoryId = $category?->id ?? DB::table('categories')->insertGetId([
                'shop_id' => $shop->id,
                'name' => $categoryName,
                'slug' => $categorySlug,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('products')->insert([
                'shop_id' => $shop->id,
                'category_id' => $categoryId,
                'saree_type_id' => $sareeTypes->get($definition['type']),
                'fabric_id' => $fabrics->get($definition['fabric']),
                'name' => $definition['name'],
                'slug' => Str::slug($definition['name']),
                'code' => $definition['code'],
                'colour' => $definition['colour'],
                'description' => 'A considered saree for the '.$shop->name.' retail collection.',
                'wholesale_price' => $definition['price'],
                'price_paise' => $definition['price'] * 100,
                'minimum_order_quantity' => $definition['minimum'],
                'pack_size' => $definition['minimum'],
                'blouse_piece' => 'included',
                'availability' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $currentProductCount++;
        }
    }

    public function down(): void
    {
        $shopId = DB::table('shops')->where('slug', 'savita-saree')->where('type', 'retailer')->value('id');

        if ($shopId === null) {
            return;
        }

        DB::table('products')
            ->where('shop_id', $shopId)
            ->whereIn('code', ['SV-001', 'SV-002', 'SV-003', 'SV-004', 'SV-005'])
            ->delete();
    }
};
