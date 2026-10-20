<?php

namespace Database\Seeders;

use App\Models\Fabric;
use App\Models\PlatformSetting;
use App\Models\SareeType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        User::firstOrCreate(['email' => 'admin@wholemarket.test'], ['name' => 'WholeMarket Admin', 'password' => Hash::make('ChangeMe123!'), 'role' => 'admin']);
        foreach ([['Krishna Sarees', 'krishna-sarees'], ['Royal Silk', 'royal-silk'], ['Patel Textiles', 'patel-textiles']] as [$name, $slug]) {
            $seller = User::firstOrCreate(['email' => "{$slug}@wholemarket.test"], ['name' => "{$name} Seller", 'password' => Hash::make('ChangeMe123!'), 'role' => 'seller']);
            $seller->shop()->firstOrCreate(['user_id' => $seller->id], ['name' => $name, 'slug' => $slug, 'status' => 'active']);
        }
    }
}
