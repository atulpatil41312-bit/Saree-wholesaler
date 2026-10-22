<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invitation extends Model
{
    /** @use HasFactory<InvitationFactory> */
    use HasFactory;

    protected $fillable = ['sponsor_shop_id', 'invited_by', 'retailer_shop_id', 'email', 'token_hash', 'term_days', 'status', 'expires_at', 'accepted_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function sponsorShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'sponsor_shop_id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function retailerShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'retailer_shop_id');
    }

    public function sponsorship(): HasOne
    {
        return $this->hasOne(Sponsorship::class);
    }

    public function isAvailable(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return $this->status === 'pending' && $this->expires_at?->greaterThan($at);
    }
}
