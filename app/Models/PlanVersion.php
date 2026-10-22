<?php

namespace App\Models;

use Database\Factories\PlanVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanVersion extends Model
{
    /** @use HasFactory<PlanVersionFactory> */
    use HasFactory;

    protected $fillable = ['plan_id', 'version', 'price_paise', 'billing_interval', 'limits', 'template_keys', 'active_from', 'active_to'];

    protected function casts(): array
    {
        return [
            'limits' => 'array',
            'template_keys' => 'array',
            'active_from' => 'datetime',
            'active_to' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
