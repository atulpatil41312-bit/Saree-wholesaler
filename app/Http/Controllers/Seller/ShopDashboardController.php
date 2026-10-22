<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Fabric;
use App\Models\Shop;
use App\Models\User;
use App\Services\ShopAccessService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShopDashboardController extends Controller
{
    private const DESIGN_COLOUR_KEYS = ['heritage', 'ruby', 'indigo'];

    private const DESIGN_FONT_KEYS = ['editorial', 'classic', 'modern'];

    /** @var array<string, array<int, string>> */
    private const DESIGN_SECTION_ORDERS = [
        'featured-story-contact' => ['featured', 'story', 'contact'],
        'story-featured-contact' => ['story', 'featured', 'contact'],
        'featured-contact-story' => ['featured', 'contact', 'story'],
    ];

    public function __construct(private ShopAccessService $access) {}

    public function index(Request $request): View
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop);

        $shop->load(['categories', 'products', 'settings']);
        $limits = $this->access->limits($shop);
        $subscription = $this->access->activeSubscription($shop);
        $sponsorships = $shop->isWholesaler()
            ? $shop->sponsorships()->with('retailerShop')->latest('expires_at')->get()
            : collect();

        return view('seller.dashboard', compact('shop', 'limits', 'subscription', 'sponsorships'));
    }

    public function design(Request $request): View
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_branding');
        $allowedTemplates = $this->access->allowedTemplates($shop);
        $templates = array_filter(
            Shop::templates(),
            fn (string $key): bool => in_array($key, $allowedTemplates, true),
            ARRAY_FILTER_USE_KEY,
        );
        $shop->loadMissing('settings');

        return view('seller.design', [
            'shop' => $shop,
            'templates' => $templates,
            'templateDetails' => Shop::templateDetails(),
            'designSettings' => $shop->settings,
            'wholesaleAtelierAvailable' => array_key_exists(Shop::WHOLESALE_ATELIER_TEMPLATE, $templates),
        ]);
    }

    public function preview(Request $request, string $template): View
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_branding');
        abort_unless(Shop::supportsTemplate($template), 404);
        abort_unless($this->access->canUseTemplate($shop, $template), 403);

        return view(Shop::storefrontViewFor($template), [
            'shop' => $shop,
            'template' => $template,
            'templateDetails' => Shop::templateDetails()[$template],
            'previewingTemplate' => true,
            ...$this->catalogueData($request, $shop),
        ]);
    }

    public function updateDesign(Request $request): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_branding');
        $data = $request->validate([
            'template' => ['required', 'string', Rule::in(array_keys(Shop::templates()))],
        ]);

        if (! $this->access->canUseTemplate($shop, $data['template'])) {
            throw ValidationException::withMessages([
                'template' => 'This template is not included with the current plan.',
            ]);
        }

        $shop->update(['template' => $data['template']]);
        $shop->settings()->updateOrCreate([], ['template_key' => $data['template']]);
        $this->access->audit($request->user(), $shop, 'shop.template-updated', $shop, [
            'template' => $data['template'],
        ]);

        return redirect()->route('seller.design')->with('status', 'Storefront template updated. Your catalogue data is unchanged.');
    }

    public function updateDesignSettings(Request $request): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_branding');

        abort_unless($this->access->canUseTemplate($shop, Shop::WHOLESALE_ATELIER_TEMPLATE), 403);

        $data = $request->validate([
            'colour_key' => ['required', 'string', Rule::in(self::DESIGN_COLOUR_KEYS)],
            'font_key' => ['required', 'string', Rule::in(self::DESIGN_FONT_KEYS)],
            'hero_title' => ['nullable', 'string', 'max:100'],
            'hero_subtitle' => ['nullable', 'string', 'max:280'],
            'hero_cta_label' => ['nullable', 'string', 'max:40'],
            'section_order' => ['required', 'string', Rule::in(array_keys(self::DESIGN_SECTION_ORDERS))],
            'show_featured' => ['nullable', 'boolean'],
            'show_story' => ['nullable', 'boolean'],
            'show_contact' => ['nullable', 'boolean'],
            'show_prices' => ['nullable', 'boolean'],
        ]);

        $shop->settings()->updateOrCreate([], [
            'template_key' => $shop->storefrontTemplate(),
            'colour_key' => $data['colour_key'],
            'font_key' => $data['font_key'],
            'show_prices' => $request->boolean('show_prices'),
            'sections' => [
                'hero_title' => $data['hero_title'] ?: null,
                'hero_subtitle' => $data['hero_subtitle'] ?: null,
                'hero_cta_label' => $data['hero_cta_label'] ?: null,
                'visible' => [
                    'featured' => $request->boolean('show_featured'),
                    'story' => $request->boolean('show_story'),
                    'contact' => $request->boolean('show_contact'),
                ],
                'order' => self::DESIGN_SECTION_ORDERS[$data['section_order']],
            ],
        ]);
        $this->access->audit($request->user(), $shop, 'shop.design-settings-updated', $shop, [
            'template' => Shop::WHOLESALE_ATELIER_TEMPLATE,
        ]);

        return redirect()->route('seller.design')->with('status', 'Wholesale Atelier settings saved.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $limits = $this->access->limits($shop);

        abort_if($shop->categories()->count() >= (int) $limits['category_limit'], 422, 'Category limit reached.');

        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $category = $shop->categories()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5)),
        ]);
        $this->access->audit($request->user(), $shop, 'catalogue.category-created', $category);

        return back()->with('status', 'Category created.');
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $shop = $this->dashboardShop($request);
        $this->access->authorize($request->user(), $shop, 'manage_catalogue');
        $limits = $this->access->limits($shop);

        abort_if($shop->products()->count() >= (int) $limits['product_limit'], 422, 'Product limit reached.');

        $data = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('shop_id', $shop->id)],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', Rule::unique('products', 'code')->where('shop_id', $shop->id)],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_order_quantity' => ['required', 'integer', 'min:1'],
            'pack_size' => ['nullable', 'integer', 'min:1'],
            'availability' => ['required', Rule::in(['available', 'out_of_stock', 'on_enquiry'])],
        ]);
        $pricePaise = ($data['wholesale_price'] ?? null) === null
            ? null
            : (int) round((float) $data['wholesale_price'] * 100);
        $product = $shop->products()->create($data + [
            'price_paise' => $pricePaise,
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5)),
            'blouse_piece' => 'not_specified',
        ]);
        $this->access->audit($request->user(), $shop, 'catalogue.product-created', $product);

        return back()->with('status', 'Product created.');
    }

    private function dashboardShop(Request $request): Shop
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $shop = $user->dashboardShop();
        abort_unless($shop instanceof Shop, 404, 'No shop is associated with this account.');

        return $shop;
    }

    /**
     * @return array{products: LengthAwarePaginator, categories: \Illuminate\Database\Eloquent\Collection<int, Category>, fabrics: \Illuminate\Database\Eloquent\Collection<int, Fabric>, colours: Collection<int, string>}
     */
    private function catalogueData(Request $request, Shop $shop): array
    {
        $query = $request->string('q')->trim()->toString();
        $categoryId = $request->integer('category') ?: null;
        $products = $shop->products()
            ->with(['images', 'category', 'fabric'])
            ->when($categoryId, fn ($builder) => $builder->where('category_id', $categoryId))
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(fn ($search) => $search
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%"));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return [
            'products' => $products,
            'categories' => $shop->categories()->orderBy('sort_order')->get(),
            'fabrics' => Fabric::query()->orderBy('name')->get(),
            'colours' => $shop->products()->whereNotNull('colour')->where('colour', '!=', '')->distinct()->orderBy('colour')->pluck('colour'),
        ];
    }
}
