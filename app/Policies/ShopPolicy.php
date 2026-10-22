<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->memberships()->exists() || $user->ownedShops()->exists();
    }

    public function view(User $user, Shop $shop): bool
    {
        return $this->isMember($user, $shop);
    }

    public function update(User $user, Shop $shop): bool
    {
        return $user->canManageShop($shop, 'manage_branding');
    }

    public function manageCatalogue(User $user, Shop $shop): bool
    {
        return $user->canManageShop($shop, 'manage_catalogue');
    }

    public function viewEnquiries(User $user, Shop $shop): bool
    {
        return $user->canManageShop($shop, 'manage_enquiries');
    }

    public function manageBilling(User $user, Shop $shop): bool
    {
        return $shop->user_id === $user->id || $user->membershipFor($shop)?->role === 'owner';
    }

    public function manageSponsorships(User $user, Shop $shop): bool
    {
        return $shop->isWholesaler()
            && ($shop->user_id === $user->id || $user->membershipFor($shop)?->role === 'owner');
    }

    public function manageMemberships(User $user, Shop $shop): bool
    {
        return $shop->user_id === $user->id || $user->membershipFor($shop)?->role === 'owner';
    }

    private function isMember(User $user, Shop $shop): bool
    {
        return $shop->user_id === $user->id || $user->membershipFor($shop) !== null;
    }
}
