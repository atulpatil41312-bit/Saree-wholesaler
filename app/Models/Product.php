<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = ['category_id', 'saree_type_id', 'fabric_id', 'name', 'slug', 'code', 'colour', 'description', 'wholesale_price', 'price_paise', 'minimum_order_quantity', 'pack_size', 'blouse_piece', 'availability'];

    protected function casts(): array
    {
        return [
            'wholesale_price' => 'decimal:2',
            'price_paise' => 'integer',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sareeType(): BelongsTo
    {
        return $this->belongsTo(SareeType::class);
    }

    public function fabric(): BelongsTo
    {
        return $this->belongsTo(Fabric::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('availability', 'available');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function publicUrl(): string
    {
        return $this->shop->productUrl($this);
    }

    public function priceInRupees(): ?float
    {
        if ($this->price_paise !== null) {
            return $this->price_paise / 100;
        }

        return $this->wholesale_price !== null ? (float) $this->wholesale_price : null;
    }
}
