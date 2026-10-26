@extends('layouts.storefront')

@section('body_class', 'admin-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/admin.css') }}?v={{ filemtime(public_path('storefront/admin.css')) }}">
@endpush

@section('content')
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}" aria-label="WholeMarket admin home"><span class="admin-brand-mark">W</span><span><strong>WholeMarket</strong><small>Platform control</small></span></a>
            <nav class="admin-nav" aria-label="Admin navigation">
                <span class="admin-nav-label">Workspace</span>
                <a class="admin-nav-link" href="{{ route('admin.dashboard') }}"><span aria-hidden="true">⌂</span>Overview</a>
                <a class="admin-nav-link" href="{{ route('admin.subscriptions.index') }}"><span aria-hidden="true">◈</span>Subscriptions</a>
                <a class="admin-nav-link is-active" href="{{ route('admin.plans.index') }}"><span aria-hidden="true">◇</span>Plans & access</a>
                <span class="admin-nav-label admin-nav-label-spaced">Shortcuts</span>
                <a class="admin-nav-link" href="#new-plan"><span aria-hidden="true">＋</span>Add a plan</a>
                <a class="admin-nav-link" href="#plan-list"><span aria-hidden="true">▦</span>Plan catalogue</a>
            </nav>
            <div class="admin-sidebar-footer">
                <div class="admin-user-chip"><span class="admin-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></span></div>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="admin-signout" type="submit">Sign out <span aria-hidden="true">↗</span></button></form>
            </div>
        </aside>

        <div class="admin-workspace">
            <header class="admin-topbar"><div><p class="admin-overline">Platform operations</p><p class="admin-breadcrumb">Admin / Plans & access</p></div><div class="admin-topbar-actions"><a class="admin-view-link" href="{{ route('market.index') }}" target="_blank" rel="noopener">View marketplace <span aria-hidden="true">↗</span></a></div></header>
            <main class="admin-content">
                @if (session('status'))<div class="admin-alert admin-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="admin-alert admin-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                <section class="admin-page-heading" aria-labelledby="plans-title">
                    <div><p class="admin-overline">Plans & entitlements</p><h1 id="plans-title">Plan catalogue</h1><p class="admin-page-intro">Define the access, limits, and template choices available to every shop.</p></div>
                    <a class="admin-primary-button" href="#new-plan"><span aria-hidden="true">＋</span> Add a plan</a>
                </section>

                <section class="admin-metrics" aria-label="Plan metrics">
                    <article class="admin-metric-card admin-metric-primary"><span class="admin-metric-icon" aria-hidden="true">◇</span><div><span class="admin-metric-label">Total plans</span><strong>{{ $plans->count() }}</strong><small>Configured for the marketplace</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">✓</span><div><span class="admin-metric-label">Active plans</span><strong>{{ $plans->where('is_active', true)->count() }}</strong><small>Available for assignment</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">▤</span><div><span class="admin-metric-label">Plan versions</span><strong>{{ $plans->sum(fn ($plan) => $plan->versions->count()) }}</strong><small>Billing and limit variants</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">↗</span><div><span class="admin-metric-label">Signup enabled</span><strong>{{ $plans->where('signup_enabled', true)->count() }}</strong><small>Shown during seller onboarding</small></div></article>
                </section>

                <section class="admin-layout-grid">
                    <div class="admin-panel admin-plan-list" id="plan-list">
                        <div class="admin-panel-heading"><div><p class="admin-overline">Access catalogue</p><h2>Plans and versions</h2></div><span class="admin-count-pill">{{ $plans->count() }} total</span></div>
                        <div class="admin-plan-items">
                            @forelse ($plans as $plan)
                                <article class="admin-plan-card">
                                    <div class="admin-plan-card-head"><div><div class="admin-shop-type">{{ Str::headline($plan->shop_type) }}</div><h3>{{ $plan->name }}</h3><p class="admin-plan-key">{{ $plan->key }}</p></div><span class="admin-status admin-status-{{ $plan->is_active ? 'active' : 'inactive' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span></div>
                                    <form method="post" action="{{ route('admin.plans.update', $plan) }}" class="admin-form admin-plan-form">
                                        @csrf @method('patch')
                                        <div class="admin-form-grid"><label>Name<input name="name" value="{{ $plan->name }}" required></label><label>Sort order<input name="sort_order" type="number" min="0" value="{{ $plan->sort_order }}"></label></div>
                                        <div class="admin-form-actions"><label class="admin-checkbox"><input name="signup_enabled" type="checkbox" value="1" @checked($plan->signup_enabled)> Available at signup</label><label class="admin-checkbox"><input name="is_active" type="checkbox" value="1" @checked($plan->is_active)> Active</label><button class="admin-secondary-button" type="submit">Save plan</button></div>
                                    </form>
                                    <div class="admin-version-heading"><h4>Versions</h4><span>{{ $plan->versions->count() }} configured</span></div>
                                    <div class="admin-version-list">
                                        @forelse($plan->versions as $version)
                                            <div class="admin-version-card"><div><strong>Version {{ $version->version }}</strong><small>{{ Str::headline($version->billing_interval) }} · {{ $version->price_paise === null ? 'Manual price' : '₹'.number_format($version->price_paise / 100, 2) }}</small></div><p><span>Limits</span> {{ json_encode($version->limits) }}</p><p><span>Templates</span> {{ is_array($version->template_keys) ? implode(', ', $version->template_keys) : $version->template_keys }}</p></div>
                                        @empty
                                            <div class="admin-version-empty">No versions yet. Add the first billing option below.</div>
                                        @endforelse
                                    </div>
                                    <details class="admin-add-version"><summary>Add a version <span aria-hidden="true">＋</span></summary>
                                        <form method="post" action="{{ route('admin.plans.versions.store', $plan) }}" class="admin-form admin-plan-version-form">
                                            @csrf
                                            <div class="admin-form-grid"><label>Price in paise <small>Leave blank for manual</small><input name="price_paise" type="number" min="0"></label><label>Billing interval<select name="billing_interval"><option>monthly</option><option>quarterly</option><option>yearly</option><option>manual</option></select></label></div>
                                            <label>Limits JSON<textarea name="limits" rows="4" required>{"product_limit":30,"category_limit":5,"product_image_limit":2,"staff_limit":0,"sponsored_retailer_limit":0}</textarea></label>
                                            <label>Template keys<input name="template_keys" value="classic" required></label>
                                            <button class="admin-secondary-button" type="submit">Add version <span aria-hidden="true">→</span></button>
                                        </form>
                                    </details>
                                </article>
                            @empty
                                <div class="admin-empty-state"><span aria-hidden="true">◇</span><h3>No plans yet</h3><p>Create a plan to start assigning access to shops.</p></div>
                            @endforelse
                        </div>
                    </div>

                    <aside class="admin-side-column" id="new-plan">
                        <section class="admin-panel admin-create-panel"><div class="admin-panel-heading"><div><p class="admin-overline">Plan setup</p><h2>Create a plan</h2></div><span class="admin-panel-number">01</span></div><p class="admin-panel-copy">Start with the audience and signup visibility. Add pricing, limits, and templates as versions once the plan is created.</p>
                            <form method="post" action="{{ route('admin.plans.store') }}" class="admin-form">
                                @csrf
                                <label>Plan key<input name="key" placeholder="seasonal-pro" required></label>
                                <label>Display name<input name="name" placeholder="Seasonal Pro" required></label>
                                <label>Shop type<select name="shop_type"><option value="wholesaler">Wholesaler</option><option value="retailer">Retailer</option></select></label>
                                <label>Sort order<input name="sort_order" type="number" min="0" value="0"></label>
                                <label class="admin-checkbox"><input name="signup_enabled" type="checkbox" value="1"> Available at signup</label>
                                <button class="admin-primary-button admin-full-button" type="submit">Create plan <span aria-hidden="true">→</span></button>
                            </form>
                        </section>
                        <section class="admin-help-card"><span class="admin-help-icon" aria-hidden="true">?</span><div><h3>Keep access predictable</h3><p>Use versions for billing changes so existing subscriptions keep their saved limits and template access.</p></div></section>
                    </aside>
                </section>
            </main>
        </div>
    </div>
@endsection
