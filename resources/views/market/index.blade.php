@extends('layouts.storefront')

@section('body_class', 'marketplace-page')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/marketplace/style.css') }}">
@endpush

@section('content')
    @php
        $fallbackImages = [asset('storefront/saree0.jpg'), asset('storefront/saree1.jpg'), asset('storefront/saree2.jpg')];
    @endphp

    <div class="wm-marketplace">
        <a class="wm-skip" href="#directory">Skip to shop directory</a>
        <div class="wm-announcement" role="region" aria-label="Market announcement"><div class="wm-shell wm-announcement-inner"><span><i></i>Verified wholesale saree marketplace</span><span>Handloom catalogues · Direct enquiries · Pan-India sourcing</span></div></div>

        <header class="wm-header"><div class="wm-shell wm-header-inner"><a class="wm-brand" href="{{ route('market.index') }}" aria-label="WholeMarket home"><span class="wm-mark">W</span><span><strong>WholeMarket</strong><small>Wholesale saree directory</small></span></a><nav class="wm-nav" aria-label="Main navigation"><a href="#directory">Shop directory</a><a href="#about">Heritage &amp; provenance</a>@auth<a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('seller.dashboard') }}">{{ auth()->user()->isAdmin() ? 'Admin dashboard' : 'Seller dashboard' }}</a>@else<a href="{{ route('login') }}">Seller login</a>@endauth</nav><button class="wm-menu" type="button" aria-label="Toggle navigation" aria-expanded="false" data-wm-menu>☰</button></div></header>

        <main>
            <section class="wm-hero" id="home"><div class="wm-shell wm-hero-grid"><div class="wm-hero-copy"><p class="wm-eyebrow">Direct handloom weaver mills</p><h1>Discover your<br><em>next supplier.</em></h1><p class="wm-hero-desc">Source authentic Banarasi, Kanchipuram, Chanderi and Tussar silks directly from verified weaver houses, mill clusters and regional looms.</p><form class="wm-search" method="get" action="{{ route('market.index') }}" role="search"><span aria-hidden="true">⌕</span><input name="q" value="{{ $query }}" placeholder="Search by shop or weaving house" aria-label="Search approved shops"><button type="submit">Explore shops <span>↗</span></button></form><div class="wm-quick-links"><span>Popular weaves:</span><a href="#directory">Kanchipuram</a><a href="#directory">Banarasi</a><a href="#directory">Organza</a><a href="#directory">Tussar</a></div></div><div class="wm-hero-visual"><img src="{{ asset('storefront/saree.jpg') }}" alt="Saree model representing the WholeMarket directory"><div class="wm-hero-caption"><small>WholeMarket edit</small><strong>Curated collections.<br>Direct enquiries.</strong></div><span class="wm-hero-stamp">WHOLE<br><b>MARKET</b></span></div></div></section>

            <section class="wm-benefits" aria-label="Marketplace benefits"><div class="wm-shell wm-benefits-grid"><div><b>100% vetted shops</b><span>Active catalogues and seller details</span></div><div><b>Direct seller rates</b><span>Connect without broker overhead</span></div><div><b>Clear availability</b><span>Live product and contact information</span></div><div><b>Low-batch sourcing</b><span>Find collections for your store</span></div></div></section>

            <section class="wm-directory wm-shell" id="directory"><div class="wm-section-head"><div><p class="wm-eyebrow">WholeMarket artisan directory</p><h2>Approved mills &amp; master weavers</h2><p>Hand-selected production houses with live catalogues and direct enquiry paths.</p></div><span class="wm-count">{{ $shops->count() }} active {{ Str::plural('shop', $shops->count()) }}</span></div><div class="wm-directory-tools"><div class="wm-pills"><a class="is-active" href="#directory">All approved shops</a><a href="#directory">Kanchipuram</a><a href="#directory">Banarasi</a><a href="#directory">Chanderi</a><a href="#directory">Paithani</a></div><span class="wm-sort-note">Showing live storefronts</span></div><div class="wm-shops-grid">@forelse($shops as $shop)<article class="wm-shop-card"><a class="wm-card-media" href="{{ $shop->publicUrl() }}"><img src="{{ $shop->banner_path ? Storage::url($shop->banner_path) : $fallbackImages[$loop->index % count($fallbackImages)] }}" alt="{{ $shop->name }} banner" loading="lazy"><span class="wm-card-badge">{{ $shop->market_category ?: 'Wholesale shop' }}</span></a><div class="wm-shop-body"><small>{{ $shop->market_category ?: 'Saree atelier' }}</small><h3><a href="{{ $shop->publicUrl() }}">{{ $shop->name }}</a></h3><p>{{ $shop->description ?: 'A live saree catalogue with direct seller enquiries and current availability.' }}</p><div class="wm-card-meta"><span>{{ $shop->products_count }} catalogue {{ Str::plural('piece', $shop->products_count) }}</span><a href="{{ $shop->publicUrl() }}">Visit shop <span aria-hidden="true">↗</span></a></div></div></article>@empty<div class="wm-empty" role="status"><span aria-hidden="true">⌕</span><h3>No approved shops found</h3><p>We could not find an active shop matching “{{ $query }}”. Try a broader search or return to the full directory.</p><a href="{{ route('market.index') }}">Reset search</a></div>@endforelse</div></section>

            <section class="wm-about" id="about"><div class="wm-shell wm-about-grid"><div class="wm-about-art"><span>W</span><small>WholeMarket<br>artisan network</small></div><div><p class="wm-eyebrow">The marketplace advantage</p><h2>Bridging luxury retailers with authentic master weavers.</h2><p>WholeMarket helps buyers discover independent saree businesses, understand their live catalogues and start a direct conversation with the people behind each collection.</p><div class="wm-about-points"><span><b>Transparent listings</b>Live shop details and product counts</span><span><b>Curated discovery</b>Browse by supplier and collection</span><span><b>Direct conversations</b>Use the seller’s configured contact details</span><span><b>Trusted network</b>Public shops remain subject to platform visibility rules</span></div></div></div></section>

            <section class="wm-seller" id="seller"><div class="wm-shell wm-seller-inner"><div><p class="wm-eyebrow">For independent sellers</p><h2>Bring your catalogue to the market.</h2><p>Create a professional storefront, upload your sarees and let buyers enquire directly.</p></div><div class="wm-seller-actions"><a href="{{ route('seller.signup') }}">Apply for a seller account <span>↗</span></a><a href="{{ route('login') }}">Seller login <span>↗</span></a></div></div></section>
        </main>

        <footer class="wm-footer"><div class="wm-shell wm-footer-grid"><div><a class="wm-footer-brand" href="{{ route('market.index') }}"><span class="wm-mark">W</span><span><strong>WholeMarket</strong><small>Wholesale saree directory</small></span></a><p>Independent saree businesses, beautifully presented and easy to contact.</p></div><div><h3>Directory</h3><a href="#directory">Approved shops</a><a href="#about">Our standards</a><a href="{{ route('market.index') }}">Reset search</a></div><div><h3>Seller hub</h3><a href="{{ route('seller.signup') }}">Seller onboarding</a><a href="{{ route('login') }}">Seller login</a><a href="{{ route('login') }}">Support access</a></div><div><h3>WholeMarket</h3><a href="#about">About the platform</a><a href="{{ route('market.index') }}">Marketplace home</a></div></div><div class="wm-shell wm-footer-bottom"><span>© {{ now()->year }} WholeMarket. All rights reserved.</span><span>Direct seller enquiries · Built for saree sourcing</span></div></footer>
    </div>
@endsection

@push('scripts')
    <script>
        (() => { const button = document.querySelector('[data-wm-menu]'); const nav = document.querySelector('.wm-nav'); if (!button || !nav) return; button.addEventListener('click', () => { const open = nav.classList.toggle('is-open'); button.setAttribute('aria-expanded', String(open)); }); })();
    </script>
@endpush
