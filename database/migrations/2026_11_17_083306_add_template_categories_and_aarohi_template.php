<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('templates', 'category')) {
            Schema::table('templates', function (Blueprint $table): void {
                $table->string('category')->default('sarees')->after('shop_type');
            });
        }

        DB::table('templates')->update(['category' => 'sarees']);

        $now = now();
        DB::table('templates')->updateOrInsert(
            ['key' => 'aarohi-01'],
            [
                'name' => 'AAROHI — Ethnic Fashion Boutique',
                'shop_type' => null,
                'category' => 'dresses',
                'is_active' => true,
                'sort_order' => 100,
                'configuration' => json_encode(['source' => 'stitch', 'business_types' => ['retailer', 'wholesaler']]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        $planIds = DB::table('plans')->whereIn('shop_type', ['wholesaler', 'retailer'])->pluck('id');
        DB::table('plan_versions')
            ->whereIn('plan_id', $planIds)
            ->whereNotNull('template_keys')
            ->orderBy('id')
            ->get(['id', 'template_keys'])
            ->each(function (object $version): void {
                $keys = json_decode((string) $version->template_keys, true);

                if (! is_array($keys) || in_array('aarohi-01', $keys, true)) {
                    return;
                }

                DB::table('plan_versions')->where('id', $version->id)->update([
                    'template_keys' => json_encode([...$keys, 'aarohi-01']),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('templates')->where('key', 'aarohi-01')->delete();

        DB::table('plan_versions')
            ->whereNotNull('template_keys')
            ->orderBy('id')
            ->get(['id', 'template_keys'])
            ->each(function (object $version): void {
                $keys = json_decode((string) $version->template_keys, true);

                if (! is_array($keys) || ! in_array('aarohi-01', $keys, true)) {
                    return;
                }

                DB::table('plan_versions')->where('id', $version->id)->update([
                    'template_keys' => json_encode(array_values(array_diff($keys, ['aarohi-01']))),
                    'updated_at' => now(),
                ]);
            });

        if (Schema::hasColumn('templates', 'category')) {
            Schema::table('templates', function (Blueprint $table): void {
                $table->dropColumn('category');
            });
        }
    }
};
