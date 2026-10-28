<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_images_are_optimized_and_attached_to_the_new_product(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'seller']);
        $shop = Shop::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'storage_quota_bytes' => 10 * 1024 * 1024,
        ]);
        $category = $shop->categories()->create([
            'name' => 'Wedding sarees',
            'slug' => 'wedding-sarees',
        ]);

        $response = $this->actingAs($user)->post(route('seller.products.store'), [
            'category_id' => $category->id,
            'name' => 'Ivory sequin saree',
            'code' => 'IV-001',
            'wholesale_price' => '2450',
            'minimum_order_quantity' => 2,
            'pack_size' => 6,
            'availability' => 'available',
            'images' => [
                UploadedFile::fake()->image('ivory-front.jpg', 2400, 3200),
                UploadedFile::fake()->image('ivory-detail.jpg', 2400, 3200),
            ],
        ]);

        $response->assertRedirect();
        $product = $shop->products()->with('images')->firstOrFail();

        $this->assertCount(2, $product->images);
        foreach ($product->images as $image) {
            $this->assertStringEndsWith('.webp', $image->path);
            Storage::disk('public')->assertExists($image->path);
            $dimensions = getimagesize(Storage::disk('public')->path($image->path));
            $this->assertSame('image/webp', $dimensions['mime']);
            $this->assertLessThanOrEqual(1600, $dimensions[0]);
            $this->assertLessThanOrEqual(2200, $dimensions[1]);
        }

        $shop->refresh();
        $this->assertSame(
            $product->images->sum(fn (ProductImage $image): int => Storage::disk('public')->size($image->path)),
            $shop->storage_used_bytes,
        );
    }
}
