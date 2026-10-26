<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the imported retailer designs to existing local retailer plan versions.
     * This only changes entitlement metadata; shop data and subscriptions remain untouched.
     */
    public function up(): void
    {
        foreach ([
            ['key' => 'royal-drape', 'name' => 'Royal Drape', 'sort_order' => 6],
            ['key' => 'silk-atelier', 'name' => 'Silk Atelier', 'sort_order' => 7],
            ['key' => 'heritage-weaves', 'name' => 'Heritage Weaves', 'sort_order' => 8],
        ] as $template) {
            $values = [
                'name' => $template['name'],
                'shop_type' => 'retailer',
                'is_active' => true,
                'sort_order' => $template['sort_order'],
                'configuration' => json_encode(['source' => 'wholemarket-retailer-template-pack']),
                'updated_at' => now(),
            ];

            if (DB::table('templates')->where('key', $template['key'])->exists()) {
                DB::table('templates')->where('key', $template['key'])->update($values);
            } else {
                DB::table('templates')->insert($values + ['key' => $template['key'], 'created_at' => now()]);
            }
        }

        $planId = DB::table('plans')->where('key', 'retailer-basic')->value('id');

        if ($planId === null) {
            return;
        }

        $newKeys = ['royal-drape', 'silk-atelier', 'heritage-weaves'];
        DB::table('plan_versions')->where('plan_id', $planId)->get()->each(function (object $version) use ($newKeys): void {
            $keys = json_decode((string) $version->template_keys, true);

            if ($keys === 'all') {
                return;
            }

            $keys = is_array($keys) ? $keys : [];
            $updated = array_values(array_unique([...$keys, ...$newKeys]));

            DB::table('plan_versions')->where('id', $version->id)->update([
                'template_keys' => json_encode($updated),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        DB::table('templates')->whereIn('key', ['royal-drape', 'silk-atelier', 'heritage-weaves'])->delete();

        $planId = DB::table('plans')->where('key', 'retailer-basic')->value('id');

        if ($planId === null) {
            return;
        }

        $removed = ['royal-drape', 'silk-atelier', 'heritage-weaves'];
        DB::table('plan_versions')->where('plan_id', $planId)->get()->each(function (object $version) use ($removed): void {
            $keys = json_decode((string) $version->template_keys, true);

            if (! is_array($keys)) {
                return;
            }

            DB::table('plan_versions')->where('id', $version->id)->update([
                'template_keys' => json_encode(array_values(array_diff($keys, $removed))),
                'updated_at' => now(),
            ]);
        });
    }
};
