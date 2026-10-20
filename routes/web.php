<?php

use App\Models\Fabric;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

$catalogueData = static function (Request $request, Shop $shop): array {
    $query = $request->string('q')->trim()->toString();
    $colour = $request->string('colour')->trim()->toString();
    $categoryId = $request->integer('category') ?: null;
    $fabricId = $request->integer('fabric') ?: null;
    $minPrice = is_numeric($request->input('min_price')) ? (float) $request->input('min_price') : null;
    $maxPrice = is_numeric($request->input('max_price')) ? (float) $request->input('max_price') : null;
    $sort = $request->string('sort')->toString();

    $productsQuery = $shop->products()
        ->with(['images', 'category', 'fabric'])
        ->when($categoryId, fn ($builder) => $builder->where('category_id', $categoryId))
        ->when($fabricId, fn ($builder) => $builder->where('fabric_id', $fabricId))
        ->when($colour !== '', fn ($builder) => $builder->where('colour', $colour))
        ->when($minPrice !== null, fn ($builder) => $builder->where('wholesale_price', '>=', $minPrice))
        ->when($maxPrice !== null, fn ($builder) => $builder->where('wholesale_price', '<=', $maxPrice))
        ->when($query !== '', fn ($builder) => $builder->where(fn ($search) => $search
            ->where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->orWhere('colour', 'like', "%{$query}%")));

    match ($sort) {
        'price_asc' => $productsQuery->orderBy('wholesale_price'),
        'price_desc' => $productsQuery->orderByDesc('wholesale_price'),
        default => $productsQuery->latest(),
    };

    return [
        'products' => $productsQuery->paginate(12)->withQueryString(),
        'categories' => $shop->categories()->orderBy('sort_order')->get(),
        'fabrics' => Fabric::orderBy('name')->get(),
        'colours' => $shop->products()->whereNotNull('colour')->where('colour', '!=', '')->distinct()->orderBy('colour')->pluck('colour'),
    ];
};

Route::get('/', function (Request $request) {
    $query = $request->string('q')->trim()->toString();
    $shops = Shop::active()->withCount('products')->when($query, fn ($builder) => $builder->where('name', 'like', "%{$query}%"))->orderBy('name')->get();

    return view('market.index', compact('shops', 'query'));
})->name('market.index');

Route::get('/shops/{shop:slug}', function (Request $request, Shop $shop) use ($catalogueData) {
    abort_unless($shop->status === 'active', 404);
    $template = $shop->storefrontTemplate();

    return view(Shop::storefrontViewFor($template), ['shop' => $shop, 'template' => $template, 'templateDetails' => Shop::templateDetails()[$template]] + $catalogueData($request, $shop));
})->name('shops.show');

