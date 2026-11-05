@extends('layouts.storefront')

@section('body_class', 'mehfil-boutique')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/mehfil-boutique/style.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('storefront');
        $shopUrl = $shop->publicUrl();
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $fallbackImages = ['saree.jpg', 'saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
        $showPrices = $shop->settings?->show_prices ?? true;
        $heroImage = $shop->banner_path ? Storage::url($shop->banner_path) : $assetBase.'/saree.jpg';
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shopUrl)
            : null;
        $phoneNumber = preg_replace('/\D/', '', (string) $shop->phone);
        $phoneUrl = preg_match('/^\d{8,15}$/', $phoneNumber) === 1 ? 'tel:'.$phoneNumber : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $instagramUrl = filter_var($shop->instagram_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->instagram_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->instagram_url : null;
        $facebookUrl = filter_var($shop->facebook_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->facebook_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->facebook_url : null;
    @endphp

    @if (! empty($previewingTemplate))
        <div class="mf-preview-bar" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
    @endif

    <a class="mf-skip" href="#catalogue">Skip to catalogue</a>
    <div class="mf-header">
        <div class="mf-ribbon"><div class="mf-ribbon-inner"><span>• Direct weavers &amp; master craftsmen · Pure Kanjivaram, Banarasi &amp; Chanderi silks · Verified partner</span><span><strong>Wholesale MOQ: 5 pcs/lot</strong> · INR (₹) · {{ $shop->name }}</span></div></div>
        <header class="mf-header-main">
            <a class="mf-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                <span class="mf-brand-mark">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="46" height="46">@else{{ Str::upper(Str::substr($shop->name, 0, 1)) }}@endif</span>
                <span><span class="mf-brand-name">{{ $shop->name }}</span><small class="mf-brand-sub">{{ $templateDetails['label'] }} · Bespoke silks</small></span>
            </a>
            <form class="mf-search" method="get" action="{{ $collectionUrl }}" role="search"><span aria-hidden="true">⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search by weave, fabric code or colour" aria-label="Search sarees"><button type="submit" aria-label="Search">→</button></form>
            <div class="mf-tools"><a class="mf-tool" href="#contact">Enquiry sheet <span class="mf-tool-count" data-favourite-count>0</span></a><a class="mf-tool" href="#contact" aria-label="Contact seller">◉</a></div>
        </header>
        <nav class="mf-nav" aria-label="Main navigation">
            <a href="{{ $shopUrl }}">All sarees</a>
            @foreach ($categories->take(6) as $category)
                <a href="{{ $collectionUrl.'?category='.$category->id }}#catalogue">{{ $category->name }}</a>
            @endforeach
            <a href="#fabrics">Shop by fabric</a><a href="#story">About atelier</a><a href="#contact">Contact &amp; showroom</a>
        </nav>
    </div>

    <main class="mf-main">
        <section class="mf-hero" aria-label="{{ $shop->name }} collection">
            <div class="mf-hero-inner">
                <div class="mf-hero-copy"><p class="mf-eyebrow">Verified heirloom looms · {{ Str::upper($templateDetails['badge']) }}</p><h1>{{ $templateDetails['hero_heading'] }}<br><em>{{ $templateDetails['hero_emphasis'] }}</em></h1><p>{{ $shop->description ?: 'Curated handloom treasures presented with the clarity your store, boutique or celebration deserves.' }}</p><a class="mf-button" href="#catalogue">Explore the catalogue <span aria-hidden="true">↗</span></a><div class="mf-hero-note"><span>Discover</span><span>✦</span><span>Weaves</span><span>✦</span><span>Direct enquiries</span></div></div>
                <div class="mf-hero-media"><img src="{{ $heroImage }}" alt="{{ $shop->name }} saree collection" width="1400" height="1200"><div class="mf-hero-caption"><small>{{ Str::upper($templateDetails['badge']) }}</small><strong>Details that make a difference.</strong></div></div>
            </div>
        </section>

        <section class="mf-benefits" aria-label="Shop benefits"><div class="mf-benefit"><span class="mf-benefit-icon">◈</span><div><strong>Purity assured</strong><span>Clear fabric and finish details</span></div></div><div class="mf-benefit"><span class="mf-benefit-icon">✂</span><div><strong>Direct looms</strong><span>Wholesale-ready pricing</span></div></div><div class="mf-benefit"><span class="mf-benefit-icon">✧</span><div><strong>Global dispatch</strong><span>Seller-confirmed timelines</span></div></div><div class="mf-benefit"><span class="mf-benefit-icon">↗</span><div><strong>WhatsApp dossier</strong><span>Instant buyer connection</span></div></div></section>

        @if ($categories->isNotEmpty())
            <section class="mf-section" id="collections" aria-labelledby="collections-heading"><div class="mf-heading"><div><p class="mf-eyebrow">Master loom archives · 2025 release</p><h2 id="collections-heading">Master loom collections</h2></div><p>Explore the categories already curated by {{ $shop->name }}, with live products and availability behind every chapter.</p></div><div class="mf-category-grid">
                @foreach ($categories->take(4)->values() as $index => $category)
                    @php $categoryProduct = $products->getCollection()->firstWhere('category_id', $category->id); $categoryImage = $categoryProduct?->images?->first() ? Storage::url($categoryProduct->images->first()->path) : $assetBase.'/'.$fallbackImages[$index % count($fallbackImages)]; @endphp
                    <a class="mf-category-card" href="{{ $collectionUrl.'?category='.$category->id }}#catalogue"><img src="{{ $categoryImage }}" alt="{{ $category->name }} collection" loading="lazy" width="720" height="900"><span class="mf-category-copy"><small>{{ Str::upper($shop->market_category ?: 'SAREE COLLECTION') }}</small><strong>{{ $category->name }} ↗</strong></span></a>
                @endforeach
            </div></section>
        @endif

        <section class="mf-section mf-section-muted mf-catalogue" id="catalogue" aria-labelledby="catalogue-heading"><div class="mf-section-inner"><div class="mf-heading"><div><p class="mf-eyebrow">Collector editions</p><h2 id="catalogue-heading">The saree archive</h2></div><p>{{ $products->total() }} curated {{ Str::plural('piece', $products->total()) }} · Wholesale-ready details</p></div><div class="mf-filter-wrap"><form class="mf-filterbar" method="get" action="{{ $collectionUrl }}" role="search"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search archive by name, code or colour" aria-label="Search archive"><select name="category"><option value="">All collections</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select><select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select><button type="submit">Filter</button></form><a class="mf-filter-clear" href="{{ $collectionUrl }}#catalogue">Clear filters ×</a></div>
                @if ($products->isNotEmpty())
                    <div class="mf-product-grid">
                        @foreach ($products as $product)
                            @php $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$fallbackImages[$loop->index % count($fallbackImages)]; $price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry'; @endphp
                            <article class="mf-product"><div class="mf-product-media"><a href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy" width="720" height="960"></a><span class="mf-product-badge">{{ Str::upper($product->fabric?->name ?: 'SAREE') }}</span><button class="mf-save" type="button" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">♡</button></div><div class="mf-product-body"><span class="mf-product-code">{{ $product->code }} · {{ $product->category?->name ?: 'Saree collection' }}</span><h3><a href="{{ $product->publicUrl() }}">{{ $product->name }}</a></h3><p class="mf-product-description">{{ $product->description ?: 'A considered saree selected for colour, drape and finish.' }}</p><p class="mf-product-meta">{{ $product->colour ?: 'Colour on enquiry' }} · Minimum {{ $product->minimum_order_quantity }} pieces</p><div class="mf-product-price"><strong>{{ $price }}</strong><span>{{ Str::headline($product->availability) }}</span></div>@include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])</div></article>
                        @endforeach
                    </div>
                    @if ($products->hasPages())<nav class="mf-pagination" aria-label="Catalogue pages">@if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#catalogue">← Previous</a>@endif<span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>@if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#catalogue">Next →</a>@endif</nav>@endif
                @else
                    <div class="mf-empty">The archive is being prepared. Contact {{ $shop->name }} for a private catalogue.</div>
                @endif
            </div></section>

        <section class="mf-story" id="story"><div class="mf-story-grid"><div><p class="mf-eyebrow">The WholeMarket edit</p><h2>Drapes beyond the ordinary.</h2><p>{{ $shop->description ?: 'From first look to final enquiry, give every saree the space it deserves. Compare details, save favourites and connect directly with the people behind the collection.' }}</p><a class="mf-button" href="#contact">Discover the atelier <span aria-hidden="true">↗</span></a></div><div class="mf-story-card"><strong>Quiet luxury, practical sourcing.</strong><h3>Built for your next collection.</h3><ul><li>Live product names, images, prices and availability</li><li>Clear minimums for wholesale or boutique buying</li><li>Direct contact with {{ $shop->name }}</li></ul></div></div></section>

        @if ($fabrics->isNotEmpty())
            <section class="mf-section" id="fabrics" aria-labelledby="fabrics-heading"><div class="mf-heading"><div><p class="mf-eyebrow">Texture &amp; fall</p><h2 id="fabrics-heading">Shop by fabric</h2></div><p>Build a collection with the considered hand-feel your customers remember.</p></div><div class="mf-fabric-grid">@foreach ($fabrics->take(4)->values() as $index => $fabric)<a class="mf-fabric-card" href="{{ $collectionUrl.'?fabric='.$fabric->id }}#catalogue"><img src="{{ $assetBase.'/'.$fallbackImages[$index % count($fallbackImages)] }}" alt="{{ $fabric->name }} sarees" loading="lazy" width="520" height="520"><span>{{ $fabric->name }} ↗</span></a>@endforeach</div></section>
        @endif

        <section class="mf-contact" id="contact"><div class="mf-contact-grid"><div><p class="mf-eyebrow">Private viewing</p><h2>Begin a considered conversation.</h2><p>Ask about quantities, colours, prices and current availability from {{ $shop->name }}.</p>@if ($whatsappUrl)<a class="mf-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp the seller ↗</a>@elseif ($shopEmail)<a class="mf-button" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>@elseif ($phoneUrl)<a class="mf-button" href="{{ $phoneUrl }}">Call the seller ↗</a>@endif</div><div class="mf-contact-card"><h3>{{ $shop->name }}</h3>@if ($shop->shop_number || $shop->address)<p>{{ collect([$shop->shop_number, $shop->address])->filter()->implode(', ') }}</p>@endif @if ($shop->opening_hours)<p><strong>Opening hours</strong><br>{{ $shop->opening_hours }}</p>@endif @if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail)<div class="mt-4">@include('shop.partials.contact-icons', ['shop' => $shop, 'whatsappUrl' => $whatsappUrl, 'instagramUrl' => $instagramUrl, 'facebookUrl' => $facebookUrl, 'shopEmail' => $shopEmail])</div>@endif</div></div></section>
    </main>
    @include('shop.partials.storefront-footer', ['shop' => $shop])
@endsection

@push('scripts')
    <script>
        (() => {
            const favouriteKey = {{ Illuminate\Support\Js::from('wholemarket:'.$shop->id.':'.$template.':favourites') }};
            let saved = [];
            try { saved = JSON.parse(localStorage.getItem(favouriteKey) || '[]'); } catch { saved = []; }
            const savedProducts = new Set(Array.isArray(saved) ? saved.map(String) : []);
            const updateSaved = () => { document.querySelectorAll('[data-product-id]').forEach((button) => { const active = savedProducts.has(button.dataset.productId); button.textContent = active ? '♥' : '♡'; button.setAttribute('aria-pressed', String(active)); }); document.querySelectorAll('[data-favourite-count]').forEach((count) => { count.textContent = String(savedProducts.size); }); };
            document.querySelectorAll('[data-product-id]').forEach((button) => button.addEventListener('click', () => { const id = button.dataset.productId; savedProducts.has(id) ? savedProducts.delete(id) : savedProducts.add(id); localStorage.setItem(favouriteKey, JSON.stringify([...savedProducts])); updateSaved(); }));
            updateSaved();
        })();
    </script>
@endpush
