<?php

namespace App\Policies;

use App\Models\Sponsorship;
use App\Models\User;

class SponsorshipPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Sponsorship $sponsorship): bool
    {
        return $user->can('manageSponsorships', $sponsorship->sponsorShop)
            || $user->can('view', $sponsorship->retailerShop);
    }

    public function renew(User $user, Sponsorship $sponsorship): bool
    {
        return $user->can('manageSponsorships', $sponsorship->sponsorShop)
            && $sponsorship->independently_paid_at === null;
    }

    public function revoke(User $user, Sponsorship $sponsorship): bool
    {
        return $user->can('manageSponsorships', $sponsorship->sponsorShop)
            && $sponsorship->independently_paid_at === null;
    }

    public function viewRetailerPrivateData(User $user, Sponsorship $sponsorship): bool
    {
        return $user->can('view', $sponsorship->retailerShop);
    }
}
