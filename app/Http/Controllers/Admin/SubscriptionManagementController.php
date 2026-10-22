<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordManualPaymentRequest;
use App\Models\BillingEvent;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Sponsorship;
use App\Models\Subscription;
use App\Services\ManualBillingService;
use App\Services\ShopAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionManagementController extends Controller
{
    public function __construct(
        private ManualBillingService $billing,
        private ShopAccessService $access,
    ) {}

    public function index(): View
    {
        $subscriptions = Subscription::query()
            ->with(['shop', 'plan', 'planVersion', 'payments'])
            ->latest()
            ->paginate(50);
        $plans = Plan::query()->where('is_active', true)->with('currentVersion')->orderBy('sort_order')->get();
        $shops = Shop::query()->orderBy('name')->get(['id', 'name', 'type']);
        $subscriptionStats = [
            'active' => Subscription::query()->where('status', 'active')->count(),
            'grace' => Subscription::query()->where('status', 'grace')->count(),
            'attention' => Subscription::query()->whereIn('status', ['draft', 'expired', 'cancelled'])->count(),
            'total' => Subscription::query()->count(),
        ];
        $paymentStats = [
            'count' => Payment::query()->count(),
            'total_paise' => (int) Payment::query()->sum('amount_paise'),
        ];
        $recentPayments = Payment::query()
            ->with(['shop', 'subscription.plan'])
            ->latest('paid_at')
            ->latest()
            ->limit(8)
            ->get();
        $recentBillingEvents = BillingEvent::query()->latest()->limit(6)->get();

        return view('admin.subscriptions', compact(
            'subscriptions',
            'plans',
            'shops',
            'subscriptionStats',
            'paymentStats',
            'recentPayments',
            'recentBillingEvents',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_id' => ['required', Rule::exists('shops', 'id')],
            'plan_id' => ['required', Rule::exists('plans', 'id')],
            'status' => ['required', Rule::in(['draft', 'active', 'grace', 'cancelled', 'expired'])],
            'paid_through_at' => ['nullable', 'date'],
            'grace_until' => ['nullable', 'date'],
            'billing_interval' => ['nullable', Rule::in(['monthly', 'quarterly', 'yearly', 'manual'])],
        ]);
        $plan = Plan::query()->with('currentVersion')->findOrFail($data['plan_id']);
        $shop = Shop::query()->findOrFail($data['shop_id']);
        abort_unless($plan->shop_type === $shop->type, 422, 'The selected plan does not match the shop type.');

        $subscription = $shop->subscriptions()->create([
            'plan_id' => $plan->id,
            'plan_version_id' => $plan->currentVersion?->id,
            'status' => $data['status'],
            'source' => 'manual',
            'billing_interval' => $data['billing_interval'] ?? $plan->currentVersion?->billing_interval ?? 'manual',
            'started_at' => now(),
            'paid_through_at' => $data['paid_through_at'] ?? null,
            'grace_until' => $data['grace_until'] ?? null,
        ]);
        $this->access->audit($request->user(), $shop, 'billing.subscription-assigned', $subscription);

        return back()->with('status', 'Subscription assigned.');
    }

    public function recordManualPayment(RecordManualPaymentRequest $request, Subscription $subscription): RedirectResponse
    {
        $wasSponsorshipSubscription = $subscription->source === 'sponsorship';
        $payment = $this->billing->recordPayment(
            $subscription,
            $request->user(),
            $request->integer('amount_paise'),
            now()->parse($request->string('paid_through_at')->toString()),
            $request->string('reference')->trim()->toString() ?: null,
        );

        if ($wasSponsorshipSubscription) {
            Sponsorship::query()
                ->where('retailer_shop_id', $subscription->shop_id)
                ->whereNull('independently_paid_at')
                ->whereIn('status', ['active', 'grace'])
                ->update([
                    'status' => 'independent',
                    'independently_paid_at' => now(),
                    'reason' => 'retailer-converted-to-paid',
                ]);
        }

        return back()->with('status', "Manual payment {$payment->id} recorded.");
    }

    public function recordProviderEvent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'string', 'max:100'],
            'event_id' => ['required', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'max:255'],
            'payload' => ['nullable', 'json'],
        ]);
        $event = $this->billing->recordProviderEvent(
            $data['provider'],
            $data['event_id'],
            $data['event_type'],
            isset($data['payload']) ? json_decode($data['payload'], true, flags: JSON_THROW_ON_ERROR) : [],
        );

        return back()->with('status', "Billing event {$event->provider_event_id} recorded idempotently.");
    }
}
