<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->memberships()->exists() || $user->ownedShops()->exists();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->canManageShop($product->shop, 'manage_catalogue');
    }

    public function create(User $user, Shop $shop): bool
    {
        return $user->canManageShop($shop, 'manage_catalogue');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->canManageShop($product->shop, 'manage_catalogue');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->canManageShop($product->shop, 'manage_catalogue');
    }
}
