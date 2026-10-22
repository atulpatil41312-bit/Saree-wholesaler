@extends('layouts.storefront')

@section('body_class', 'admin-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/admin.css') }}?v={{ filemtime(public_path('storefront/admin.css')) }}">
@endpush

@section('content')
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}" aria-label="WholeMarket admin home">
                <span class="admin-brand-mark">W</span>
                <span><strong>WholeMarket</strong><small>Platform control</small></span>
            </a>

            <nav class="admin-nav" aria-label="Admin navigation">
                <span class="admin-nav-label">Workspace</span>
                <a class="admin-nav-link" href="{{ route('admin.dashboard') }}"><span aria-hidden="true">⌂</span>Overview</a>
                <a class="admin-nav-link is-active" href="{{ route('admin.subscriptions.index') }}"><span aria-hidden="true">◈</span>Subscriptions</a>
                <a class="admin-nav-link" href="{{ route('admin.plans.index') }}"><span aria-hidden="true">◇</span>Plans & access</a>
                <span class="admin-nav-label admin-nav-label-spaced">Shortcuts</span>
                <a class="admin-nav-link" href="#assign-subscription"><span aria-hidden="true">＋</span>Assign subscription</a>
                <a class="admin-nav-link" href="#payment-activity"><span aria-hidden="true">↗</span>Payment activity</a>
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-user-chip">
                    <span class="admin-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span>
                    <span><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></span>
                </div>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="admin-signout" type="submit">Sign out <span aria-hidden="true">↗</span></button>
                </form>
            </div>
        </aside>

        <div class="admin-workspace">
            <header class="admin-topbar">
                <div><p class="admin-overline">Platform operations</p><p class="admin-breadcrumb">Admin / Subscriptions</p></div>
                <div class="admin-topbar-actions"><a class="admin-view-link" href="{{ route('market.index') }}" target="_blank" rel="noopener">View marketplace <span aria-hidden="true">↗</span></a></div>
            </header>

            <main class="admin-content">
                @if (session('status'))
                    <div class="admin-alert admin-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="admin-alert admin-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="admin-page-heading" aria-labelledby="subscriptions-title">
                    <div><p class="admin-overline">Revenue & access</p><h1 id="subscriptions-title">Subscriptions</h1><p class="admin-page-intro">Assign plans, keep seller access current, and record payments in one place.</p></div>
                    <a class="admin-primary-button" href="#assign-subscription"><span aria-hidden="true">＋</span> Assign subscription</a>
                </section>

                <section class="admin-metrics" aria-label="Subscription metrics">
                    <article class="admin-metric-card admin-metric-primary"><span class="admin-metric-icon" aria-hidden="true">◈</span><div><span class="admin-metric-label">Active subscriptions</span><strong>{{ $subscriptionStats['active'] }}</strong><small>Currently paid and live</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">◌</span><div><span class="admin-metric-label">Grace period</span><strong>{{ $subscriptionStats['grace'] }}</strong><small>Needs renewal follow-up</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">!</span><div><span class="admin-metric-label">Needs attention</span><strong>{{ $subscriptionStats['attention'] }}</strong><small>Draft, expired, or cancelled</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">₹</span><div><span class="admin-metric-label">Payments recorded</span><strong>₹{{ number_format($paymentStats['total_paise'] / 100, 0) }}</strong><small>{{ $paymentStats['count'] }} payment records</small></div></article>
                </section>

                <section class="admin-layout-grid">
                    <div class="admin-panel admin-subscription-panel">
                        <div class="admin-panel-heading"><div><p class="admin-overline">Access ledger</p><h2>Subscription list</h2></div><span class="admin-count-pill">{{ $subscriptionStats['total'] }} total</span></div>
                        <div class="admin-subscription-list">
                            @forelse ($subscriptions as $subscription)
                                <article class="admin-subscription-card">
                                    <div class="admin-subscription-card-head">
                                        <div class="admin-subscription-identity"><span class="admin-shop-avatar">{{ Str::upper(Str::substr($subscription->shop->name, 0, 1)) }}</span><div><div class="admin-shop-type">{{ Str::headline($subscription->shop->type) }} · {{ Str::headline($subscription->source) }}</div><h3>{{ $subscription->shop->name }}</h3><p>{{ $subscription->plan->name }} <span>·</span> {{ Str::headline($subscription->billing_interval) }}</p></div></div>
                                        <span class="admin-status admin-status-{{ $subscription->status }}">{{ Str::headline($subscription->status) }}</span>
                                    </div>
                                    <div class="admin-subscription-summary"><span><small>Access through</small><strong>{{ $subscription->paid_through_at?->format('d M Y') ?? 'Not set' }}</strong></span><span><small>Grace until</small><strong>{{ $subscription->grace_until?->format('d M Y') ?? '—' }}</strong></span><span><small>Payments</small><strong>{{ $subscription->payments->count() }} recorded</strong></span></div>
                                    <details class="admin-manage-details"><summary>Record a payment <span aria-hidden="true">⌄</span></summary>
                                        <form method="post" action="{{ route('admin.subscriptions.payments.store', $subscription) }}" class="admin-form admin-payment-form">
                                            @csrf
                                            <div class="admin-form-grid"><label>Amount (paise)<input name="amount_paise" type="number" min="0" required placeholder="e.g. 29900"></label><label>Paid through<input name="paid_through_at" type="datetime-local" required></label><label>Reference<input name="reference" placeholder="Optional receipt ID"></label></div>
                                            <div class="admin-form-actions"><span class="admin-form-hint">Manual payments extend the seller’s access period.</span><button class="admin-secondary-button" type="submit">Save payment</button></div>
                                        </form>
                                    </details>
                                </article>
                            @empty
                                <div class="admin-empty-state"><span aria-hidden="true">◈</span><h3>No subscriptions yet</h3><p>Assign a plan to a shop to start tracking access and payments.</p></div>
                            @endforelse
                        </div>
                        @if ($subscriptions->hasPages())<div class="admin-pagination">{{ $subscriptions->links() }}</div>@endif
                    </div>

                    <aside class="admin-side-column">
                        <section class="admin-panel admin-create-panel" id="assign-subscription">
                            <div class="admin-panel-heading"><div><p class="admin-overline">Onboarding</p><h2>Assign a plan</h2></div><span class="admin-panel-number">01</span></div>
                            <p class="admin-panel-copy">Choose a shop and a plan to grant catalogue access. You can change the dates or status later by recording a payment.</p>
                            <form method="post" action="{{ route('admin.subscriptions.store') }}" class="admin-form">
                                @csrf
                                <label>Shop<select name="shop_id" required><option value="">Choose a shop</option>@foreach ($shops as $shop)<option value="{{ $shop->id }}" @selected(old('shop_id') == $shop->id)>{{ $shop->name }} · {{ Str::headline($shop->type) }}</option>@endforeach</select></label>
                                <label>Plan<select name="plan_id" required><option value="">Choose a plan</option>@foreach ($plans as $plan)<option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }} · {{ Str::headline($plan->shop_type) }}</option>@endforeach</select></label>
                                <div class="admin-form-grid"><label>Status<select name="status"><option value="active">Active</option><option value="draft">Draft</option><option value="grace">Grace</option><option value="expired">Expired</option></select></label><label>Billing interval<select name="billing_interval"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="yearly">Yearly</option><option value="manual">Manual</option></select></label></div>
                                <div class="admin-form-grid"><label>Paid through<input name="paid_through_at" type="datetime-local"></label><label>Grace until<input name="grace_until" type="datetime-local"></label></div>
                                <button class="admin-primary-button admin-full-button" type="submit">Assign subscription <span aria-hidden="true">→</span></button>
                            </form>
                        </section>

                        <section class="admin-panel admin-events-panel">
                            <div class="admin-panel-heading"><div><p class="admin-overline">Integrations</p><h2>Billing event</h2></div><span class="admin-panel-number">02</span></div>
                            <p class="admin-panel-copy">Record a provider event for testing or reconciliation. Duplicate provider IDs are safely ignored.</p>
                            <form method="post" action="{{ route('admin.billing.events.store') }}" class="admin-form admin-event-form">
                                @csrf
                                <div class="admin-form-grid"><label>Provider<input name="provider" placeholder="razorpay" required></label><label>Event ID<input name="event_id" placeholder="evt_123" required></label></div>
                                <label>Event type<input name="event_type" placeholder="payment.received" required></label>
                                <label>Payload JSON<textarea name="payload" rows="3" placeholder="{}"></textarea></label>
                                <button class="admin-secondary-button" type="submit">Record event</button>
                            </form>
                        </section>
                    </aside>
                </section>

                <section class="admin-bottom-grid" id="payment-activity">
                    <div class="admin-panel admin-activity-panel"><div class="admin-panel-heading"><div><p class="admin-overline">Cash flow</p><h2>Recent payments</h2></div></div><div class="admin-activity-list">@forelse ($recentPayments as $payment)<div class="admin-activity-row"><span class="admin-activity-icon">₹</span><div><strong>{{ $payment->shop->name }}</strong><small>{{ $payment->subscription->plan->name }} · {{ $payment->paid_at?->format('d M Y, H:i') ?? 'Date not set' }}</small></div><b>₹{{ number_format($payment->amount_paise / 100, 2) }}</b></div>@empty<div class="admin-activity-empty">No payment records yet.</div>@endforelse</div></div>
                    <div class="admin-panel admin-activity-panel"><div class="admin-panel-heading"><div><p class="admin-overline">Audit trail</p><h2>Provider events</h2></div></div><div class="admin-activity-list">@forelse ($recentBillingEvents as $event)<div class="admin-activity-row"><span class="admin-activity-icon">↗</span><div><strong>{{ $event->provider }}</strong><small>{{ $event->event_type }} · {{ $event->created_at?->format('d M Y, H:i') }}</small></div><span class="admin-event-status">{{ Str::headline($event->status) }}</span></div>@empty<div class="admin-activity-empty">No provider events recorded.</div>@endforelse</div></div>
                </section>
            </main>
        </div>
    </div>
@endsection
