<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected $fillable = ['shop_id', 'plan_id', 'plan_version_id', 'status', 'source', 'billing_interval', 'started_at', 'paid_through_at', 'grace_until', 'cancelled_at', 'overrides'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'paid_through_at' => 'datetime',
            'grace_until' => 'datetime',
            'cancelled_at' => 'datetime',
            'overrides' => 'array',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function planVersion(): BelongsTo
    {
        return $this->belongsTo(PlanVersion::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function hasAccess(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if (! in_array($this->status, ['active', 'grace'], true)) {
            return false;
        }

        return $this->paid_through_at?->greaterThanOrEqualTo($at)
            || $this->grace_until?->greaterThanOrEqualTo($at);
    }
}
