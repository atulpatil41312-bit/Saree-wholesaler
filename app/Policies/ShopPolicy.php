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
        return $user->isAdmin();
    }

    public function view(User $user, Shop $shop): bool
    {
        return $user->shop?->is($shop) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Shop $shop): bool
    {
        return $user->shop?->is($shop) ?? false;
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $user->isAdmin();
    }
}
