@extends('layouts.storefront')

@section('body_class', 'marketplace-page')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/marketplace/style.css') }}">
@endpush

@section('content')
    @php
        $fallbackImages = [asset('storefront/saree0.jpg'), asset('storefront/saree1.jpg'), asset('storefront/saree2.jpg')];
        $shopCategories = $shops->pluck('market_category')->filter()->unique()->values()->take(5);
    @endphp

    <div class="wm-marketplace wm-redesign">
        <a class="wm-skip" href="#directory">Skip to supplier directory</a>

        <div class="wm2-topline"><div class="wm-shell wm2-topline-inner"><span><i></i> Safe B2B sourcing for Indian textiles</span><span>Verified catalogues · Direct seller access</span></div></div>

        <header class="wm2-header">
            <div class="wm-shell wm2-header-inner">
                <a class="wm2-brand" href="{{ route('market.index') }}" aria-label="WholeMarket home"><span class="wm2-brand-mark">W</span><span><strong>WholeMarket</strong><small>Direct mill infrastructure</small></span></a>
                <nav class="wm2-nav" aria-label="Main navigation"><a class="is-active" href="#directory">Directory</a><a href="#assurance">Weaving clusters</a><a href="#assurance">Purity index</a><a href="#about">Grow your shop</a>@auth<a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('seller.dashboard') }}">{{ auth()->user()->isAdmin() ? 'Admin dashboard' : 'Seller login' }}</a>@else<a href="{{ route('login') }}">Wholesaler login</a>@endauth</nav>
                <a class="wm2-header-cta" href="{{ route('seller.signup') }}">Join as manufacturer <span>↗</span></a>
                <button class="wm2-menu" type="button" aria-label="Toggle navigation" aria-expanded="false" data-wm-menu>☰</button>
            </div>
        </header>

        <main>
            <section class="wm2-hero">
                <div class="wm-shell wm2-hero-grid">
                    <div class="wm2-hero-copy">
                        <p class="wm2-eyebrow">2026 institutional saree procurement</p>
                        <h1>The modern B2B gateway for <em>authentic saree wholesale.</em></h1>
                        <p class="wm2-hero-desc">Direct loom sourcing, lab-verified authenticity, and clear catalogue details for buyers building their next collection.</p>
                        <div class="wm2-trust-row"><span>✓ Live shop details</span><span>✓ Product availability</span><span>✓ Direct enquiries</span></div>
                    </div>
                    <div class="wm2-hero-card">
                        <img src="{{ asset('storefront/saree.jpg') }}" alt="Saree model representing the WholeMarket supplier network">
                        <div class="wm2-hero-card-overlay"><small>WholeMarket editorial</small><strong>Curated collections.<br><em>Direct enquiries.</em></strong><span>Live catalogue network</span></div>
                    </div>
                </div>
            </section>

            <section class="wm2-search-wrap" id="searchConsole">
                <div class="wm-shell wm2-search-panel">
                    <div class="wm2-browse-row"><span>Browse the network</span><a class="is-active" href="{{ route('market.index') }}#directory">All shops</a>@foreach($shopCategories as $category)<a href="{{ route('market.index', ['q' => $category]) }}#directory">{{ $category }}</a>@endforeach</div>
                    <form class="wm2-search-form" method="get" action="{{ route('market.index') }}" role="search">
                        <label class="wm2-search-input"><span aria-hidden="true">⌕</span><input name="q" value="{{ $query }}" placeholder="Search by shop name, fabric, or city..." aria-label="Search approved shops"><button type="button" class="wm2-clear" data-wm-clear {{ $query === '' ? 'hidden' : '' }}>Clear</button></label>
                        <label class="wm2-select"><span>Category</span><select aria-label="Category"><option>All shops</option>@foreach($shopCategories as $category)<option>{{ $category }}</option>@endforeach</select></label>
                        <label class="wm2-select wm2-select-small"><span>Catalogue</span><select aria-label="Catalogue size"><option>Any size</option><option>10+ pieces</option><option>25+ pieces</option></select></label>
                        <button class="wm2-filter-button" type="submit">Filter <span>↗</span></button>
                    </form>
                    <div class="wm2-quick-tags"><span>Quick tags:</span><a href="{{ route('market.index', ['q' => 'silk']) }}#directory">Silk</a><a href="{{ route('market.index', ['q' => 'cotton']) }}#directory">Cotton</a><a href="{{ route('market.index', ['q' => 'organza']) }}#directory">Organza</a><a href="{{ route('market.index', ['q' => 'ready']) }}#directory">Ready stock</a></div>
                </div>
            </section>

            <section class="wm2-metrics" aria-label="Marketplace summary"><div class="wm-shell wm2-metrics-grid"><div><span>▦</span><strong>{{ $shops->count() }}</strong><small>Active shops<br><em>Live catalogues</em></small></div><div><span>%</span><strong>0</strong><small>Brokerage fee<br><em>Direct to seller</em></small></div><div><span>₹</span><strong>{{ number_format($shops->sum('products_count')) }}</strong><small>Catalogue pieces<br><em>Available to explore</em></small></div><div><span>↗</span><strong>Live</strong><small>Seller response<br><em>Enquire directly</em></small></div></div></section>

            <section class="wm2-directory wm-shell" id="directory">
                <div class="wm2-directory-head"><div><p class="wm2-eyebrow">Verified commercial directory</p><h2>Approved wholesale shops</h2><p>{{ $shops->count() }} {{ Str::plural('shop', $shops->count()) }} with live catalogues, public details and direct enquiry paths.</p></div><span class="wm2-directory-count">{{ $shops->count() }} live</span></div>
                <div class="wm2-directory-toolbar"><div class="wm2-directory-tabs"><a class="is-active" href="{{ route('market.index') }}#directory">All shops <b>{{ $shops->count() }}</b></a>@foreach($shopCategories->take(3) as $category)<a href="{{ route('market.index', ['q' => $category]) }}#directory">{{ $category }}</a>@endforeach</div><span>Sorted by active catalogue</span></div>
                <div class="wm2-shop-grid">
                    @forelse($shops as $shop)
                        @php
                            $shopImage = $shop->banner_path ? Storage::url($shop->banner_path) : $fallbackImages[$loop->index % count($fallbackImages)];
                            $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
                            $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1 ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shop->publicUrl()) : null;
                        @endphp
                        <article class="wm2-shop-card">
                            <a class="wm2-shop-image" href="{{ $shop->publicUrl() }}"><img src="{{ $shopImage }}" alt="{{ $shop->name }} banner" loading="lazy"><span class="wm2-verified">✓ Verified shop</span><span class="wm2-location">{{ $shop->market_category ?: 'India' }}</span><div class="wm2-image-caption"><small>{{ $shop->market_category ?: 'Saree atelier' }}</small><strong>{{ $shop->products_count }} catalogue {{ Str::plural('piece', $shop->products_count) }}</strong></div></a>
                            <div class="wm2-shop-body"><div class="wm2-shop-kicker">Active manufacturer</div><h3><a href="{{ $shop->publicUrl() }}">{{ $shop->name }}</a></h3><p>{{ $shop->description ?: 'A live saree catalogue with wholesale-ready details, current availability and direct seller conversations.' }}</p><dl><div><dt>Catalogue</dt><dd>{{ $shop->products_count }} pieces</dd></div><div><dt>Access</dt><dd>Direct seller</dd></div><div><dt>Status</dt><dd>Available</dd></div></dl><div class="wm2-shop-tags"><span>Wholesale ready</span><span>{{ $shop->market_category ?: 'Saree collection' }}</span></div><a class="wm2-shop-button" href="{{ $shop->publicUrl() }}">Visit shop <span>↗</span></a>@if($whatsappUrl)<a class="wm2-shop-secondary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Quick enquiry / catalogue <span>↗</span></a>@endif</div>
                        </article>
                    @empty
                        <div class="wm2-empty" role="status"><span aria-hidden="true">⌕</span><h3>No approved shops found</h3><p>We could not find an active shop matching “{{ $query }}”. Try a broader search or return to the complete network.</p><a href="{{ route('market.index') }}#directory">Reset search</a></div>
                    @endforelse
                </div>
            </section>

            <section class="wm2-assurance" id="assurance"><div class="wm-shell"><div class="wm2-assurance-head"><div><p class="wm2-eyebrow">Quality &amp; settlement architecture</p><h2>Loom-to-retail assurance</h2><p>Institutional sourcing clarity with simple, buyer-friendly signals.</p></div><div class="wm2-assurance-badges"><span>✓ Active catalogue</span><span>✓ Direct seller access</span></div></div><div class="wm2-assurance-grid"><article><img src="{{ $fallbackImages[0] }}" alt="Woven saree detail"><div><small>Verified catalogue</small><strong>Clear product details</strong></div></article><article><img src="{{ $fallbackImages[1] }}" alt="Saree model in a garden setting"><div><small>Wholesale ready</small><strong>Minimums made visible</strong></div></article><article><img src="{{ $fallbackImages[2] }}" alt="Saree collection detail"><div><small>Direct conversations</small><strong>Ask the seller quickly</strong></div></article><aside><span>◈</span><h3>Source with confidence.</h3><p>Shop status, catalogue counts and contact links are pulled from the live WholeMarket system.</p></aside></div></div></section>

            <section class="wm2-seller" id="about"><div class="wm-shell wm2-seller-inner"><div><p class="wm2-eyebrow">For verified saree manufacturers</p><h2>Are you a saree manufacturer or weaver?</h2><p>List your live catalogue, show availability clearly and let buyers enquire directly from one considered wholesale profile.</p></div><div class="wm2-seller-actions"><a href="{{ route('seller.signup') }}">Apply to sell wholesale <span>↗</span></a><a href="{{ route('login') }}">Seller login <span>↗</span></a></div></div></section>
        </main>

        <footer class="wm2-footer"><div class="wm-shell wm2-footer-grid"><div><a class="wm2-footer-brand" href="{{ route('market.index') }}"><span class="wm2-brand-mark">W</span><span><strong>WholeMarket</strong><small>Direct mill infrastructure</small></span></a><p>Independent saree businesses, beautifully presented for serious sourcing.</p></div><div><h3>Regional clusters</h3><a href="#directory">Kanchipuram</a><a href="#directory">Banarasi</a><a href="#directory">Chanderi</a><a href="#directory">Paithani</a></div><div><h3>Marketplace &amp; trade</h3><a href="#directory">Wholesale directory</a><a href="#assurance">Quality assurance</a><a href="{{ route('seller.signup') }}">Seller onboarding</a><a href="{{ route('login') }}">Seller login</a></div><div><h3>Verification &amp; desk</h3><a href="#assurance">Directory standards</a><a href="#about">Grow your shop</a><a href="{{ route('market.index') }}">Marketplace home</a></div></div><div class="wm-shell wm2-footer-bottom"><span>© {{ now()->year }} WholeMarket. Built for saree sourcing.</span><span>Privacy charter · Seller code of conduct</span></div></footer>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const button = document.querySelector('[data-wm-menu]');
            const nav = document.querySelector('.wm2-nav');
            const clear = document.querySelector('[data-wm-clear]');
            const input = document.querySelector('.wm2-search-input input');
            if (button && nav) {
                button.addEventListener('click', () => {
                    const open = nav.classList.toggle('is-open');
                    button.setAttribute('aria-expanded', String(open));
                });
            }
            if (clear && input) clear.addEventListener('click', () => { input.value = ''; input.form.submit(); });
        })();
    </script>
@endpush
