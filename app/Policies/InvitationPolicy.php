<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\Shop;
use App\Models\User;

class InvitationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user, Shop $shop): bool
    {
        return $user->can('manageSponsorships', $shop);
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return $user->can('manageSponsorships', $invitation->sponsorShop);
    }

    public function create(User $user, Shop $shop): bool
    {
        return $user->can('manageSponsorships', $shop);
    }

    public function revoke(User $user, Invitation $invitation): bool
    {
        return $user->can('manageSponsorships', $invitation->sponsorShop);
    }

    public function accept(User $user, Invitation $invitation): bool
    {
        return $invitation->isAvailable() && strcasecmp($user->email, $invitation->email) === 0;
    }
}
