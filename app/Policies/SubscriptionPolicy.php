<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user, Shop $shop): bool
    {
        return $user->can('manageBilling', $shop);
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->can('manageBilling', $subscription->shop);
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->can('manageBilling', $subscription->shop);
    }
}
