<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Add only the missing local/demo catalogue records for the existing Divya Saree shop.
     * Existing products are never updated or removed. The migration is intentionally scoped
     * to this explicitly named demo shop so other seller catalogues remain untouched.
     */
    public function up(): void
    {
        $shop = DB::table('shops')->where('slug', 'divya-saree')->first();

        if ($shop === null) {
            return;
        }

        $currentProductCount = DB::table('products')->where('shop_id', $shop->id)->count();
        $missingProducts = max(0, 5 - $currentProductCount);

        if ($missingProducts === 0) {
            return;
        }

        $fabrics = DB::table('fabrics')->pluck('id', 'slug');
        $sareeTypes = DB::table('saree_types')->pluck('id', 'slug');
        $definitions = [
            ['name' => 'Kanjivaram Heritage Silk', 'code' => 'DV-001', 'colour' => 'Ruby red', 'price' => 2850, 'minimum' => 6, 'fabric' => 'silk', 'type' => 'festive'],
            ['name' => 'Mysore Soft Silk Draped Saree', 'code' => 'DV-002', 'colour' => 'Emerald green', 'price' => 2190, 'minimum' => 6, 'fabric' => 'silk', 'type' => 'classic'],
            ['name' => 'Handloom Cotton Checks', 'code' => 'DV-003', 'colour' => 'Indigo blue', 'price' => 1180, 'minimum' => 10, 'fabric' => 'cotton', 'type' => 'classic'],
            ['name' => 'Organza Pearl Border Saree', 'code' => 'DV-004', 'colour' => 'Blush pink', 'price' => 1640, 'minimum' => 6, 'fabric' => 'georgette', 'type' => 'pastel'],
            ['name' => 'Banarasi Gold Zari Edit', 'code' => 'DV-005', 'colour' => 'Antique gold', 'price' => 3420, 'minimum' => 4, 'fabric' => 'silk', 'type' => 'festive'],
        ];

        foreach (array_slice($definitions, 0, $missingProducts) as $index => $definition) {
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

            if (DB::table('products')->where('shop_id', $shop->id)->where('code', $definition['code'])->exists()) {
                continue;
            }

            DB::table('products')->insert([
                'shop_id' => $shop->id,
                'category_id' => $categoryId,
                'saree_type_id' => $sareeTypes->get($definition['type']),
                'fabric_id' => $fabrics->get($definition['fabric']),
                'name' => $definition['name'],
                'slug' => Str::slug($definition['name']),
                'code' => $definition['code'],
                'colour' => $definition['colour'],
                'description' => 'A curated saree for the '.$shop->name.' wholesale collection.',
                'wholesale_price' => $definition['price'],
                'price_paise' => $definition['price'] * 100,
                'minimum_order_quantity' => $definition['minimum'],
                'pack_size' => $definition['minimum'],
                'blouse_piece' => 'included',
                'availability' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('products')
            ->where('shop_id', DB::table('shops')->where('slug', 'divya-saree')->value('id'))
            ->whereIn('code', ['DV-001', 'DV-002', 'DV-003', 'DV-004', 'DV-005'])
            ->delete();
    }
};
