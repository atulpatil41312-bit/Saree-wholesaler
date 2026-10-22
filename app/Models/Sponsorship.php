<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\SponsorshipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sponsorship extends Model
{
    /** @use HasFactory<SponsorshipFactory> */
    use HasFactory;

    protected $fillable = ['sponsor_shop_id', 'retailer_shop_id', 'invitation_id', 'status', 'starts_at', 'expires_at', 'grace_until', 'independently_paid_at', 'ended_at', 'reason'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'grace_until' => 'datetime',
            'independently_paid_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function sponsorShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'sponsor_shop_id');
    }

    public function retailerShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'retailer_shop_id');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function isActiveAt(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        if ($this->independently_paid_at !== null) {
            return true;
        }

        return match ($this->status) {
            'active' => $this->expires_at->greaterThanOrEqualTo($at),
            'grace' => $this->grace_until?->greaterThanOrEqualTo($at) ?? false,
            default => false,
        };
    }
}
