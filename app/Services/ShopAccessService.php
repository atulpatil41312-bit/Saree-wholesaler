<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;

class ShopAccessService
{
    public function canManage(User $user, Shop $shop, string $capability = 'manage_catalogue'): bool
    {
        return $user->canManageShop($shop, $capability);
    }

    /**
     * @throws AuthorizationException
     */
    public function authorize(User $user, Shop $shop, string $capability = 'manage_catalogue'): void
    {
        if (! $this->canManage($user, $shop, $capability)) {
            throw new AuthorizationException('You do not have access to this shop resource.');
        }
    }

    public function activeSubscription(Shop $shop, ?CarbonInterface $at = null): ?Subscription
    {
        $at ??= now();

        return $shop->subscriptions()
            ->with(['plan', 'planVersion'])
            ->latest('paid_through_at')
            ->get()
            ->first(fn (Subscription $subscription): bool => $subscription->hasAccess($at));
    }

    public function isEntitled(Shop $shop, ?CarbonInterface $at = null): bool
    {
        if ($shop->status !== 'active' || $shop->suspended_at !== null) {
            return false;
        }

        if (! $shop->subscriptions()->exists()) {
            return true;
        }

        return $this->activeSubscription($shop, $at) !== null;
    }

    public function planKey(Shop $shop, ?CarbonInterface $at = null): ?string
    {
        return $this->activeSubscription($shop, $at)?->plan?->key;
    }

    /**
     * @return array<string, mixed>
     */
    public function limits(Shop $shop, ?CarbonInterface $at = null): array
    {
        $limits = $shop->entitlementLimits($at);
        $subscription = $this->activeSubscription($shop, $at);

        if ($subscription?->planVersion?->template_keys !== null) {
            $limits['template_keys'] = $subscription->planVersion->template_keys;
        }

        return $limits;
    }

    /**
     * @return array<int, string>
     */
    public function allowedTemplates(Shop $shop, ?CarbonInterface $at = null): array
    {
        $templates = $this->limits($shop, $at)['template_keys'] ?? array_keys(Shop::templatesForType($shop->type));

        $templates = $templates === 'all'
            ? array_keys(Shop::templatesForType($shop->type))
            : array_values(array_intersect($templates, array_keys(Shop::templatesForType($shop->type))));

        return $templates;
    }

    public function canUseTemplate(Shop $shop, string $template, ?CarbonInterface $at = null): bool
    {
        if ($shop->subscriptions()->exists() && ! $this->isEntitled($shop, $at)) {
            return false;
        }

        return in_array($template, $this->allowedTemplates($shop, $at), true);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function audit(?User $actor, ?Shop $shop, string $action, object|string|null $subject = null, array $context = []): AuditLog
    {
        return AuditLog::query()->create([
            'user_id' => $actor?->id,
            'shop_id' => $shop?->id,
            'action' => $action,
            'subject_type' => is_object($subject) ? $subject::class : $subject,
            'subject_id' => is_object($subject) && isset($subject->id) ? $subject->id : null,
            'context' => $context,
            'ip_address' => request()?->ip(),
        ]);
    }
}
