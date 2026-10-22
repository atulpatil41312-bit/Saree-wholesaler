<?php

namespace App\Models;

use Database\Factories\ShopMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopMembership extends Model
{
    /** @use HasFactory<ShopMembershipFactory> */
    use HasFactory;

    protected $fillable = ['shop_id', 'user_id', 'role', 'capabilities'];

    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasCapability(string $capability): bool
    {
        return $this->role === 'owner' || in_array($capability, $this->capabilities ?? [], true);
    }
}
