<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('templates')->updateOrInsert(
            ['key' => 'mavuris-edit'],
            [
                'name' => 'Mavuris Edit',
                'shop_type' => null,
                'is_active' => true,
                'sort_order' => 11,
                'configuration' => json_encode(['source' => 'mavuris-inspired-wholemarket-template']),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        DB::table('plan_versions')
            ->join('plans', 'plans.id', '=', 'plan_versions.plan_id')
            ->whereIn('plans.key', ['basic', 'normal', 'pro', 'retailer-basic'])
            ->get(['plan_versions.id', 'plan_versions.template_keys'])
            ->each(function (object $version): void {
                $keys = json_decode((string) $version->template_keys, true);

                if ($keys === 'all' || ! is_array($keys) || in_array('mavuris-edit', $keys, true)) {
                    return;
                }

                DB::table('plan_versions')->where('id', $version->id)->update([
                    'template_keys' => json_encode([...$keys, 'mavuris-edit']),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('plan_versions')
            ->join('plans', 'plans.id', '=', 'plan_versions.plan_id')
            ->whereIn('plans.key', ['basic', 'normal', 'pro', 'retailer-basic'])
            ->get(['plan_versions.id', 'plan_versions.template_keys'])
            ->each(function (object $version): void {
                $keys = json_decode((string) $version->template_keys, true);

                if (! is_array($keys)) {
                    return;
                }

                DB::table('plan_versions')->where('id', $version->id)->update([
                    'template_keys' => json_encode(array_values(array_diff($keys, ['mavuris-edit']))),
                    'updated_at' => now(),
                ]);
            });

        DB::table('templates')->where('key', 'mavuris-edit')->delete();
    }
};
