<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Fabric;
use App\Models\Shop;
use App\Models\ShopRedirect;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function wholesaler(Request $request, string $shop): View|Response|RedirectResponse
    {
        return $this->showShop($request, 'wholesaler', $shop);
    }

    public function retailer(Request $request, string $shop): View|Response|RedirectResponse
    {
        return $this->showShop($request, 'retailer', $shop);
    }

    public function wholesalerProduct(Request $request, string $shop, string $product): View|Response|RedirectResponse
    {
        return $this->showProduct($request, 'wholesaler', $shop, $product);
    }

    public function retailerProduct(Request $request, string $shop, string $product): View|Response|RedirectResponse
    {
        return $this->showProduct($request, 'retailer', $shop, $product);
    }

    public function legacyShop(Request $request, string $slug): View|Response|RedirectResponse
    {
        $shop = Shop::query()->where('slug', $slug)->get();

        if ($shop->count() !== 1) {
            return $this->unavailable();
        }

        $resolvedShop = $shop->sole();

        if (! $resolvedShop->isPubliclyVisible()) {
            return $this->unavailable();
        }

        return redirect()->to($resolvedShop->publicUrl(), 301);
    }

    public function legacyProduct(Request $request, string $shopSlug, string $productSlug): View|Response|RedirectResponse
    {
        $shops = Shop::query()->where('slug', $shopSlug)->get();

        if ($shops->count() !== 1) {
            return $this->unavailable();
        }

        $shop = $shops->sole();
        $product = $shop->products()->where('slug', $productSlug)->first();

        if ($product === null || ! $shop->isPubliclyVisible()) {
            return $this->unavailable();
        }

        return redirect()->to($product->publicUrl(), 301);
    }

    private function showShop(Request $request, string $type, string $slug): View|Response|RedirectResponse
    {
        $shop = $this->findTypedShop($type, $slug);

        if ($shop instanceof RedirectResponse) {
            return $shop;
        }

        if ($shop === null || ! $shop->isPubliclyVisible()) {
            return $this->unavailable();
        }

        $template = $shop->storefrontTemplate();

        return view(Shop::storefrontViewFor($template), [
            'shop' => $shop,
            'title' => $shop->seo_title ?: $shop->name.' | WholeMarket',
            'template' => $template,
            'templateDetails' => Shop::templateDetails()[$template],
            ...$this->catalogueData($request, $shop),
        ]);
    }

    private function showProduct(Request $request, string $type, string $shopSlug, string $productSlug): View|Response|RedirectResponse
    {
        $shop = $this->findTypedShop($type, $shopSlug);

        if ($shop instanceof RedirectResponse) {
            return $shop;
        }

        if ($shop === null || ! $shop->isPubliclyVisible()) {
            return $this->unavailable();
        }

        $product = $shop->products()
            ->with(['images', 'category', 'fabric'])
            ->where('slug', $productSlug)
            ->first();

        if ($product === null) {
            return $this->unavailable();
        }

        $product->setRelation('shop', $shop);
        $template = $shop->storefrontTemplate();

        return view(Shop::productStorefrontViewFor($template), [
            'shop' => $shop,
            'product' => $product,
            'title' => $shop->seo_title ?: $product->name.' | '.$shop->name,
            'template' => $template,
            'templateDetails' => Shop::templateDetails()[$template],
        ]);
    }

    private function findTypedShop(string $type, string $slug): Shop|RedirectResponse|null
    {
        $shop = Shop::query()
            ->ofType($type)
            ->where('slug', $slug)
            ->with(['settings', 'subscriptions.planVersion'])
            ->first();

        if ($shop !== null) {
            return $shop;
        }

        $redirect = ShopRedirect::query()
            ->with(['shop.settings', 'shop.subscriptions.planVersion'])
            ->where('shop_type', $type)
            ->where('old_slug', $slug)
            ->first();

        if ($redirect === null || $redirect->shop === null || ! $redirect->shop->isPubliclyVisible()) {
            return null;
        }

        return redirect()->to($redirect->redirect_to ?: $redirect->shop->publicUrl(), 301);
    }

    /**
     * @return array{products: LengthAwarePaginator, categories: \Illuminate\Database\Eloquent\Collection<int, Category>, fabrics: \Illuminate\Database\Eloquent\Collection<int, Fabric>, colours: Collection<int, string>}
     */
    private function catalogueData(Request $request, Shop $shop): array
    {
        $query = $request->string('q')->trim()->toString();
        $colour = $request->string('colour')->trim()->toString();
        $categoryId = $request->integer('category') ?: null;
        $fabricId = $request->integer('fabric') ?: null;
        $minPrice = $this->priceInPaise($request->input('min_price'));
        $maxPrice = $this->priceInPaise($request->input('max_price'));
        $sort = $request->string('sort')->toString();

        $products = $shop->products()
            ->with(['images', 'category', 'fabric'])
            ->when($categoryId, fn ($builder) => $builder->where('category_id', $categoryId))
            ->when($fabricId, fn ($builder) => $builder->where('fabric_id', $fabricId))
            ->when($colour !== '', fn ($builder) => $builder->where('colour', $colour))
            ->when($minPrice !== null, function ($builder) use ($minPrice): void {
                $builder->where(function ($price) use ($minPrice): void {
                    $price->where('price_paise', '>=', $minPrice)
                        ->orWhere(function ($legacy) use ($minPrice): void {
                            $legacy->whereNull('price_paise')->where('wholesale_price', '>=', $minPrice / 100);
                        });
                });
            })
            ->when($maxPrice !== null, function ($builder) use ($maxPrice): void {
                $builder->where(function ($price) use ($maxPrice): void {
                    $price->where('price_paise', '<=', $maxPrice)
                        ->orWhere(function ($legacy) use ($maxPrice): void {
                            $legacy->whereNull('price_paise')->where('wholesale_price', '<=', $maxPrice / 100);
                        });
                });
            })
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($search) use ($query): void {
                    $search
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('code', 'like', "%{$query}%")
                        ->orWhere('colour', 'like', "%{$query}%");
                });
            });

        match ($sort) {
            'price_asc' => $products->orderByRaw('COALESCE(price_paise, CAST(ROUND(wholesale_price * 100) AS INTEGER))'),
            'price_desc' => $products->orderByRaw('COALESCE(price_paise, CAST(ROUND(wholesale_price * 100) AS INTEGER)) DESC'),
            default => $products->latest(),
        };

        return [
            'products' => $products->paginate(12)->withQueryString(),
            'categories' => $shop->categories()->orderBy('sort_order')->get(),
            'fabrics' => Fabric::query()->orderBy('name')->get(),
            'colours' => $shop->products()
                ->whereNotNull('colour')
                ->where('colour', '!=', '')
                ->distinct()
                ->orderBy('colour')
                ->pluck('colour'),
        ];
    }

    private function priceInPaise(mixed $value): ?int
    {
        if (! is_numeric($value) || (float) $value < 0) {
            return null;
        }

        return (int) round((float) $value * 100);
    }

    private function unavailable(): Response
    {
        return response()->view('shop.unavailable', [], 404);
    }
}
