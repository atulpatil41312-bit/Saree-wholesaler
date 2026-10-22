<?php

namespace App\Models;

use Database\Factories\ShopRedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopRedirect extends Model
{
    /** @use HasFactory<ShopRedirectFactory> */
    use HasFactory;

    protected $fillable = ['shop_id', 'shop_type', 'old_slug', 'redirect_to'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
