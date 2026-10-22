<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PlanManagementController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\SubscriptionManagementController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\Public\MarketController;
use App\Http\Controllers\Public\StorefrontController;
use App\Http\Controllers\Retailer\InvitationController;
use App\Http\Controllers\Seller\ShopDashboardController;
use App\Http\Controllers\Seller\ShopProfileController;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

$reservedShopSlugs = [
    'admin', 'api', 'billing', 'dashboard', 'email', 'enquiries', 'favicon', 'forgot-password',
    'login', 'logout', 'manifest', 'password', 'plans', 'reset-password', 'retailer', 'robots',
    'seller', 'shops', 'storage', 'sw', 'templates', 'up', 'verify', 'wholesaler',
];

Route::get('/', [MarketController::class, 'index'])->name('market.index');

Route::middleware('guest')->group(function () use ($reservedShopSlugs): void {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(Auth::user()->isAdmin() ? route('admin.dashboard') : route('seller.dashboard'));
    })->middleware('throttle:login')->name('login.store');

    Route::view('/seller/signup', 'auth.seller-signup')->name('seller.signup');
    Route::post('/seller/signup', function (Request $request) use ($reservedShopSlugs) {
        $data = $request->validate([
            'seller_name' => ['required', 'string', 'max:255'],
            'seller_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'seller_password' => ['required', 'string', 'min:12', 'confirmed'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'alpha_dash',
                'max:255',
                Rule::notIn($reservedShopSlugs),
                Rule::unique('shops', 'slug')->where(fn ($query) => $query->where('type', 'wholesaler')),
            ],
        ]);

        $seller = DB::transaction(function () use ($data): User {
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
                'status' => 'pending',
                'email' => $seller->email,
            ]);
            $shop->memberships()->create([
                'user_id' => $seller->id,
                'role' => 'owner',
                'capabilities' => ['manage_catalogue', 'manage_branding', 'manage_billing'],
            ]);
            $shop->settings()->create(['template_key' => Shop::DEFAULT_TEMPLATE]);

            return $seller;
        }, 3);
        $seller->sendEmailVerificationNotification();

        return redirect()->route('login')->with('status', 'Application received. Verify your email, then an admin can approve your shop and assign a plan.');
    })->middleware('throttle:seller-signup')->name('seller.signup.store');

    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    })->middleware('throttle:password-reset')->name('password.email');
    Route::get('/reset-password/{token}', fn (string $token) => view('auth.reset-password', compact('token')))->name('password.reset');
    Route::post('/reset-password', function (Request $request) {
        $credentials = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:12'],
        ]);
        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    })->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('market.index');
    })->name('logout');

    Route::get('/email/verify', fn () => view('auth.verify-email'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->fulfill();
        }

        return redirect()->route('seller.dashboard')->with('status', 'Email address verified.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('seller.dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent.');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('/dashboard', [ShopDashboardController::class, 'index'])->name('seller.dashboard');
    Route::get('/dashboard/design', [ShopDashboardController::class, 'design'])->name('seller.design');
    Route::get('/dashboard/design/{template}/preview', [ShopDashboardController::class, 'preview'])->name('seller.design.preview');
    Route::put('/dashboard/design', [ShopDashboardController::class, 'updateDesign'])->name('seller.design.update');
    Route::put('/dashboard/design/settings', [ShopDashboardController::class, 'updateDesignSettings'])->name('seller.design.settings.update');
    Route::post('/dashboard/categories', [ShopDashboardController::class, 'storeCategory'])->name('seller.categories.store');
    Route::post('/dashboard/products', [ShopDashboardController::class, 'storeProduct'])->name('seller.products.store');
    Route::get('/dashboard/profile', [ShopProfileController::class, 'edit'])->name('seller.profile.edit');
    Route::put('/dashboard/profile', [ShopProfileController::class, 'update'])->name('seller.profile.update');
    Route::get('/dashboard/enquiries', [EnquiryController::class, 'index'])->name('seller.enquiries.index');
    Route::patch('/dashboard/enquiries/{enquiry}', [EnquiryController::class, 'update'])->name('seller.enquiries.update');
    Route::get('/dashboard/sponsorships', [InvitationController::class, 'index'])->name('seller.sponsorships.index');
    Route::post('/dashboard/sponsorships', [InvitationController::class, 'store'])->name('seller.sponsorships.store');
    Route::delete('/dashboard/sponsorships/{invitation}', [InvitationController::class, 'revoke'])->name('seller.sponsorships.revoke');

    Route::redirect('/seller', '/dashboard')->name('seller.legacy.dashboard');
    Route::redirect('/seller/design', '/dashboard/design');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/shops', [AdminShopController::class, 'store'])->name('admin.shops.store');
    Route::patch('/shops/{shop:id}', [AdminShopController::class, 'update'])->name('admin.shops.update');

    Route::get('/plans', [PlanManagementController::class, 'index'])->name('admin.plans.index');
    Route::post('/plans', [PlanManagementController::class, 'store'])->name('admin.plans.store');
    Route::patch('/plans/{plan}', [PlanManagementController::class, 'update'])->name('admin.plans.update');
    Route::post('/plans/{plan}/versions', [PlanManagementController::class, 'storeVersion'])->name('admin.plans.versions.store');

    Route::get('/subscriptions', [SubscriptionManagementController::class, 'index'])->name('admin.subscriptions.index');
    Route::post('/subscriptions', [SubscriptionManagementController::class, 'store'])->name('admin.subscriptions.store');
    Route::post('/subscriptions/{subscription}/payments', [SubscriptionManagementController::class, 'recordManualPayment'])->name('admin.subscriptions.payments.store');
    Route::post('/billing/events', [SubscriptionManagementController::class, 'recordProviderEvent'])->name('admin.billing.events.store');
});

