<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $shops = Shop::query()->withCount('products')->latest()->get();
        $productCount = Product::query()->count();
        $sellerCount = User::query()->whereIn('role', ['seller', 'wholesaler'])->count();
        $retailerCount = User::query()->where('role', 'retailer')->count();
        $activeSubscriptionCount = Subscription::query()->whereIn('status', ['active', 'grace'])->count();
        $planCount = Plan::query()->where('is_active', true)->count();

        return view('admin.dashboard', compact(
            'shops',
            'productCount',
            'sellerCount',
            'retailerCount',
            'activeSubscriptionCount',
            'planCount',
        ));
    }
}
