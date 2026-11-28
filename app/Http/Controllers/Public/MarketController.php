<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MarketController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->toString();
        $craft = $request->string('craft')->trim()->toString();
        $city = $request->string('city')->trim()->toString();
        $moq = $request->integer('moq') ?: null;

        $shops = Shop::query()
            ->active()
            ->whereNull('suspended_at')
            ->with(['subscriptions.planVersion'])
            ->withCount(['products as products_count' => fn ($builder) => $builder->available()])
            ->with([
                'categories' => fn ($builder) => $builder->orderBy('sort_order'),
                'products' => fn ($builder) => $builder
                    ->available()
                    ->with(['images', 'category', 'fabric'])
                    ->latest(),
            ])
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($search) use ($query): void {
                    $search
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('market_category', 'like', "%{$query}%")
                        ->orWhere('address', 'like', "%{$query}%")
                        ->orWhereHas('categories', fn ($categories) => $categories->where('name', 'like', "%{$query}%"))
                        ->orWhereHas('products', function ($products) use ($query): void {
                            $products
                                ->where('name', 'like', "%{$query}%")
                                ->orWhere('code', 'like', "%{$query}%")
                                ->orWhere('colour', 'like', "%{$query}%");
                        });
                });
            })
            ->when($craft !== '', function ($builder) use ($craft): void {
                $builder->where(function ($filter) use ($craft): void {
                    $filter
                        ->where('market_category', 'like', "%{$craft}%")
                        ->orWhereHas('categories', fn ($categories) => $categories->where('name', 'like', "%{$craft}%"))
                        ->orWhereHas('products.fabric', fn ($fabrics) => $fabrics->where('name', 'like', "%{$craft}%"));
                });
            })
            ->when($city !== '', fn ($builder) => $builder->where('address', 'like', "%{$city}%"))
            ->when($moq !== null, fn ($builder) => $builder->whereHas('products', fn ($products) => $products->where('minimum_order_quantity', '<=', $moq)))
            ->orderBy('name')
            ->get()
            ->filter(fn (Shop $shop): bool => $shop->isPubliclyVisible())
            ->values();

        $shops->each(function (Shop $shop): void {
            $shop->setRelation('products', $shop->products->each(function (Product $product): void {
                $product->setRelation('images', $product->images->filter(
                    fn ($image): bool => Storage::disk('public')->exists($image->path),
                )->values());
            }));
        });

        $shopIds = $shops->pluck('id');
        $featuredProducts = $shopIds->isEmpty()
            ? collect()
            : Product::query()
                ->whereIn('shop_id', $shopIds)
                ->available()
                ->with(['shop', 'images', 'category', 'fabric'])
                ->latest()
                ->limit(8)
                ->get();

        $featuredProducts->each(function (Product $product): void {
            $product->setRelation('images', $product->images->filter(
                fn ($image): bool => Storage::disk('public')->exists($image->path),
            )->values());
        });

        $craftOptions = $shops
            ->flatMap(fn (Shop $shop): Collection => $shop->categories->pluck('name')->merge(
                $shop->products->map(fn (Product $product): ?string => $product->fabric?->name),
            ))
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $cityOptions = $shops
            ->map(fn (Shop $shop): string => trim((string) str($shop->address ?: '')->before(',')))
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $clusterCount = $shops
            ->map(fn (Shop $shop): string => trim((string) str($shop->address ?: '')->before(',')))
            ->filter()
            ->unique()
            ->count();

        return view('market.stitch', [
            'shops' => $shops,
            'query' => $query,
            'craft' => $craft,
            'city' => $city,
            'moq' => $moq,
            'craftOptions' => $craftOptions,
            'cityOptions' => $cityOptions,
            'featuredProducts' => $featuredProducts,
            'clusterCount' => max($clusterCount, $shops->count()),
            'catalogueCount' => $shops->sum('products_count'),
        ]);
    }
}
