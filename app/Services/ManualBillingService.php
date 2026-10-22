<?php

namespace App\Services;

use App\Models\BillingEvent;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class ManualBillingService
{
    public function __construct(private ShopAccessService $access) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function recordPayment(
        Subscription $subscription,
        User $recordedBy,
        int $amountPaise,
        CarbonInterface $paidThroughAt,
        ?string $reference = null,
        array $metadata = [],
    ): Payment {
        return DB::transaction(function () use ($subscription, $recordedBy, $amountPaise, $paidThroughAt, $reference, $metadata): Payment {
            $subscription = Subscription::query()->lockForUpdate()->with('shop')->findOrFail($subscription->id);
            $payment = Payment::query()->create([
                'subscription_id' => $subscription->id,
                'shop_id' => $subscription->shop_id,
                'amount_paise' => $amountPaise,
                'status' => 'recorded',
                'method' => 'manual',
                'reference' => $reference,
                'recorded_by' => $recordedBy->id,
                'paid_at' => now(),
                'metadata' => $metadata,
            ]);
            $subscription->update([
                'status' => 'active',
                'started_at' => $subscription->started_at ?? now(),
                'paid_through_at' => $paidThroughAt,
                'grace_until' => null,
                'cancelled_at' => null,
                'source' => 'manual',
            ]);
            $this->access->audit($recordedBy, $subscription->shop, 'billing.manual-payment-recorded', $payment, [
                'subscription_id' => $subscription->id,
                'paid_through_at' => $paidThroughAt->toIso8601String(),
            ]);

            return $payment;
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordProviderEvent(string $provider, string $eventId, string $eventType, array $payload = []): BillingEvent
    {
        return DB::transaction(function () use ($provider, $eventId, $eventType, $payload): BillingEvent {
            $event = BillingEvent::query()->firstOrCreate(
                ['provider' => $provider, 'provider_event_id' => $eventId],
                [
                    'event_type' => $eventType,
                    'status' => 'received',
                    'payload' => $payload,
                ],
            );

            if ($event->wasRecentlyCreated) {
                $event->update(['status' => 'processed', 'processed_at' => now()]);
            }

            return $event;
        }, 3);
    }
}
