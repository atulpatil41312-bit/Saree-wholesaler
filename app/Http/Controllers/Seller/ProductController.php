<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Shop;
use App\Models\User;
use App\Services\ImageOptimizationService;
use App\Services\ShopAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private ShopAccessService $access, private ImageOptimizationService $images) {}

    public function index(Request $request): View
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $shop->load('categories');
        $query = $request->string('q')->trim()->toString();
        $categoryId = $request->integer('category') ?: null;
        $availability = $request->string('availability')->trim()->toString();

        if (! in_array($availability, ['available', 'out_of_stock', 'on_enquiry'], true)) {
            $availability = '';
        }

        $products = $shop->products()
            ->with(['images', 'category'])
            ->when($query !== '', fn (Builder $builder): Builder => $builder->where(fn (Builder $search): Builder => $search
                ->where('name', 'like', "%{$query}%")
                ->orWhere('code', 'like', "%{$query}%")))
            ->when($categoryId, fn (Builder $builder): Builder => $builder->where('category_id', $categoryId))
            ->when($availability !== '', fn (Builder $builder): Builder => $builder->where('availability', $availability))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('seller.products.index', [
            'shop' => $shop,
            'products' => $products,
            'limits' => $this->access->limits($shop),
            'filters' => compact('query', 'categoryId', 'availability'),
            'sellerSection' => 'products',
            'sellerPageLabel' => 'Products',
        ]);
    }

    public function create(Request $request): View
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $shop->load('categories');

        return view('seller.products.create', [
            'shop' => $shop,
            'limits' => $this->access->limits($shop),
            'sellerSection' => 'catalogue',
            'sellerPageLabel' => 'Add product',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $limits = $this->access->limits($shop);
        abort_if($shop->products()->count() >= (int) $limits['product_limit'], 422, 'Product limit reached.');
        $data = $this->validatedProductData($request, $shop, $limits);
        [$optimizedImages, $uploadBytes] = $this->optimizedImages($request);
        $this->ensureStorageAvailable($shop, $uploadBytes);
        $pricePaise = $this->priceInPaise($data['wholesale_price'] ?? null);
        unset($data['images']);
        $storedPaths = [];

        try {
            $product = DB::transaction(function () use ($shop, $data, $pricePaise, $optimizedImages, $uploadBytes, &$storedPaths): Product {
                $lockedShop = Shop::query()->lockForUpdate()->findOrFail($shop->id);
                $this->ensureStorageAvailable($lockedShop, $uploadBytes);
                $product = $lockedShop->products()->create($data + [
                    'price_paise' => $pricePaise,
                    'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5)),
                    'blouse_piece' => 'not_specified',
                ]);
                $storedPaths = $this->attachImages($product, $optimizedImages);

                if ($uploadBytes > 0) {
                    $lockedShop->update(['storage_used_bytes' => (int) $lockedShop->storage_used_bytes + $uploadBytes]);
                }

                return $product;
            }, 3);
        } catch (\Throwable $exception) {
            $this->removeStoredFiles($storedPaths);
            throw $exception;
        }

        $this->access->audit($request->user(), $shop, 'catalogue.product-created', $product, [
            'image_count' => count($optimizedImages),
        ]);

        return redirect()->route('seller.products.index')->with('status', 'Product added to your catalogue.');
    }

    public function edit(Request $request, Product $product): View
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $product = $this->ownedProduct($shop, $product)->load(['images', 'category']);
        $shop->load('categories');

        return view('seller.products.edit', [
            'shop' => $shop,
            'product' => $product,
            'limits' => $this->access->limits($shop),
            'sellerSection' => 'catalogue',
            'sellerPageLabel' => 'Edit product',
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $product = $this->ownedProduct($shop, $product);
        $limits = $this->access->limits($shop);
        $data = $this->validatedProductData($request, $shop, $limits, $product, $product->images()->count());
        [$optimizedImages, $uploadBytes] = $this->optimizedImages($request);
        $this->ensureStorageAvailable($shop, $uploadBytes);
        $pricePaise = $this->priceInPaise($data['wholesale_price'] ?? null);
        unset($data['images']);
        $storedPaths = [];

        try {
            DB::transaction(function () use ($shop, $product, $data, $pricePaise, $optimizedImages, $uploadBytes, &$storedPaths): void {
                $lockedShop = Shop::query()->lockForUpdate()->findOrFail($shop->id);
                $this->ensureStorageAvailable($lockedShop, $uploadBytes);
                $lockedProduct = $lockedShop->products()->findOrFail($product->id);
                $lockedProduct->update($data + ['price_paise' => $pricePaise]);
                $storedPaths = $this->attachImages($lockedProduct, $optimizedImages);

                if ($uploadBytes > 0) {
                    $lockedShop->update(['storage_used_bytes' => (int) $lockedShop->storage_used_bytes + $uploadBytes]);
                }
            }, 3);
        } catch (\Throwable $exception) {
            $this->removeStoredFiles($storedPaths);
            throw $exception;
        }

        $this->access->audit($request->user(), $shop, 'catalogue.product-updated', $product, [
            'image_count' => count($optimizedImages),
        ]);

        return redirect()->route('seller.products.edit', $product)->with('status', 'Product changes saved.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $product = $this->ownedProduct($shop, $product)->load('images');
        $paths = $product->images->pluck('path')->all();
        $bytes = $this->storedBytes($paths);

        DB::transaction(function () use ($shop, $product, $bytes): void {
            $lockedShop = Shop::query()->lockForUpdate()->findOrFail($shop->id);
            $product->delete();
            $lockedShop->update(['storage_used_bytes' => max(0, (int) $lockedShop->storage_used_bytes - $bytes)]);
        }, 3);
        $this->removeStoredFiles($paths);
        $this->access->audit($request->user(), $shop, 'catalogue.product-deleted', $product);

        return redirect()->route('seller.products.index')->with('status', 'Product removed from your catalogue.');
    }

    public function destroyImage(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $product = $this->ownedProduct($shop, $product);
        abort_unless($image->product_id === $product->id, 404);
        $bytes = $this->storedBytes([$image->path]);
        $path = $image->path;

        DB::transaction(function () use ($shop, $image, $bytes): void {
            $lockedShop = Shop::query()->lockForUpdate()->findOrFail($shop->id);
            $image->delete();
            $lockedShop->update(['storage_used_bytes' => max(0, (int) $lockedShop->storage_used_bytes - $bytes)]);
        }, 3);
        $this->removeStoredFiles([$path]);

        return redirect()->route('seller.products.edit', $product)->with('status', 'Product image removed.');
    }

    private function dashboardShop(Request $request): Shop
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $shop = $user->dashboardShop();
        abort_unless($shop instanceof Shop, 404, 'No shop is associated with this account.');

        return $shop;
    }

    private function ownedProduct(Shop $shop, Product $product): Product
    {
        abort_unless((int) $product->shop_id === (int) $shop->id, 404);

        return $product;
    }

    /** @return array<string, mixed> */
    private function validatedProductData(Request $request, Shop $shop, array $limits, ?Product $product = null, int $existingImageCount = 0): array
    {
        $remainingImageSlots = max(0, (int) $limits['product_image_limit'] - $existingImageCount);

        return $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('shop_id', $shop->id)],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', Rule::unique('products', 'code')->where('shop_id', $shop->id)->ignore($product?->id)],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_order_quantity' => ['required', 'integer', 'min:1'],
            'pack_size' => ['nullable', 'integer', 'min:1'],
            'availability' => ['required', Rule::in(['available', 'out_of_stock', 'on_enquiry'])],
            'images' => ['nullable', 'array', 'max:'.$remainingImageSlots],
            'images.*' => ['image', 'max:5120'],
        ]);
    }

    /** @return array{0: array<int, array{contents: string, extension: string, mime: string}>, 1: int} */
    private function optimizedImages(Request $request): array
    {
        $uploads = array_values(array_filter(
            $request->file('images', []),
            fn (mixed $upload): bool => $upload instanceof UploadedFile,
        ));
        $optimized = array_map(
            fn (UploadedFile $upload): array => $this->images->optimize($upload, 1600, 2200),
            $uploads,
        );

        return [$optimized, array_sum(array_map(fn (array $image): int => strlen($image['contents']), $optimized))];
    }

    private function priceInPaise(mixed $price): ?int
    {
        return $price === null ? null : (int) round((float) $price * 100);
    }

    private function ensureStorageAvailable(Shop $shop, int $bytes): void
    {
        if ((int) $shop->storage_used_bytes + $bytes > (int) $shop->storage_quota_bytes) {
            throw ValidationException::withMessages([
                'images' => 'These images exceed your shop storage allowance. Try fewer or smaller images.',
            ]);
        }
    }

    /** @param  array<int, array{contents: string, extension: string, mime: string}>
     * @return array<int, string>
     */
    private function attachImages(Product $product, array $optimizedImages): array
    {
        $existingImages = $product->images()->count();
        $paths = [];

        foreach ($optimizedImages as $index => $optimizedImage) {
            $path = $this->images->store($optimizedImage, "shops/{$product->shop_id}/products/{$product->id}");
            $paths[] = $path;
            $product->images()->create([
                'path' => $path,
                'alt_text' => $product->name.' product image '.($existingImages + $index + 1),
                'sort_order' => $existingImages + $index,
            ]);
        }

        return $paths;
    }

    /** @param  array<int, string>  $paths */
    private function storedBytes(array $paths): int
    {
        return array_sum(array_map(
            fn (string $path): int => Storage::disk('public')->exists($path) ? (int) Storage::disk('public')->size($path) : 0,
            $paths,
        ));
    }

    /** @param  array<int, string>  $paths */
    private function removeStoredFiles(array $paths): void
    {
        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }
}
