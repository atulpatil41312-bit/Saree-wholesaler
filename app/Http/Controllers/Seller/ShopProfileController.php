<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopRedirect;
use App\Models\User;
use App\Services\ImageOptimizationService;
use App\Services\ShopAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShopProfileController extends Controller
{
    public function __construct(private ShopAccessService $access, private ImageOptimizationService $images) {}

    public function edit(Request $request): View
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_branding');

        return view('seller.profile', compact('shop'));
    }

    public function update(Request $request): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_branding');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'alpha_dash',
                'max:255',
                Rule::notIn(['admin', 'api', 'dashboard', 'login', 'logout', 'retailer', 'seller', 'shops', 'wholesaler']),
                Rule::unique('shops', 'slug')->where(fn ($query) => $query->where('type', $shop->type))->ignore($shop->id),
                function (string $attribute, mixed $value, \Closure $fail) use ($shop): void {
                    if ($value !== $shop->slug && ShopRedirect::query()->where('shop_type', $shop->type)->where('old_slug', $value)->exists()) {
                        $fail('This shop URL is reserved by an existing redirect.');
                    }
                },
            ],
            'market_category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'address' => ['nullable', 'string', 'max:500'],
            'shop_number' => ['nullable', 'string', 'max:100'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:5120'],
        ]);

        $logo = $request->file('logo');
        $banner = $request->file('banner');
        $optimizedLogo = $logo === null ? null : $this->images->optimize($logo, 1200, 1200);
        $optimizedBanner = $banner === null ? null : $this->images->optimize($banner, 2400, 1600);
        $uploadBytes = ($optimizedLogo === null ? 0 : strlen($optimizedLogo['contents']))
            + ($optimizedBanner === null ? 0 : strlen($optimizedBanner['contents']));
        $replacedBytes = ($logo === null ? 0 : $this->storageSize($shop->logo_path))
            + ($banner === null ? 0 : $this->storageSize($shop->banner_path));
        $expectedStorage = max(0, (int) $shop->storage_used_bytes - $replacedBytes) + $uploadBytes;

        if ($expectedStorage > (int) $shop->storage_quota_bytes) {
            throw ValidationException::withMessages([
                'logo' => 'The selected branding images exceed this shop\'s storage allowance.',
            ]);
        }

        unset($data['logo'], $data['banner']);
        $oldLogoPath = $shop->logo_path;
        $oldBannerPath = $shop->banner_path;

        if ($optimizedLogo !== null) {
            $data['logo_path'] = $this->images->store($optimizedLogo, "shops/{$shop->id}/branding");
        }

        if ($optimizedBanner !== null) {
            $data['banner_path'] = $this->images->store($optimizedBanner, "shops/{$shop->id}/branding");
        }

        $data['storage_used_bytes'] = $expectedStorage;

        DB::transaction(function () use ($shop, $data, $request): void {
            $locked = Shop::query()->lockForUpdate()->findOrFail($shop->id);
            $oldSlug = $locked->slug;
            $locked->update($data);

            if ($oldSlug !== $locked->slug) {
                ShopRedirect::query()->updateOrCreate(
                    ['shop_type' => $locked->type, 'old_slug' => $oldSlug],
                    ['shop_id' => $locked->id, 'redirect_to' => $locked->publicUrl()],
                );
            }

            $this->access->audit($request->user(), $locked, 'shop.profile-updated', $locked, [
                'slug_changed' => $oldSlug !== $locked->slug,
            ]);
        }, 3);

        if ($logo !== null && $oldLogoPath !== null) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        if ($banner !== null && $oldBannerPath !== null && $oldBannerPath !== $oldLogoPath) {
            Storage::disk('public')->delete($oldBannerPath);
        }

        return redirect()->route('seller.profile.edit')->with('status', 'Shop profile saved.');
    }

    private function storageSize(?string $path): int
    {
        if ($path === null || ! Storage::disk('public')->exists($path)) {
            return 0;
        }

        return (int) Storage::disk('public')->size($path);
    }

    private function dashboardShop(Request $request): Shop
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $shop = $user->dashboardShop();
        abort_unless($shop instanceof Shop, 404, 'No shop is associated with this account.');

        return $shop;
    }
}
