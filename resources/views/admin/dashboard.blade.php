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

            <nav class="admin-nav" id="admin-navigation" aria-label="Admin navigation">
                <span class="admin-nav-label">Workspace</span>
                <a class="admin-nav-link is-active" href="{{ route('admin.dashboard') }}"><span aria-hidden="true">⌂</span>Overview</a>
                <a class="admin-nav-link" href="{{ route('admin.subscriptions.index') }}"><span aria-hidden="true">◈</span>Subscriptions</a>
                <a class="admin-nav-link" href="{{ route('admin.plans.index') }}"><span aria-hidden="true">◇</span>Plans & access</a>
                <span class="admin-nav-label admin-nav-label-spaced">Shortcuts</span>
                <a class="admin-nav-link" href="#new-shop"><span aria-hidden="true">＋</span>Create wholesaler</a>
                <a class="admin-nav-link" href="#shop-list"><span aria-hidden="true">▦</span>Manage shops</a>
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
                <div><p class="admin-overline">Platform operations</p><p class="admin-breadcrumb">Admin / Overview</p></div>
                <div class="admin-topbar-actions"><a class="admin-view-link" href="{{ route('market.index') }}" target="_blank" rel="noopener">View marketplace <span aria-hidden="true">↗</span></a><a class="admin-mobile-menu" href="#admin-navigation">Menu</a></div>
            </header>

            <main class="admin-content">
                @if (session('status'))
                    <div class="admin-alert admin-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="admin-alert admin-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="admin-page-heading" aria-labelledby="admin-page-title">
                    <div><p class="admin-overline">Good work starts with a clear view</p><h1 id="admin-page-title">Market overview</h1><p class="admin-page-intro">Manage shops, access plans, and seller readiness from one calm workspace.</p></div>
                    <a class="admin-primary-button" href="#new-shop"><span aria-hidden="true">＋</span> Create wholesaler</a>
                </section>

                <section class="admin-metrics" aria-label="Platform metrics">
                    <article class="admin-metric-card admin-metric-primary"><span class="admin-metric-icon" aria-hidden="true">◌</span><div><span class="admin-metric-label">Total shops</span><strong>{{ $shops->count() }}</strong><small>{{ $sellerCount }} wholesalers · {{ $retailerCount }} retailers</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">▦</span><div><span class="admin-metric-label">Catalogue pieces</span><strong>{{ number_format($productCount) }}</strong><small>Across all approved shops</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">◈</span><div><span class="admin-metric-label">Active plans</span><strong>{{ $planCount }}</strong><small>Plan versions available</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">↗</span><div><span class="admin-metric-label">Live subscriptions</span><strong>{{ $activeSubscriptionCount }}</strong><small>Active or in grace period</small></div></article>
                </section>

                <section class="admin-layout-grid">
                    <div class="admin-panel admin-shop-panel" id="shop-list">
                        <div class="admin-panel-heading"><div><p class="admin-overline">Seller network</p><h2>Shop directory</h2></div><span class="admin-count-pill">{{ $shops->count() }} total</span></div>
                        <div class="admin-shop-list">
                            @forelse ($shops as $shop)
                                <article class="admin-shop-card">
                                    <div class="admin-shop-card-head">
                                        <div class="admin-shop-identity"><span class="admin-shop-avatar">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span><div><div class="admin-shop-type">{{ $shop->type === 'retailer' ? 'Retailer' : 'Wholesaler' }}</div><h3>{{ $shop->name }}</h3><a href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">{{ $shop->publicUrl() }} <span aria-hidden="true">↗</span></a></div></div>
                                        <span class="admin-status admin-status-{{ $shop->suspended_at ? 'suspended' : $shop->status }}">{{ $shop->suspended_at ? 'Suspended' : Str::headline($shop->status) }}</span>
                                    </div>
                                    <div class="admin-shop-summary"><span><strong>{{ $shop->products_count }}</strong> products</span><span><strong>{{ $shop->category_limit }}</strong> category limit</span><span><strong>{{ $shop->product_limit }}</strong> product limit</span></div>
                                    <details class="admin-manage-details"><summary>Manage shop <span aria-hidden="true">⌄</span></summary>
                                        <form method="post" action="{{ route('admin.shops.update', ['shop' => $shop->id]) }}" class="admin-form admin-shop-form">
                                            @csrf @method('patch')
                                            <div class="admin-form-grid"><label>Status<select name="status"><option value="pending" @selected($shop->status === 'pending')>Pending</option><option value="active" @selected($shop->status === 'active')>Active</option><option value="inactive" @selected($shop->status === 'inactive')>Inactive</option></select></label><label>Category limit<input name="category_limit" type="number" min="1" value="{{ $shop->category_limit }}"></label><label>Product limit<input name="product_limit" type="number" min="1" value="{{ $shop->product_limit }}"></label><label>Images per product<input name="product_image_limit" type="number" min="1" value="{{ $shop->product_image_limit }}"></label><label class="admin-field-wide">Suspension reason<input name="suspension_reason" value="{{ $shop->suspension_reason }}" placeholder="Optional note for the seller"></label></div>
                                            <div class="admin-form-actions"><label class="admin-checkbox"><input name="suspend" type="checkbox" value="1" @checked($shop->suspended_at !== null)> Suspend this shop</label><button class="admin-secondary-button" type="submit">Save changes</button></div>
                                        </form>
                                    </details>
                                </article>
                            @empty
                                <div class="admin-empty-state"><span aria-hidden="true">◌</span><h3>No shops yet</h3><p>Create your first wholesaler to start building the marketplace.</p></div>
                            @endforelse
                        </div>
                    </div>

                    <aside class="admin-side-column" id="new-shop">
                        <section class="admin-panel admin-create-panel"><div class="admin-panel-heading"><div><p class="admin-overline">Onboarding</p><h2>Create a shop</h2></div><span class="admin-panel-number">01</span></div><p class="admin-panel-copy">Set up a seller account and reserve their public shop address. The shop will start pending until you approve it.</p>
                            <form method="post" action="{{ route('admin.shops.store') }}" class="admin-form" data-shop-slug-form>
                                @csrf
                                <label>Owner name<input name="seller_name" value="{{ old('seller_name') }}" autocomplete="name" required></label>
                                <label>Owner email<input name="seller_email" type="email" value="{{ old('seller_email') }}" autocomplete="email" required></label>
                                <div class="admin-form-grid"><label>Password<input name="seller_password" type="password" minlength="12" required></label><label>Confirm password<input name="seller_password_confirmation" type="password" minlength="12" required></label></div>
                                <label>Shop name<input name="name" value="{{ old('name') }}" data-shop-name autocomplete="organization" required></label>
                                <label>Public shop URL<div class="admin-slug-field"><span>{{ request()->getSchemeAndHttpHost() }}/wholesaler/</span><input name="slug" value="{{ old('slug') }}" data-shop-slug required></div></label>
                                <button class="admin-primary-button admin-full-button" type="submit">Create pending shop <span aria-hidden="true">→</span></button>
                            </form>
                        </section>
                        <section class="admin-help-card"><span class="admin-help-icon" aria-hidden="true">?</span><div><h3>Need a quick check?</h3><p>New shops remain pending until you review their details and activate them from the directory.</p></div></section>
                    </aside>
                </section>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-shop-slug-form]').forEach((form) => {
            const name = form.querySelector('[data-shop-name]');
            const slug = form.querySelector('[data-shop-slug]');
            let customSlug = slug.value.trim() !== '';
            const slugify = (value) => value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            name.addEventListener('input', () => { if (!customSlug) slug.value = slugify(name.value); });
            slug.addEventListener('input', () => { slug.value = slugify(slug.value); customSlug = slug.value !== slugify(name.value); });
        });
    </script>
@endpush
