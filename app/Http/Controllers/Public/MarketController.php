<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->toString();

        $shops = Shop::query()
            ->active()
            ->whereNull('suspended_at')
            ->with(['subscriptions.planVersion'])
            ->withCount('products')
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where('name', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->get()
            ->filter(fn (Shop $shop): bool => $shop->isPubliclyVisible())
            ->values();

        return view('market.index', compact('shops', 'query'));
    }
}
