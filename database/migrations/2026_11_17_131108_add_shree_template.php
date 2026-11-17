<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('templates')->updateOrInsert(
            ['key' => 'shree-01'],
            [
                'name' => 'SHREE — Indian Ethnic Ready-to-Wear',
                'shop_type' => null,
                'category' => 'dresses',
                'is_active' => true,
                'sort_order' => 101,
                'configuration' => json_encode(['source' => 'stitch', 'business_types' => ['retailer', 'wholesaler']]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $planIds = DB::table('plans')->whereIn('shop_type', ['wholesaler', 'retailer'])->pluck('id');
        DB::table('plan_versions')
            ->whereIn('plan_id', $planIds)
            ->whereNotNull('template_keys')
            ->get(['id', 'template_keys'])
            ->each(function (object $version): void {
                $keys = json_decode((string) $version->template_keys, true);

                if (! is_array($keys) || in_array('shree-01', $keys, true)) {
                    return;
                }

                DB::table('plan_versions')->where('id', $version->id)->update([
                    'template_keys' => json_encode([...$keys, 'shree-01']),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('templates')->where('key', 'shree-01')->delete();

        DB::table('plan_versions')
            ->whereNotNull('template_keys')
            ->get(['id', 'template_keys'])
            ->each(function (object $version): void {
                $keys = json_decode((string) $version->template_keys, true);

                if (! is_array($keys) || ! in_array('shree-01', $keys, true)) {
                    return;
                }

                DB::table('plan_versions')->where('id', $version->id)->update([
                    'template_keys' => json_encode(array_values(array_diff($keys, ['shree-01']))),
                    'updated_at' => now(),
                ]);
            });
    }
};
