<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use App\Services\ShopAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function __construct(private ShopAccessService $access) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'seller_name' => ['required', 'string', 'max:255'],
            'seller_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'seller_password' => ['required', 'string', 'min:12', 'confirmed'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'alpha_dash',
                'max:255',
                Rule::notIn(['admin', 'api', 'dashboard', 'login', 'logout', 'retailer', 'seller', 'shops', 'wholesaler']),
                Rule::unique('shops', 'slug')->where(fn ($query) => $query->where('type', 'wholesaler')),
            ],
        ]);

        DB::transaction(function () use ($data, $request): void {
            $seller = User::query()->create([
                'name' => $data['seller_name'],
                'email' => $data['seller_email'],
                'password' => Hash::make($data['seller_password']),
                'role' => 'wholesaler',
            ]);
            $shop = Shop::query()->create([
                'user_id' => $seller->id,
                'type' => 'wholesaler',
                'name' => $data['name'],
                'slug' => $data['slug'],
                'email' => $seller->email,
                'status' => 'pending',
            ]);
            $shop->memberships()->create([
                'user_id' => $seller->id,
                'role' => 'owner',
                'capabilities' => ['manage_catalogue', 'manage_branding', 'manage_billing'],
            ]);
            $shop->settings()->create(['template_key' => Shop::DEFAULT_TEMPLATE]);
            $this->access->audit($request->user(), $shop, 'admin.shop-created', $shop);
        }, 3);

        return back()->with('status', 'Wholesaler and pending shop created.');
    }

    public function update(Request $request, Shop $shop): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'active', 'inactive'])],
            'category_limit' => ['nullable', 'integer', 'min:1'],
            'product_limit' => ['nullable', 'integer', 'min:1'],
            'product_image_limit' => ['nullable', 'integer', 'min:1', 'max:10'],
            'suspend' => ['nullable', 'boolean'],
            'suspension_reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($shop, $data, $request): void {
            $locked = Shop::query()->lockForUpdate()->findOrFail($shop->id);
            $isSuspended = (bool) ($data['suspend'] ?? false);
            $locked->update([
                'status' => $data['status'],
                'category_limit' => $data['category_limit'] ?? $locked->category_limit,
                'product_limit' => $data['product_limit'] ?? $locked->product_limit,
                'product_image_limit' => $data['product_image_limit'] ?? $locked->product_image_limit,
                'approved_at' => $data['status'] === 'active' ? ($locked->approved_at ?? now()) : $locked->approved_at,
                'suspended_at' => $isSuspended ? now() : null,
                'suspension_reason' => $isSuspended ? ($data['suspension_reason'] ?? 'Suspended by an administrator.') : null,
            ]);
            $this->access->audit($request->user(), $locked, 'admin.shop-updated', $locked, [
                'status' => $data['status'],
                'suspended' => $isSuspended,
            ]);
        }, 3);

        return back()->with('status', 'Shop updated.');
    }
}