Route::get('/shops/{shop:slug}/products/{product:slug}', function (Shop $shop, Product $product) {
    abort_unless($shop->status === 'active' && $product->shop_id === $shop->id, 404);
    $product->load(['images', 'category', 'fabric']);
    $template = $shop->storefrontTemplate();

    return view(Shop::productStorefrontViewFor($template), ['shop' => $shop, 'product' => $product, 'template' => $template, 'templateDetails' => Shop::templateDetails()[$template]]);
})->name('shops.products.show');
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');

    Route::view('/seller/signup', 'auth.seller-signup')->name('seller.signup');
    Route::post('/seller/signup', function (Request $request) {
        $data = $request->validate([
            'seller_name' => ['required', 'string', 'max:255'],
            'seller_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'seller_password' => ['required', 'string', 'min:12', 'confirmed'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', 'unique:shops,slug'],
        ]);

        DB::transaction(function () use ($data): void {
            $seller = User::create([
                'name' => $data['seller_name'],
                'email' => $data['seller_email'],
                'password' => Hash::make($data['seller_password']),
                'role' => 'seller',
            ]);

            $seller->shop()->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'status' => 'pending',
            ]);
        });

        return redirect()->route('login')->with('status', 'Application received. An admin must activate your shop before it appears in the market.');
    })->middleware('throttle:seller-signup')->name('seller.signup.store');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->isAdmin() ? '/admin' : '/seller');
    })->middleware('throttle:login');
});
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('market.index');
})->middleware('auth')->name('logout');
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', fn () => view('admin.dashboard', ['shops' => Shop::withCount('products')->latest()->get(), 'productCount' => Product::count(), 'sellerCount' => User::where('role', 'seller')->count()]))->name('admin.dashboard');
    Route::post('/shops', function (Request $request) {
        $data = $request->validate(['seller_name' => ['required', 'string'], 'seller_email' => ['required', 'email', 'unique:users,email'], 'seller_password' => ['required', 'string', 'min:12', 'confirmed'], 'name' => ['required', 'string'], 'slug' => ['required', 'alpha_dash', 'unique:shops,slug']]);
        $seller = User::create(['name' => $data['seller_name'], 'email' => $data['seller_email'], 'password' => Hash::make($data['seller_password']), 'role' => 'seller']);
        $seller->shop()->create(['name' => $data['name'], 'slug' => $data['slug'], 'status' => 'pending']);

        return back()->with('status', 'Seller and pending shop created.');
    })->name('admin.shops.store');
    Route::patch('/shops/{shop}', function (Request $request, Shop $shop) {
        $shop->update($request->validate(['status' => ['required', Rule::in(['pending', 'active', 'inactive'])], 'category_limit' => ['nullable', 'integer', 'min:1'], 'product_limit' => ['nullable', 'integer', 'min:1'], 'product_image_limit' => ['nullable', 'integer', 'min:1', 'max:10']]));

        return back()->with('status', 'Shop updated.');
    })->name('admin.shops.update');
});
Route::middleware(['auth', 'role:seller'])->prefix('seller')->group(function () use ($catalogueData) {
    Route::get('/', function (Request $request) {
        $shop = $request->user()->shop;
        abort_unless($shop, 404);

        return view('seller.dashboard', compact('shop'));
    })->name('seller.dashboard');
    Route::get('/design', function (Request $request) {
        $shop = $request->user()->shop;
        abort_unless($shop, 404);

        return view('seller.design', ['shop' => $shop, 'templates' => Shop::templates(), 'templateDetails' => Shop::templateDetails()]);
    })->name('seller.design');
    Route::get('/design/{template}/preview', function (Request $request, string $template) use ($catalogueData) {
        abort_unless(Shop::supportsTemplate($template), 404);
        $shop = $request->user()->shop;
        abort_unless($shop, 404);

        return view(Shop::storefrontViewFor($template), ['shop' => $shop, 'template' => $template, 'templateDetails' => Shop::templateDetails()[$template], 'previewingTemplate' => true] + $catalogueData($request, $shop));
    })->name('seller.design.preview');
    Route::put('/design', function (Request $request) {
        $shop = $request->user()->shop;
        abort_unless($shop, 404);
        $data = $request->validate(['template' => ['required', 'string', Rule::in(array_keys(Shop::templates()))]]);
        $shop->update(['template' => $data['template']]);

        return redirect()->route('seller.design')->with('status', 'Storefront template updated. Your catalogue data is unchanged.');
    })->name('seller.design.update');
    Route::post('/categories', function (Request $request) {
        $shop = $request->user()->shop;
        abort_unless($shop, 404);
        abort_if($shop->categories()->count() >= $shop->category_limit, 422, 'Category limit reached.');
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $shop->categories()->create(['name' => $data['name'], 'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5))]);

        return back()->with('status', 'Category created.');
    })->name('seller.categories.store');
    Route::post('/products', function (Request $request) {
        $shop = $request->user()->shop;
        abort_unless($shop, 404);
        abort_if($shop->products()->count() >= $shop->product_limit, 422, 'Product limit reached.');
        $data = $request->validate(['category_id' => ['required', Rule::exists('categories', 'id')->where('shop_id', $shop->id)], 'name' => ['required', 'string', 'max:255'], 'code' => ['required', 'string', 'max:100', Rule::unique('products', 'code')->where('shop_id', $shop->id)], 'wholesale_price' => ['nullable', 'numeric', 'min:0'], 'minimum_order_quantity' => ['required', 'integer', 'min:1'], 'availability' => ['required', Rule::in(['available', 'out_of_stock', 'on_enquiry'])]]);
        $shop->products()->create($data + ['slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5)), 'blouse_piece' => 'not_specified']);

        return back()->with('status', 'Product created.');
    })->name('seller.products.store');
});
