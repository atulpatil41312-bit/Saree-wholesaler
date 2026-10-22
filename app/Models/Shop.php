<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shop extends Model
{
    /** @use HasFactory<ShopFactory> */
    use HasFactory;

    public const DEFAULT_TEMPLATE = 'classic';

    public const VASTRA_EMERALD_TEMPLATE = 'vastra-emerald';

    public const ROYAL_NOIR_TEMPLATE = 'royal-noir';

    public const ROSE_ATELIER_TEMPLATE = 'rose-atelier';

    public const INDIGO_EDIT_TEMPLATE = 'indigo-edit';

    public const WHOLESALE_ATELIER_TEMPLATE = 'wholesaler-templates';

    /** @var array<string, string> */
    public const TEMPLATES = [
        self::DEFAULT_TEMPLATE => 'WholeMarket Classic',
        self::VASTRA_EMERALD_TEMPLATE => 'Vastra Emerald',
        self::ROYAL_NOIR_TEMPLATE => 'Royal Noir',
        self::ROSE_ATELIER_TEMPLATE => 'Rose Atelier',
        self::INDIGO_EDIT_TEMPLATE => 'Indigo Edit',
        self::WHOLESALE_ATELIER_TEMPLATE => 'Wholesale Atelier',
    ];

    /** @var array<string, array{label: string, description: string, badge: string, asset_path: string, hero_image: string, hero_heading: string, hero_emphasis: string}> */
    private const TEMPLATE_DETAILS = [
        self::DEFAULT_TEMPLATE => [
            'label' => 'WholeMarket Classic',
            'description' => 'The original WholeMarket presentation for your wholesale catalogue.',
            'badge' => 'CURRENT EDIT',
            'asset_path' => 'storefront',
            'hero_image' => 'saree.jpg',
            'hero_heading' => 'Your wholesale',
            'hero_emphasis' => 'collection.',
        ],
        self::VASTRA_EMERALD_TEMPLATE => [
            'label' => 'Vastra Emerald',
            'description' => 'An editorial emerald presentation with collection-led navigation.',
            'badge' => 'EMERALD EDIT',
            'asset_path' => 'templates/vastra-emerald',
            'hero_image' => 'saree.jpg',
            'hero_heading' => 'The timeless',
            'hero_emphasis' => 'edit.',
        ],
        self::ROYAL_NOIR_TEMPLATE => [
            'label' => 'Royal Noir',
            'description' => 'A cinematic black-and-gold storefront with image-led category panels.',
            'badge' => 'ROYAL EDIT',
            'asset_path' => 'templates/royal-noir',
            'hero_image' => 'saree.jpg',
            'hero_heading' => 'For collections with',
            'hero_emphasis' => 'presence.',
        ],
        self::ROSE_ATELIER_TEMPLATE => [
            'label' => 'Rose Atelier',
            'description' => 'A blush-and-ivory boutique with a soft editorial product collage.',
            'badge' => 'ATELIER EDIT',
            'asset_path' => 'templates/rose-atelier',
            'hero_image' => 'saree2.jpg',
            'hero_heading' => 'A little colour. A lot of',
            'hero_emphasis' => 'character.',
        ],
        self::INDIGO_EDIT_TEMPLATE => [
            'label' => 'Indigo Edit',
            'description' => 'A compact blue-and-white catalogue with a dedicated filter sidebar.',
            'badge' => 'INDIGO EDIT',
            'asset_path' => 'templates/indigo-edit',
            'hero_image' => 'saree1.jpg',
            'hero_heading' => 'Your next collection,',
            'hero_emphasis' => 'beautifully simple.',
        ],
        self::WHOLESALE_ATELIER_TEMPLATE => [
            'label' => 'Wholesale Atelier',
            'description' => 'A warm, wholesale-first catalogue with practical filters and direct buyer enquiries.',
            'badge' => 'NEW WHOLESALE',
            'asset_path' => 'templates/wholesaler-templates',
            'hero_image' => 'placeholder-hero.svg',
            'hero_heading' => 'Made for',
            'hero_emphasis' => 'serious sourcing.',
        ],
    ];

    protected $attributes = [
        'template' => self::DEFAULT_TEMPLATE,
    ];

    protected $fillable = ['user_id', 'name', 'slug', 'status', 'type', 'template', 'market_category', 'logo_path', 'banner_path', 'description', 'address', 'shop_number', 'opening_hours', 'phone', 'whatsapp', 'email', 'instagram_url', 'facebook_url', 'seo_title', 'seo_description', 'category_limit', 'product_limit', 'product_image_limit', 'approved_at', 'suspended_at', 'suspension_reason', 'storage_quota_bytes', 'storage_used_bytes'];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    /** @return array<string, string> */
    public static function templates(): array
    {
        return self::TEMPLATES;
    }

    /** @return array<string, array{label: string, description: string, badge: string, asset_path: string, hero_image: string, hero_heading: string, hero_emphasis: string}> */
    public static function templateDetails(): array
    {
        return self::TEMPLATE_DETAILS;
    }

    public static function supportsTemplate(string $template): bool
    {
        return array_key_exists($template, self::TEMPLATES);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function storefrontViewFor(string $template): string
    {
        return match ($template) {
            self::VASTRA_EMERALD_TEMPLATE => 'shop.vastra-emerald',
            self::ROYAL_NOIR_TEMPLATE, self::ROSE_ATELIER_TEMPLATE, self::INDIGO_EDIT_TEMPLATE => 'shop.saree-template-pack',
            self::WHOLESALE_ATELIER_TEMPLATE => 'shop.wholesaler-templates',
            default => 'shop.show',
        };
    }

    public static function productStorefrontViewFor(string $template): string
    {
        return match ($template) {
            self::VASTRA_EMERALD_TEMPLATE => 'shop.vastra-emerald-product',
            self::ROYAL_NOIR_TEMPLATE, self::ROSE_ATELIER_TEMPLATE, self::INDIGO_EDIT_TEMPLATE => 'shop.saree-template-pack-product',
            self::WHOLESALE_ATELIER_TEMPLATE => 'shop.wholesaler-templates-product',
            default => 'shop.product',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ShopMembership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_memberships')
            ->withPivot(['role', 'capabilities'])
            ->withTimestamps();
    }

    public function settings(): HasOne
    {
        return $this->hasOne(ShopSetting::class);
    }

    public function redirects(): HasMany
    {
        return $this->hasMany(ShopRedirect::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class, 'sponsor_shop_id');
    }

    public function sponsorship(): HasOne
    {
        return $this->hasOne(Sponsorship::class, 'retailer_shop_id');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function isWholesaler(): bool
    {
        return $this->type === 'wholesaler';
    }

    public function isRetailer(): bool
    {
        return $this->type === 'retailer';
    }

    public function publicRouteName(): string
    {
        return $this->isRetailer() ? 'retailer.shops.show' : 'shops.show';
    }

    /**
     * @param  array<string, scalar|null>  $query
     */
    public function publicUrl(array $query = []): string
    {
        return route($this->publicRouteName(), array_merge(['shop' => $this], $query));
    }

    public function productRouteName(): string
    {
        return $this->isRetailer() ? 'retailer.shops.products.show' : 'shops.products.show';
    }

    public function productUrl(Product $product): string
    {
        return route($this->productRouteName(), ['shop' => $this, 'product' => $product]);
    }

    public function membershipFor(User $user): ?ShopMembership
    {
        return $this->memberships()->where('user_id', $user->id)->first();
    }

    public function isPubliclyVisible(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if ($this->status !== 'active' || $this->approved_at === null || $this->suspended_at !== null) {
            return false;
        }

        $subscriptions = $this->relationLoaded('subscriptions')
            ? $this->subscriptions
            : $this->subscriptions()->with('planVersion')->get();

        if ($subscriptions->isEmpty()) {
            return true;
        }

        return $subscriptions->contains(fn (Subscription $subscription): bool => $subscription->hasAccess($at));
    }

    /**
     * @return array<string, mixed>
     */
    public function entitlementLimits(?CarbonInterface $at = null): array
    {
        $fallback = [
            'product_limit' => $this->product_limit,
            'category_limit' => $this->category_limit,
            'product_image_limit' => $this->product_image_limit,
            'template_keys' => array_keys(self::templates()),
            'staff_limit' => 0,
            'sponsored_retailer_limit' => 0,
        ];
        $subscription = $this->subscriptions()
            ->with('planVersion')
            ->latest('paid_through_at')
            ->get()
            ->first(fn (Subscription $candidate): bool => $candidate->hasAccess($at));

        if ($subscription === null) {
            return $fallback;
        }

        return array_replace($fallback, $subscription->planVersion?->limits ?? [], $subscription->overrides ?? []);
    }

    public function storefrontTemplate(): string
    {
        $template = $this->relationLoaded('settings')
            ? $this->settings?->template_key
            : $this->settings()->value('template_key');

        $template ??= $this->template;

        return self::supportsTemplate($template) ? $template : self::DEFAULT_TEMPLATE;
    }
}
