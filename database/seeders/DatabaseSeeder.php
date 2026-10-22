<?php

namespace Database\Seeders;

use App\Models\Fabric;
use App\Models\Plan;
use App\Models\PlanVersion;
use App\Models\PlatformSetting;
use App\Models\SareeType;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        PlatformSetting::query()->firstOrCreate(['id' => 1], ['platform_name' => 'WholeMarket']);
        foreach (['Silk', 'Cotton', 'Georgette', 'Chiffon'] as $name) {
            Fabric::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]);
        }
        foreach (['Festive', 'Classic', 'Pastel'] as $name) {
            SareeType::firstOrCreate(['slug' => str($name)->slug()], ['name' => $name]);
        }

        $this->seedTemplates();
        $plans = $this->seedPlans();

        if (app()->environment('production')) {
            $adminEmail = env('INITIAL_ADMIN_EMAIL');
            $adminPassword = env('INITIAL_ADMIN_PASSWORD');

            if (filled($adminEmail) && filled($adminPassword)) {
                User::firstOrCreate(
                    ['email' => $adminEmail],
                    ['name' => 'WholeMarket Administrator', 'password' => Hash::make($adminPassword), 'role' => 'admin'],
                );
            } elseif (! User::query()->where('role', 'admin')->exists()) {
                throw new RuntimeException('Set INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD before the first production deploy.');
            }

            return;
        }

        User::firstOrCreate(['email' => 'admin@wholemarket.test'], ['name' => 'WholeMarket Admin', 'password' => Hash::make('ChangeMe123!'), 'role' => 'admin']);
        foreach ([
            ['Krishna Sarees', 'krishna-sarees', Plan::Pro],
            ['Royal Silk', 'royal-silk', Plan::Normal],
            ['Patel Textiles', 'patel-textiles', Plan::Basic],
        ] as [$name, $slug, $planKey]) {
            $seller = User::firstOrCreate(['email' => "{$slug}@wholemarket.test"], ['name' => "{$name} Seller", 'password' => Hash::make('ChangeMe123!'), 'role' => 'seller']);
            $shop = Shop::query()->updateOrCreate(
                ['user_id' => $seller->id],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'type' => 'wholesaler',
                    'status' => 'active',
                    'approved_at' => now(),
                ],
            );

            $shop->memberships()->firstOrCreate(
                ['user_id' => $seller->id],
                [
                    'role' => 'owner',
                    'capabilities' => ['manage_catalogue', 'manage_branding', 'manage_billing'],
                ],
            );
            $shop->settings()->firstOrCreate([], ['template_key' => $shop->template]);

            $plan = $plans[$planKey]['plan'];
            $version = $plans[$planKey]['version'];
            Subscription::query()->firstOrCreate(
                ['shop_id' => $shop->id, 'plan_id' => $plan->id],
                [
                    'plan_version_id' => $version->id,
                    'status' => 'active',
                    'source' => 'seed',
                    'billing_interval' => 'monthly',
                    'started_at' => now(),
                    'paid_through_at' => now()->addYear(),
                ],
            );
        }
    }

    private function seedTemplates(): void
    {
        foreach (Shop::templates() as $position => $name) {
            Template::query()->firstOrCreate(
                ['key' => $position],
                [
                    'name' => $name,
                    'shop_type' => $position === Shop::WHOLESALE_ATELIER_TEMPLATE ? null : 'wholesaler',
                    'is_active' => true,
                    'sort_order' => array_search($position, array_keys(Shop::templates()), true),
                    'configuration' => ['source' => 'built-in'],
                ],
            );
        }
    }

    /**
     * @return array<string, array{plan: Plan, version: PlanVersion}>
     */
    private function seedPlans(): array
    {
        $catalogue = [];

        foreach (config('wholemarket.plans', []) as $key => $definition) {
            /** @var array<string, mixed> $definition */
            $plan = Plan::query()->firstOrCreate(
                ['key' => $key],
                [
                    'name' => $definition['name'],
                    'shop_type' => $definition['shop_type'],
                    'signup_enabled' => true,
                    'is_active' => true,
                    'sort_order' => array_search($key, array_keys(config('wholemarket.plans', [])), true),
                ],
            );
            $limits = $definition['limits'];
            $templateKeys = $limits['templates'] === 'all'
                ? array_keys(Shop::templates())
                : $limits['templates'];
            unset($limits['templates']);

            $version = PlanVersion::query()->firstOrCreate(
                ['plan_id' => $plan->id, 'version' => 1],
                [
                    'price_paise' => null,
                    'billing_interval' => 'monthly',
                    'limits' => $limits,
                    'template_keys' => $templateKeys,
                    'active_from' => now(),
                ],
            );

            $catalogue[$key] = ['plan' => $plan, 'version' => $version];
        }

        return $catalogue;
    }
}
