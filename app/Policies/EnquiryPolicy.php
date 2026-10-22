<?php

namespace App\Policies;

use App\Models\Enquiry;
use App\Models\Shop;
use App\Models\User;

class EnquiryPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user, Shop $shop): bool
    {
        return $user->can('viewEnquiries', $shop);
    }

    public function view(User $user, Enquiry $enquiry): bool
    {
        return $user->can('viewEnquiries', $enquiry->shop);
    }

    public function update(User $user, Enquiry $enquiry): bool
    {
        return $user->can('viewEnquiries', $enquiry->shop);
    }
}
