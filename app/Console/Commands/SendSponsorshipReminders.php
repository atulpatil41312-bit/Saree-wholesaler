<?php

namespace App\Console\Commands;

use App\Models\NotificationDelivery;
use App\Models\Sponsorship;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-sponsorship-reminders')]
#[Description('Queue deduplicated local sponsorship-expiry reminder records')]
class SendSponsorshipReminders extends Command
{
    public function handle(): int
    {
        $created = 0;

        foreach ([7, 3, 1] as $days) {
            $startsAt = now()->startOfDay()->addDays($days);
            $endsAt = $startsAt->copy()->endOfDay();

            Sponsorship::query()
                ->with('retailerShop')
                ->where('status', 'active')
                ->whereNull('independently_paid_at')
                ->whereBetween('expires_at', [$startsAt, $endsAt])
                ->orderBy('id')
                ->each(function (Sponsorship $sponsorship) use ($days, &$created): void {
                    $expiresAt = $sponsorship->expires_at;
                    $delivery = NotificationDelivery::query()->firstOrCreate(
                        [
                            'dedupe_key' => "sponsorship:{$sponsorship->id}:expiry:{$days}:{$expiresAt->toDateString()}",
                        ],
                        [
                            'user_id' => $sponsorship->retailerShop->user_id,
                            'shop_id' => $sponsorship->retailer_shop_id,
                            'channel' => 'log',
                            'notification_type' => 'sponsorship_expiry_reminder',
                            'scheduled_for' => now(),
                            'metadata' => [
                                'sponsorship_id' => $sponsorship->id,
                                'days_remaining' => $days,
                                'expires_at' => $expiresAt->toIso8601String(),
                            ],
                        ],
                    );

                    if ($delivery->wasRecentlyCreated) {
                        $created++;
                    }
                });
        }

        $this->info("Queued {$created} sponsorship reminder delivery record(s).");

        return self::SUCCESS;
    }
}
