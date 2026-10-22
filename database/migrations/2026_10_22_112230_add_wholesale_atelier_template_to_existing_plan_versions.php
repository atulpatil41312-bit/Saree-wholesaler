<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->updateTemplateKeys(true);

        DB::table('templates')->updateOrInsert(
            ['key' => 'wholesaler-templates'],
            [
                'name' => 'Wholesale Atelier',
                'shop_type' => null,
                'is_active' => true,
                'sort_order' => 5,
                'configuration' => json_encode(['source' => 'wholemarket-wholesaler-templates-template']),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        $this->updateTemplateKeys(false);
        DB::table('templates')->where('key', 'wholesaler-templates')->delete();
    }

    private function updateTemplateKeys(bool $add): void
    {
        $versions = DB::table('plan_versions')
            ->join('plans', 'plans.id', '=', 'plan_versions.plan_id')
            ->whereIn('plans.key', ['basic', 'normal', 'pro', 'retailer-basic'])
            ->orderBy('plan_versions.id')
            ->get(['plan_versions.id', 'plan_versions.template_keys']);

        foreach ($versions as $version) {
            $templateKeys = json_decode((string) $version->template_keys, true);

            if (! is_array($templateKeys)) {
                continue;
            }

            $updatedTemplateKeys = $add
                ? array_values(array_unique([...$templateKeys, 'wholesaler-templates']))
                : array_values(array_filter($templateKeys, fn (string $key): bool => $key !== 'wholesaler-templates'));

            if ($updatedTemplateKeys === $templateKeys) {
                continue;
            }

            DB::table('plan_versions')->where('id', $version->id)->update([
                'template_keys' => json_encode($updatedTemplateKeys),
                'updated_at' => now(),
            ]);
        }
    }
};
