<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\NotificationDelivery;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Sponsorship;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SponsorshipService
{
    public function __construct(private ShopAccessService $access) {}

    /**
     * @return array{0: Invitation, 1: string}
     */
    public function invite(Shop $sponsor, User $invitedBy, string $email, int $termDays): array
    {
        $this->assertAllowedTerm($termDays);

        return DB::transaction(function () use ($sponsor, $invitedBy, $email, $termDays): array {
            $sponsor = Shop::query()->lockForUpdate()->findOrFail($sponsor->id);

            $this->assertProSponsor($sponsor);

            Invitation::query()
                ->where('sponsor_shop_id', $sponsor->id)
                ->where('email', $email)
                ->where('status', 'pending')
                ->update(['status' => 'revoked']);

            $token = Str::random(64);
            $invitation = Invitation::query()->create([
                'sponsor_shop_id' => $sponsor->id,
                'invited_by' => $invitedBy->id,
                'email' => $email,
                'token_hash' => hash('sha256', $token),
                'term_days' => $termDays,
                'status' => 'pending',
                'expires_at' => now()->addDays((int) config('wholemarket.sponsorship.invitation_days', 7)),
            ]);

            NotificationDelivery::query()->firstOrCreate(
                ['dedupe_key' => "invitation:{$invitation->id}:mail"],
                [
                    'shop_id' => $sponsor->id,
                    'channel' => 'mail',
                    'notification_type' => 'retailer-invitation',
                    'scheduled_for' => now(),
                    'metadata' => ['email' => $email],
                ],
            );
            $this->access->audit($invitedBy, $sponsor, 'sponsorship.invited', $invitation, ['term_days' => $termDays]);

            return [$invitation, $token];
        }, 3);
    }

    /**
     * Create a retailer account and its sponsored shop directly from a wholesaler workspace.
     *
     * @param  array{name: string, phone: string, email: string, password: string, shop_name: string, slug: string, term_days: int, template: string}  $retailerData
     */
    public function createRetailer(Shop $sponsor, User $createdBy, array $retailerData): Shop
    {
        $this->assertAllowedTerm((int) $retailerData['term_days']);

        return DB::transaction(function () use ($sponsor, $createdBy, $retailerData): Shop {
            $sponsor = Shop::query()->lockForUpdate()->findOrFail($sponsor->id);
            $this->assertProSponsor($sponsor);

            $slotLimit = (int) ($this->access->limits($sponsor)['sponsored_retailer_limit'] ?? 0);
            if ($this->activeSponsorCount($sponsor) >= $slotLimit) {
                throw ValidationException::withMessages(['term_days' => 'This sponsor has no retailer slots available.']);
            }

            if (User::query()->where('email', $retailerData['email'])->exists()) {
                throw ValidationException::withMessages(['retailer_email' => 'This email already has a WholeMarket account.']);
            }

            $plan = Plan::query()->where('key', Plan::RetailerBasic)->where('is_active', true)->first();
            if ($plan === null) {
                throw ValidationException::withMessages(['template' => 'Retailer setup is not available yet.']);
            }

            $version = $plan->currentVersion()->first();
            $limits = $version?->limits ?? config('wholemarket.plans.retailer-basic.limits', []);
            $templateKeys = $version?->template_keys ?? ($limits['templates'] ?? ['classic']);
            if ($templateKeys !== 'all' && ! in_array($retailerData['template'], $templateKeys, true)) {
                throw ValidationException::withMessages(['template' => 'This template is not available for retailer websites.']);
            }

            $retailerOwner = User::query()->create([
                'name' => $retailerData['name'],
                'email' => $retailerData['email'],
                'password' => Hash::make($retailerData['password']),
                'role' => 'retailer',
            ]);
            $expiresAt = now()->addDays((int) $retailerData['term_days']);
            $retailer = Shop::query()->create([
                'user_id' => $retailerOwner->id,
                'type' => 'retailer',
                'name' => $retailerData['shop_name'],
                'slug' => $retailerData['slug'],
                'template' => $retailerData['template'],
                'status' => 'active',
                'approved_at' => now(),
                'email' => $retailerOwner->email,
                'phone' => $retailerData['phone'],
                'whatsapp' => $retailerData['phone'],
                'category_limit' => (int) ($limits['category_limit'] ?? 5),
                'product_limit' => (int) ($limits['product_limit'] ?? 20),
                'product_image_limit' => (int) ($limits['product_image_limit'] ?? 2),
            ]);
            $retailer->memberships()->create([
                'user_id' => $retailerOwner->id,
                'role' => 'owner',
                'capabilities' => ['manage_catalogue', 'manage_branding'],
            ]);
            $retailer->settings()->create(['template_key' => $retailerData['template']]);
            $subscription = $retailer->subscriptions()->create([
                'plan_id' => $plan->id,
                'plan_version_id' => $version?->id,
                'status' => 'active',
                'source' => 'sponsorship',
                'billing_interval' => 'sponsored',
                'started_at' => now(),
                'paid_through_at' => $expiresAt,
            ]);
            $sponsorship = Sponsorship::query()->create([
                'sponsor_shop_id' => $sponsor->id,
                'retailer_shop_id' => $retailer->id,
                'invitation_id' => null,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => $expiresAt,
            ]);
            $this->access->audit($createdBy, $sponsor, 'sponsorship.retailer-created', $retailer, [
                'retailer_user_id' => $retailerOwner->id,
                'subscription_id' => $subscription->id,
                'term_days' => (int) $retailerData['term_days'],
            ]);

            return $retailer;
        }, 3);
    }

    public function invitationForToken(string $token): ?Invitation
    {
        return Invitation::query()->where('token_hash', hash('sha256', $token))->first();
    }

    /**
     * @param  array{name: string, slug: string, template: string}  $shopData
     */
    public function accept(Invitation $invitation, User $retailerOwner, array $shopData): Shop
    {
        return DB::transaction(function () use ($invitation, $retailerOwner, $shopData): Shop {
            $invitation = Invitation::query()->lockForUpdate()->findOrFail($invitation->id);

            if (! $invitation->isAvailable()) {
                if ($invitation->status === 'pending' && $invitation->expires_at->isPast()) {
                    $invitation->update(['status' => 'expired']);
                }

                throw ValidationException::withMessages(['token' => 'This retailer invitation is no longer available.']);
            }

            $sponsor = Shop::query()->lockForUpdate()->findOrFail($invitation->sponsor_shop_id);
            $this->assertProSponsor($sponsor);

            $slotLimit = (int) ($this->access->limits($sponsor)['sponsored_retailer_limit'] ?? 0);
            if ($this->activeSponsorCount($sponsor) >= $slotLimit) {
                throw ValidationException::withMessages(['token' => 'This sponsor has no retailer slots available.']);
            }

            if (Shop::query()->where('user_id', $retailerOwner->id)->where('type', 'retailer')->exists()) {
                throw ValidationException::withMessages(['email' => 'This account already has a retailer website.']);
            }

            $plan = Plan::query()->where('key', Plan::RetailerBasic)->where('is_active', true)->first();
            if ($plan === null) {
                throw ValidationException::withMessages(['token' => 'Retailer setup is not available yet.']);
            }

            $version = $plan->currentVersion()->first();
            $limits = $version?->limits ?? config('wholemarket.plans.retailer-basic.limits', []);
            $templateKeys = $version?->template_keys ?? config('wholemarket.plans.retailer-basic.limits.templates', ['classic']);
            if ($templateKeys !== 'all' && ! in_array($shopData['template'], $templateKeys, true)) {
                throw ValidationException::withMessages(['template' => 'This template is not available for retailer websites.']);
            }

            $expiresAt = now()->addDays($invitation->term_days);
            $retailer = Shop::query()->create([
                'user_id' => $retailerOwner->id,
                'type' => 'retailer',
                'name' => $shopData['name'],
                'slug' => $shopData['slug'],
                'template' => $shopData['template'],
                'status' => 'active',
                'approved_at' => now(),
                'email' => $retailerOwner->email,
                'category_limit' => (int) ($limits['category_limit'] ?? 5),
                'product_limit' => (int) ($limits['product_limit'] ?? 20),
                'product_image_limit' => (int) ($limits['product_image_limit'] ?? 2),
            ]);
            $retailer->memberships()->create([
                'user_id' => $retailerOwner->id,
                'role' => 'owner',
                'capabilities' => ['manage_catalogue', 'manage_branding'],
            ]);
            $retailer->settings()->create(['template_key' => $shopData['template']]);
            $subscription = $retailer->subscriptions()->create([
                'plan_id' => $plan->id,
                'plan_version_id' => $version?->id,
                'status' => 'active',
                'source' => 'sponsorship',
                'billing_interval' => 'sponsored',
                'started_at' => now(),
                'paid_through_at' => $expiresAt,
            ]);
            $sponsorship = Sponsorship::query()->create([
                'sponsor_shop_id' => $sponsor->id,
                'retailer_shop_id' => $retailer->id,
                'invitation_id' => $invitation->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => $expiresAt,
            ]);
            $invitation->update([
                'retailer_shop_id' => $retailer->id,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);
            $this->access->audit($retailerOwner, $retailer, 'sponsorship.accepted', $sponsorship, [
                'subscription_id' => $subscription->id,
                'sponsor_shop_id' => $sponsor->id,
            ]);

            return $retailer;
        }, 3);
    }

    public function expireDue(?CarbonInterface $at = null): int
    {
        $at ??= now();
        $expired = 0;

        Sponsorship::query()
            ->whereNull('independently_paid_at')
            ->whereIn('status', ['active', 'grace'])
            ->where(function ($query) use ($at): void {
                $query->where(function ($active) use ($at): void {
                    $active->where('status', 'active')->where('expires_at', '<', $at);
                })->orWhere(function ($grace) use ($at): void {
                    $grace->where('status', 'grace')->whereNotNull('grace_until')->where('grace_until', '<', $at);
                });
            })
            ->each(function (Sponsorship $sponsorship) use (&$expired, $at): void {
                DB::transaction(function () use ($sponsorship, &$expired, $at): void {
                    $locked = Sponsorship::query()->lockForUpdate()->findOrFail($sponsorship->id);
                    if (
                        $locked->independently_paid_at !== null
                        || ! in_array($locked->status, ['active', 'grace'], true)
                        || $locked->isActiveAt($at)
                    ) {
                        return;
                    }

                    $locked->update(['status' => 'expired', 'ended_at' => $at, 'reason' => 'sponsorship-expired']);
                    $locked->retailerShop?->subscriptions()
                        ->where('source', 'sponsorship')
                        ->whereIn('status', ['active', 'grace'])
                        ->update(['status' => 'expired']);
                    $expired++;
                }, 3);
            });

        return $expired;
    }

    public function beginSponsorGrace(Shop $sponsor, ?CarbonInterface $at = null): int
    {
        $at ??= now();
        $graceUntil = $at->copy()->addDays((int) config('wholemarket.sponsorship.grace_days', 7));
        $updated = 0;

        Sponsorship::query()
            ->where('sponsor_shop_id', $sponsor->id)
            ->whereNull('independently_paid_at')
            ->where('status', 'active')
            ->where('expires_at', '>=', $at)
            ->each(function (Sponsorship $sponsorship) use ($graceUntil, &$updated): void {
                $sponsorship->update(['status' => 'grace', 'grace_until' => $graceUntil, 'reason' => 'sponsor-pro-ended']);
                $sponsorship->retailerShop?->subscriptions()
                    ->where('source', 'sponsorship')
                    ->where('status', 'active')
                    ->update(['status' => 'grace', 'grace_until' => $graceUntil]);
                $updated++;
            });

        return $updated;
    }

    public function activeSponsorCount(Shop $sponsor, ?CarbonInterface $at = null): int
    {
        $at ??= now();

        return Sponsorship::query()
            ->where('sponsor_shop_id', $sponsor->id)
            ->whereNull('independently_paid_at')
            ->whereIn('status', ['active', 'grace'])
            ->where(function ($query) use ($at): void {
                $query->where(function ($active) use ($at): void {
                    $active->where('status', 'active')->where('expires_at', '>=', $at);
                })->orWhere(function ($grace) use ($at): void {
                    $grace->where('status', 'grace')->where('grace_until', '>=', $at);
                });
            })
            ->count();
    }

    private function assertAllowedTerm(int $termDays): void
    {
        if (! in_array($termDays, config('wholemarket.sponsorship.allowed_terms', []), true)) {
            throw ValidationException::withMessages(['term_days' => 'Choose a 30, 60, or 90 day sponsorship term.']);
        }
    }

    private function assertProSponsor(Shop $sponsor): void
    {
        if (! $sponsor->isWholesaler() || $this->access->planKey($sponsor) !== Plan::Pro) {
            throw ValidationException::withMessages(['sponsor' => 'An active Pro wholesaler subscription is required.']);
        }
    }
}
