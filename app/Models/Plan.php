<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    public const Basic = 'basic';

    public const Normal = 'normal';

    public const Pro = 'pro';

    public const RetailerBasic = 'retailer-basic';

    protected $fillable = ['key', 'name', 'shop_type', 'signup_enabled', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'signup_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PlanVersion::class);
    }

    public function currentVersion(): HasOne
    {
        return $this->hasOne(PlanVersion::class)->ofMany('version', 'max');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
