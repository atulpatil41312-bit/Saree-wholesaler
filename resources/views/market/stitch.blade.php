@extends('layouts.storefront')

@section('body_class', 'marketplace-page')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/marketplace/stitch.css') }}">
@endpush

@section('content')
    @php
        $fallbackImages = [
            asset('storefront/saree.jpg'),
            asset('storefront/saree0.jpg'),
            asset('storefront/saree1.jpg'),
            asset('storefront/saree2.jpg'),
        ];
        $heroImages = $featuredProducts
            ->map(fn ($product): ?string => $product->images->first() ? Storage::url($product->images->first()->path) : null)
            ->filter()
            ->merge($fallbackImages)
            ->unique()
            ->take(4)
            ->values();
        $hasFilters = $query !== '' || $craft !== '' || $city !== '' || $moq !== null;
    @endphp

    <div class="wm-marketplace wm-stitch">
        <a class="wm-skip" href="#directory">Skip to supplier directory</a>

        <header class="wm-stitch-header">
            <div class="wm-stitch-shell wm-stitch-header-inner">
                <a class="wm-stitch-brand" href="{{ route('market.index') }}" aria-label="WholeMarket home">
                    <span class="wm-stitch-mark" aria-hidden="true">W</span>
                    <span><strong>WholeMarket</strong><small>Wholesale saree marketplace</small></span>
                </a>
                <nav class="wm-nav" aria-label="Primary navigation">
                    <a href="#directory">Shop directory</a>
                    <a href="#collections">Browse collections</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="#for-sellers">For sellers</a>
                    <a href="#about">About WholeMarket</a>
                    @auth
                        <a class="wm-stitch-nav-login" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('seller.dashboard') }}">{{ auth()->user()->isAdmin() ? 'Admin dashboard' : 'Seller dashboard' }}</a>
                    @else
                        <a class="wm-stitch-nav-login" href="{{ route('login') }}">Seller login</a>
                    @endauth
                    <a class="wm-stitch-nav-cta" href="{{ route('seller.signup') }}">List your business <span aria-hidden="true">↗</span></a>
                </nav>
                <button class="wm-menu wm-stitch-menu" type="button" aria-label="Open navigation" aria-expanded="false" data-wm-menu>
                    <span aria-hidden="true">☰</span>
                </button>
            </div>
        </header>

        <main>
            <section class="wm-stitch-hero" id="home" data-wm-reveal>
                <div class="wm-stitch-shell wm-stitch-hero-grid">
                    <div class="wm-stitch-hero-copy">
                        <p class="wm-stitch-kicker"><span aria-hidden="true">✦</span> Verified wholesale saree marketplace</p>
                        <h1>Discover trusted suppliers for your <em>next collection.</em></h1>
                        <p class="wm-stitch-hero-lede">Find verified weaving houses, compare live catalogues and MOQ terms, and start a direct conversation with the people behind every collection.</p>
                        <form class="wm-stitch-search" method="get" action="{{ route('market.index') }}" role="search">
                            <label class="sr-only" for="market-search">Search sarees, fabrics, suppliers or cities</label>
                            <span aria-hidden="true">⌕</span>
                            <input id="market-search" name="q" type="search" value="{{ $query }}" placeholder="Search sarees, fabrics, suppliers or cities">
                            <button type="submit">Search directory <span aria-hidden="true">↗</span></button>
                        </form>
                        <div class="wm-stitch-stats" aria-label="WholeMarket marketplace statistics">
                            <div><strong>{{ $shops->count() }}</strong><span>Verified suppliers</span></div>
                            <div><strong>{{ $clusterCount }}</strong><span>Weaving clusters</span></div>
                            <div><strong>{{ $catalogueCount }}</strong><span>Live catalogue pieces</span></div>
                        </div>
                    </div>
                    <div class="wm-stitch-hero-collage" aria-label="Saree and textile imagery">
                        <figure class="wm-stitch-hero-image wm-stitch-hero-image-primary"><img src="{{ $heroImages[0] ?? $fallbackImages[0] }}" alt="Saree collection from a WholeMarket supplier"></figure>
                        <figure class="wm-stitch-hero-image wm-stitch-hero-image-secondary"><img src="{{ $heroImages[1] ?? $fallbackImages[1] }}" alt="Handwoven saree detail" loading="lazy"></figure>
                        <div class="wm-stitch-hero-stack">
                            <figure class="wm-stitch-hero-image"><img src="{{ $heroImages[2] ?? $fallbackImages[2] }}" alt="Textile texture" loading="lazy"></figure>
                            <figure class="wm-stitch-hero-image"><img src="{{ $heroImages[3] ?? $fallbackImages[3] }}" alt="Regional saree drape" loading="lazy"></figure>
                        </div>
                    </div>
                </div>
            </section>

            <div class="wm-stitch-marquee" aria-label="Featured weaving clusters">
                <div class="wm-stitch-marquee-track">
                    @foreach (['Varanasi · Banarasi Katan', 'Kanchipuram · Mulberry Korvai', 'Chanderi · Silk-Cotton Tissue', 'Bhagalpur · Ghicha Tussar', 'Shantipur · Handloom Tant', 'Surat · Zari & Organza'] as $cluster)
                        <span>{{ $cluster }}</span><b aria-hidden="true">✦</b>
                    @endforeach
                    @foreach (['Varanasi · Banarasi Katan', 'Kanchipuram · Mulberry Korvai', 'Chanderi · Silk-Cotton Tissue', 'Bhagalpur · Ghicha Tussar', 'Shantipur · Handloom Tant', 'Surat · Zari & Organza'] as $cluster)
                        <span aria-hidden="true">{{ $cluster }}</span><b aria-hidden="true">✦</b>
                    @endforeach
                </div>
            </div>

            <section class="wm-stitch-section wm-stitch-suppliers" id="directory" data-wm-reveal>
                <div class="wm-stitch-shell">
                    <div class="wm-stitch-section-heading">
                        <div><p class="wm-stitch-kicker">Direct manufacturer network</p><h2>Verified wholesale suppliers</h2><p>Browse active catalogues from businesses approved for direct buyer enquiries.</p></div>
                        <a class="wm-stitch-outline-link" href="#directory">{{ $shops->count() }} active {{ Str::plural('supplier', $shops->count()) }} <span aria-hidden="true">→</span></a>
                    </div>

                    <form class="wm-stitch-filters" method="get" action="{{ route('market.index') }}" aria-label="Filter suppliers">
                        <label><span>Search</span><input name="q" value="{{ $query }}" placeholder="Supplier, fabric or code"></label>
                        <label><span>Craft or fabric</span><select name="craft"><option value="">All crafts</option>@foreach ($craftOptions as $option)<option value="{{ $option }}" @selected($craft === $option)>{{ $option }}</option>@endforeach</select></label>
                        <label><span>City or region</span><select name="city"><option value="">All regions</option>@foreach ($cityOptions as $option)<option value="{{ $option }}" @selected($city === $option)>{{ $option }}</option>@endforeach</select></label>
                        <label><span>Maximum MOQ</span><select name="moq"><option value="">Any MOQ</option><option value="5" @selected($moq === 5)>Up to 5 pcs</option><option value="15" @selected($moq === 15)>Up to 15 pcs</option></select></label>
                        <button type="submit">Apply filters <span aria-hidden="true">↗</span></button>
                        @if ($hasFilters)<a class="wm-stitch-reset" href="{{ route('market.index') }}">Reset</a>@endif
                    </form>

                    <div class="wm-stitch-supplier-grid">
                        @forelse ($shops->take(6) as $shop)
                            @php
                                $shopCity = trim((string) str($shop->address ?: '')->before(',')) ?: 'India';
                                $shopImage = $shop->banner_path && Storage::disk('public')->exists($shop->banner_path)
                                    ? Storage::url($shop->banner_path)
                                    : $fallbackImages[$loop->index % count($fallbackImages)];
                                $shopMoq = $shop->products->min('minimum_order_quantity');
                                $shopCrafts = $shop->categories->pluck('name')->filter()->take(2)->implode(' · ');
                            @endphp
                            <article class="wm-stitch-supplier-card" data-wm-reveal style="--wm-stitch-delay: {{ min($loop->index * 70, 350) }}ms">
                                <a class="wm-stitch-supplier-media" href="{{ $shop->publicUrl() }}"><img src="{{ $shopImage }}" alt="{{ $shop->name }} catalogue" loading="lazy"><span class="wm-stitch-verified">✓ Verified</span><span class="wm-stitch-moq">MOQ {{ $shopMoq ?: 'on enquiry' }}{{ $shopMoq ? ' pcs' : '' }}</span></a>
                                <div class="wm-stitch-supplier-body"><h3><a href="{{ $shop->publicUrl() }}">{{ $shop->name }}</a></h3><p class="wm-stitch-supplier-city">{{ $shopCity }}{{ $shop->address && str_contains($shop->address, ',') ? ', '.trim((string) str($shop->address)->after(',')) : '' }}</p><div class="wm-stitch-tags"><span>{{ $shopCrafts ?: ($shop->market_category ?: 'Wholesale collection') }}</span><span>{{ $shop->products_count }} designs</span></div><div class="wm-stitch-card-actions"><a href="{{ $shop->publicUrl() }}">Visit shop</a><a class="is-primary" href="{{ $shop->publicUrl() }}#bulk-inquiry">Send enquiry</a></div></div>
                            </article>
                        @empty
                            <div class="wm-stitch-empty" role="status"><span aria-hidden="true">⌕</span><h3>No suppliers match these criteria yet</h3><p>Try clearing a craft or MOQ filter, or browse all verified weaving clusters.</p><a href="{{ route('market.index') }}">Reset supplier feed</a></div>
                        @endforelse
                    </div>
                    @if ($shops->count() > 6)<div class="wm-stitch-view-all"><a href="#directory">View all suppliers <span aria-hidden="true">→</span></a></div>@endif
                </div>
            </section>

            <section class="wm-stitch-section wm-stitch-crafts" id="collections" data-wm-reveal>
                <div class="wm-stitch-shell">
                    <div class="wm-stitch-section-heading"><div><p class="wm-stitch-kicker">Regional weaves &amp; textiles</p><h2>Browse by fabric &amp; craft</h2></div><a class="wm-stitch-text-link" href="#directory">Explore all crafts <span aria-hidden="true">→</span></a></div>
                    <div class="wm-stitch-craft-grid">
                        @forelse ($craftOptions->take(8) as $index => $craftName)
                            <a class="wm-stitch-craft-card" href="{{ route('market.index', ['craft' => $craftName]) }}#directory"><img src="{{ $fallbackImages[$index % count($fallbackImages)] }}" alt="{{ $craftName }} fabric" loading="lazy"><span><strong>{{ $craftName }}</strong><small>Explore live catalogues <b aria-hidden="true">↗</b></small></span></a>
                        @empty
                            @foreach (['Banarasi', 'Kanchipuram', 'Chanderi', 'Organza', 'Tussar', 'Cotton & Linen'] as $index => $craftName)
                                <a class="wm-stitch-craft-card" href="#directory"><img src="{{ $fallbackImages[$index % count($fallbackImages)] }}" alt="{{ $craftName }} fabric" loading="lazy"><span><strong>{{ $craftName }}</strong><small>Explore live catalogues <b aria-hidden="true">↗</b></small></span></a>
                            @endforeach
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="wm-stitch-section wm-stitch-products" data-wm-reveal>
                <div class="wm-stitch-shell">
                    <div class="wm-stitch-section-heading"><div><p class="wm-stitch-kicker">Live seasonal catalogues</p><h2>Featured wholesale collections</h2></div><a class="wm-stitch-text-link" href="#directory">Browse live catalogues <span aria-hidden="true">→</span></a></div>
                    <div class="wm-stitch-product-grid">
                        @forelse ($featuredProducts->take(8) as $product)
                            @php $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $fallbackImages[$loop->index % count($fallbackImages)]; @endphp
                            <article class="wm-stitch-product-card"><a class="wm-stitch-product-media" href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy"><span>{{ $product->code }}</span></a><div class="wm-stitch-product-body"><small>{{ $product->category?->name ?: ($product->fabric?->name ?: 'Wholesale edit') }}</small><h3><a href="{{ $product->publicUrl() }}">{{ $product->name }}</a></h3><p>{{ $product->shop->name }}{{ $product->colour ? ' · '.$product->colour : '' }}</p><div><strong>{{ $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 0).' / pc' : 'Price on enquiry' }}</strong><span>MOQ {{ $product->minimum_order_quantity }} pcs</span></div></div></article>
                        @empty
                            <div class="wm-stitch-empty" role="status"><span aria-hidden="true">◌</span><h3>Featured catalogues are being prepared</h3><p>Approved suppliers will appear here as soon as they publish available products.</p><a href="#for-sellers">List your business</a></div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="wm-stitch-process" id="how-it-works" data-wm-reveal><div class="wm-stitch-shell"><div class="wm-stitch-section-heading"><div><p class="wm-stitch-kicker">A clearer way to source</p><h2>How WholeMarket works</h2></div></div><div class="wm-stitch-process-grid"><article><span>01</span><h3>Discover verified suppliers</h3><p>Filter by fabric, city, craft and MOQ to find businesses that match your buying brief.</p></article><article><span>02</span><h3>Browse live catalogues</h3><p>Compare product details, colours, minimums and wholesale rates before you enquire.</p></article><article><span>03</span><h3>Enquire directly</h3><p>Connect with the seller through the configured enquiry form or WhatsApp channel.</p></article></div></div></section>

            <section class="wm-stitch-about" id="about" data-wm-reveal><div class="wm-stitch-shell wm-stitch-about-grid"><div class="wm-stitch-about-art"><span>W</span><small>WholeMarket<br>artisan network</small></div><div><p class="wm-stitch-kicker">The marketplace advantage</p><h2>Bridging thoughtful retailers with authentic master weavers.</h2><p>WholeMarket gives every supplier a clear digital shopfront while giving buyers the practical information they need to source confidently.</p><div class="wm-stitch-about-points"><span><b>Transparent listings</b>Live shop details, catalogues and MOQs</span><span><b>Curated discovery</b>Browse regional crafts and collections</span><span><b>Direct conversations</b>Use each seller’s configured contact details</span><span><b>Trusted network</b>Public shops remain subject to platform visibility rules</span></div></div></div></section>

            <section class="wm-stitch-seller" id="for-sellers" data-wm-reveal><div class="wm-stitch-shell wm-stitch-seller-inner"><div><p class="wm-stitch-kicker">For independent sellers</p><h2>Bring your catalogue to the right buyers.</h2><p>Create a professional storefront, upload your sarees and let retailers enquire directly.</p></div><div class="wm-stitch-seller-actions"><a href="{{ route('seller.signup') }}">Apply for a seller account <span aria-hidden="true">↗</span></a><a href="{{ route('login') }}">Seller login <span aria-hidden="true">↗</span></a></div></div></section>
        </main>

        <footer class="wm-stitch-footer"><div class="wm-stitch-shell wm-stitch-footer-grid"><div><a class="wm-stitch-brand" href="{{ route('market.index') }}"><span class="wm-stitch-mark" aria-hidden="true">W</span><span><strong>WholeMarket</strong><small>Wholesale saree marketplace</small></span></a><p>Independent saree businesses, beautifully presented and easy to contact.</p></div><div><h3>Directory</h3><a href="#directory">Verified suppliers</a><a href="#collections">Fabric &amp; craft</a><a href="#how-it-works">How it works</a></div><div><h3>Seller hub</h3><a href="{{ route('seller.signup') }}">Seller onboarding</a><a href="{{ route('login') }}">Seller login</a><a href="#for-sellers">Why WholeMarket</a></div><div><h3>WholeMarket</h3><a href="#about">About the platform</a><a href="{{ route('market.index') }}">Marketplace home</a></div></div><div class="wm-stitch-shell wm-stitch-footer-bottom"><span>© {{ now()->year }} WholeMarket. All rights reserved.</span><span>Direct seller enquiries · Built for confident sourcing</span></div></footer>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('templates/marketplace/interactions.js') }}" defer></script>
@endpush
