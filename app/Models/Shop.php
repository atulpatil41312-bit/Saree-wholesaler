<?php

namespace App\Models;

use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    /** @use HasFactory<ShopFactory> */
    use HasFactory;

    public const DEFAULT_TEMPLATE = 'classic';

    public const VASTRA_EMERALD_TEMPLATE = 'vastra-emerald';

    public const ROYAL_NOIR_TEMPLATE = 'royal-noir';

    public const ROSE_ATELIER_TEMPLATE = 'rose-atelier';

    public const INDIGO_EDIT_TEMPLATE = 'indigo-edit';

    /** @var array<string, string> */
    public const TEMPLATES = [
        self::DEFAULT_TEMPLATE => 'WholeMarket Classic',
        self::VASTRA_EMERALD_TEMPLATE => 'Vastra Emerald',
        self::ROYAL_NOIR_TEMPLATE => 'Royal Noir',
        self::ROSE_ATELIER_TEMPLATE => 'Rose Atelier',
        self::INDIGO_EDIT_TEMPLATE => 'Indigo Edit',
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
    ];

    protected $attributes = [
        'template' => self::DEFAULT_TEMPLATE,
    ];

    protected $fillable = ['name', 'slug', 'status', 'template', 'market_category', 'logo_path', 'banner_path', 'description', 'address', 'shop_number', 'opening_hours', 'phone', 'whatsapp', 'email', 'instagram_url', 'facebook_url', 'category_limit', 'product_limit', 'product_image_limit'];

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

    public function storefrontTemplate(): string
    {
        return self::supportsTemplate($this->template) ? $this->template : self::DEFAULT_TEMPLATE;
    }

    public static function storefrontViewFor(string $template): string
    {
        return match ($template) {
            self::VASTRA_EMERALD_TEMPLATE => 'shop.vastra-emerald',
            self::ROYAL_NOIR_TEMPLATE, self::ROSE_ATELIER_TEMPLATE, self::INDIGO_EDIT_TEMPLATE => 'shop.saree-template-pack',
            default => 'shop.show',
        };
    }

    public static function productStorefrontViewFor(string $template): string
    {
        return match ($template) {
            self::VASTRA_EMERALD_TEMPLATE => 'shop.vastra-emerald-product',
            self::ROYAL_NOIR_TEMPLATE, self::ROSE_ATELIER_TEMPLATE, self::INDIGO_EDIT_TEMPLATE => 'shop.saree-template-pack-product',
            default => 'shop.product',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
