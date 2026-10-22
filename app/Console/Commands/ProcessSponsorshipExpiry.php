<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use App\Models\Plan;
use App\Models\Sponsorship;
use App\Services\ShopAccessService;
use App\Services\SponsorshipService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:process-sponsorship-expiry')]
#[Description('Expire invitations, retailer sponsorships, and former-Pro sponsor grace periods')]
class ProcessSponsorshipExpiry extends Command
{
    public function handle(SponsorshipService $sponsorships, ShopAccessService $access): int
    {
        $now = now();
        $expiredInvitations = Invitation::query()
            ->where('status', 'pending')
            ->where('expires_at', '<=', $now)
            ->update(['status' => 'expired']);
        $gracePeriodsStarted = 0;
        $suspendedSponsorships = 0;

        Sponsorship::query()
            ->with('sponsorShop')
            ->whereNull('independently_paid_at')
            ->whereIn('status', ['active', 'grace'])
            ->orderBy('id')
            ->each(function (Sponsorship $sponsorship) use ($access, $sponsorships, $now, &$gracePeriodsStarted, &$suspendedSponsorships): void {
                $sponsor = $sponsorship->sponsorShop;
                if ($sponsor === null) {
                    return;
                }

                if ($sponsor->suspended_at !== null) {
                    $sponsorship->update([
                        'status' => 'expired',
                        'ended_at' => $now,
                        'reason' => 'sponsor-suspended',
                    ]);
                    $sponsorship->retailerShop?->subscriptions()
                        ->where('source', 'sponsorship')
                        ->whereIn('status', ['active', 'grace'])
                        ->update(['status' => 'expired']);
                    $suspendedSponsorships++;

                    return;
                }

                if ($sponsorship->status === 'active' && $access->planKey($sponsor, $now) !== Plan::Pro) {
                    $gracePeriodsStarted += $sponsorships->beginSponsorGrace($sponsor, $now);
                }
            });

        $expiredSponsorships = $sponsorships->expireDue($now);
        $this->info("Expired {$expiredInvitations} invitation(s), expired {$expiredSponsorships} sponsorship(s), suspended {$suspendedSponsorships} sponsorship(s), and started {$gracePeriodsStarted} grace period(s).");

        return self::SUCCESS;
    }
}