Route::get('/retailer/invitations/{token}', [InvitationController::class, 'show'])->name('retailer.invitations.show');
Route::post('/retailer/invitations/{token}', [InvitationController::class, 'accept'])->name('retailer.invitations.accept');

Route::post('/{type}/{shop}/enquiries', [EnquiryController::class, 'store'])
    ->whereIn('type', ['wholesaler', 'retailer'])
    ->where('shop', '[A-Za-z0-9_-]+')
    ->name('enquiries.store');

Route::get('/wholesaler/{shop}/products/{product}', [StorefrontController::class, 'wholesalerProduct'])
    ->where(['shop' => '[A-Za-z0-9_-]+', 'product' => '[A-Za-z0-9_-]+'])
    ->name('shops.products.show');
Route::get('/wholesaler/{shop}', [StorefrontController::class, 'wholesaler'])
    ->where('shop', '[A-Za-z0-9_-]+')
    ->name('shops.show');
Route::get('/retailer/{shop}/products/{product}', [StorefrontController::class, 'retailerProduct'])
    ->where(['shop' => '[A-Za-z0-9_-]+', 'product' => '[A-Za-z0-9_-]+'])
    ->name('retailer.shops.products.show');
Route::get('/retailer/{shop}', [StorefrontController::class, 'retailer'])
    ->where('shop', '[A-Za-z0-9_-]+')
    ->name('retailer.shops.show');

Route::get('/shops/{shopSlug}/products/{productSlug}', [StorefrontController::class, 'legacyProduct'])
    ->where(['shopSlug' => '[A-Za-z0-9_-]+', 'productSlug' => '[A-Za-z0-9_-]+'])
    ->name('shops.legacy.products');
Route::get('/shops/{slug}', [StorefrontController::class, 'legacyShop'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('shops.legacy.show');
Route::get('/{shopSlug}/products/{productSlug}', [StorefrontController::class, 'legacyProduct'])
    ->where(['shopSlug' => '[A-Za-z0-9_-]+', 'productSlug' => '[A-Za-z0-9_-]+'])
    ->name('shops.root.legacy.products');
Route::get('/{slug}', [StorefrontController::class, 'legacyShop'])
    ->where('slug', '[A-Za-z0-9_-]+')
    ->name('shops.root.legacy.show');
