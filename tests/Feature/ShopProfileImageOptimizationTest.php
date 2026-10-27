<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopProfileImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_profile_images_are_resized_and_stored_as_webp(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'wholesaler']);
        $shop = Shop::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('seller.profile.update'), [
            'name' => $shop->name,
            'slug' => $shop->slug,
            'logo' => UploadedFile::fake()->image('logo.jpg', 3000, 2000),
            'banner' => UploadedFile::fake()->image('banner.jpg', 3000, 2000),
        ]);

        $response->assertRedirect(route('seller.profile.edit'));
        $shop->refresh();

        $this->assertStringEndsWith('.webp', $shop->logo_path);
        $this->assertStringEndsWith('.webp', $shop->banner_path);
        Storage::disk('public')->assertExists($shop->logo_path);
        Storage::disk('public')->assertExists($shop->banner_path);

        $logo = getimagesize(Storage::disk('public')->path($shop->logo_path));
        $banner = getimagesize(Storage::disk('public')->path($shop->banner_path));

        $this->assertSame('image/webp', $logo['mime']);
        $this->assertSame('image/webp', $banner['mime']);
        $this->assertLessThanOrEqual(1200, $logo[0]);
        $this->assertLessThanOrEqual(1200, $logo[1]);
        $this->assertLessThanOrEqual(2400, $banner[0]);
        $this->assertLessThanOrEqual(1600, $banner[1]);
        $this->assertSame(
            Storage::disk('public')->size($shop->logo_path) + Storage::disk('public')->size($shop->banner_path),
            $shop->storage_used_bytes,
        );
    }
}
