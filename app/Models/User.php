<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function ownedShops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ShopMembership::class);
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_memberships')
            ->withPivot(['role', 'capabilities'])
            ->withTimestamps();
    }

    public function dashboardShop(): ?Shop
    {
        $membership = $this->memberships()
            ->with('shop')
            ->orderByRaw("case when role = 'owner' then 0 else 1 end")
            ->first();

        return $membership?->shop ?? $this->shop;
    }

    public function membershipFor(Shop $shop): ?ShopMembership
    {
        return $this->memberships()->where('shop_id', $shop->id)->first();
    }

    public function canManageShop(Shop $shop, string $capability = 'manage_catalogue'): bool
    {
        if ($this->isAdmin() || $shop->user_id === $this->id) {
            return true;
        }

        return $this->membershipFor($shop)?->hasCapability($capability) ?? false;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isWholesaler(): bool
    {
        return in_array($this->role, ['seller', 'wholesaler'], true);
    }

    public function isRetailer(): bool
    {
        return $this->role === 'retailer';
    }
}
