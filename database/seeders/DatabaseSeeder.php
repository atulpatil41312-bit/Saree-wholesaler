<?php

namespace Database\Seeders;

use App\Models\Fabric;
use App\Models\PlatformSetting;
use App\Models\SareeType;
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
        foreach ([['Krishna Sarees', 'krishna-sarees'], ['Royal Silk', 'royal-silk'], ['Patel Textiles', 'patel-textiles']] as [$name, $slug]) {
            $seller = User::firstOrCreate(['email' => "{$slug}@wholemarket.test"], ['name' => "{$name} Seller", 'password' => Hash::make('ChangeMe123!'), 'role' => 'seller']);
            $seller->shop()->firstOrCreate(['user_id' => $seller->id], ['name' => $name, 'slug' => $slug, 'status' => 'active']);
        }
    }
}
