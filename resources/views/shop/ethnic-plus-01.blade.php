@extends('layouts.storefront')

@section('body_class', 'ethnic-plus-storefront')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/ethnic-plus-01/style.css') }}">
@endpush

@section('content')
    @php
        $shopUrl = $shop->publicUrl();
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $dressFallbackImages = collect(range(1, 8))->map(fn (int $index): string => asset('templates/dress-models/dress-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).'.jpg'))->all();
        $fallbackImage = $dressFallbackImages[0];
        $useDressPreviewImages = ! empty($previewingTemplate);
        $catalogueProducts = $products->getCollection();
        $heroProduct = $catalogueProducts->first();
        $featureProduct = $catalogueProducts->get(1) ?: $heroProduct;
        $editorialProduct = $catalogueProducts->get(2) ?: $heroProduct;
        // A shop can retain a banner path after the file has been removed or
        // moved. Only use a banner when it is still available; otherwise the
        // first catalogue image (or the editorial fallback) keeps the hero
        // visually complete instead of showing a broken image.
        $bannerImage = filled($shop->banner_path) && Storage::disk('public')->exists($shop->banner_path)
            ? Storage::url($shop->banner_path)
            : null;
        $heroImage = $bannerImage
            ?: ($heroProduct?->images?->first() && ! $useDressPreviewImages ? Storage::url($heroProduct->images->first()->path) : $fallbackImage);
        $featureImage = $featureProduct?->images?->first() && ! $useDressPreviewImages ? Storage::url($featureProduct->images->first()->path) : $dressFallbackImages[1];
        $editorialImage = $editorialProduct?->images?->first() && ! $useDressPreviewImages ? Storage::url($editorialProduct->images->first()->path) : $dressFallbackImages[2];
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shopUrl)
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $priceLabel = static fn ($product): string => $showPrices && $product?->priceInRupees() !== null
            ? '₹'.number_format($product->priceInRupees(), 2)
            : 'Price on enquiry';
    @endphp

    @if (! empty($previewingTemplate))
        <div class="ep-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
    @endif

    <div class="ep-utility"><span>Imperial couture · inclusive occasionwear · direct sourcing</span><span>{{ $shop->name }} · INR ₹</span></div>

    <header class="ep-header">
        <div class="ep-header-main">
            <div class="ep-header-side ep-header-left">
                <span class="ep-currency">◎ INR ₹</span>
                <form class="ep-search" method="get" action="{{ $collectionUrl }}" role="search"><span aria-hidden="true">⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search bridal, silk, occasionwear" aria-label="Search catalogue"></form>
            </div>
            <a class="ep-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                <span class="ep-brand-mark">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="42" height="42">@else{{ Str::upper(Str::substr($shop->name, 0, 1)) }}@endif</span>
                <span><strong>{{ $shop->name }}</strong><small>IMPERIAL COUTURE EDIT</small></span>
            </a>
            <div class="ep-header-side ep-header-right"><a href="{{ $collectionUrl }}#catalogue" aria-label="View catalogue">♡</a>@if ($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" aria-label="WhatsApp the studio">◌</a>@endif<a href="{{ $collectionUrl }}#about" aria-label="About the shop">◎</a></div>
        </div>
        <nav class="ep-nav" aria-label="Main navigation"><a href="{{ $collectionUrl }}#trending">Bestsellers</a><a href="{{ $collectionUrl }}#categories">Silhouettes</a><a href="{{ $collectionUrl }}#plus-size-spotlight">Inclusive edit</a><a href="{{ $collectionUrl }}#tailor">Virtual tailor</a><a href="{{ $collectionUrl }}#about">About</a><a href="{{ $collectionUrl }}#contact">Contact</a></nav>
    </header>

    <main class="ep-main">
        <section class="ep-hero" aria-labelledby="ep-hero-heading">
            <div class="ep-hero-media" style="--ep-hero-fallback: url('{{ $fallbackImage }}');"><img src="{{ $heroImage }}" alt="{{ $shop->name }} occasionwear collection" width="1600" height="900" fetchpriority="high" decoding="async" onerror="this.onerror=null;this.hidden=true;"><div class="ep-hero-shade"></div><div class="ep-hero-copy"><p class="ep-kicker">THE IMPERIAL COUTURE EDIT</p><h1 id="ep-hero-heading">{{ $shop->name }}<br><em>occasion collection.</em></h1><p>{{ $shop->description ?: 'Regal Indian occasionwear for celebrations, heirloom memories and the modern boutique wardrobe.' }}</p><div class="ep-hero-offers"><span>{{ $products->total() }} live styles</span><span>Wholesale-ready details</span><span>Direct seller access</span></div><div class="ep-actions"><a class="ep-button ep-button-primary" href="{{ $collectionUrl }}#trending">Shop the edit <span aria-hidden="true">↗</span></a><a class="ep-button ep-button-outline" href="{{ $collectionUrl }}#plus-size-spotlight">Explore inclusive fits <span aria-hidden="true">↗</span></a></div></div></div>
        </section>

        <section class="ep-proof" aria-label="Shop benefits"><div><span>✦</span><strong>Authentic craft</strong><small>Curated Indian occasionwear</small></div><div><span>◈</span><strong>Clear buying</strong><small>MOQ, packs and prices on every style</small></div><div><span>↗</span><strong>Personal access</strong><small>Connect directly with {{ $shop->name }}</small></div></section>

        <section class="ep-showcase"><div class="ep-showcase-media"><img src="{{ $featureImage }}" alt="{{ $featureProduct?->name ?: 'Featured ethnicwear' }}" loading="lazy"></div><div class="ep-showcase-copy"><p class="ep-kicker">ROYAL GARDEN COUTURE</p><h2>Celebration dressing,<br><em>considered beautifully.</em></h2><p>Build a confident assortment from the live catalogue. Discover festive textures, occasion silhouettes and pieces your customers will remember.</p><a class="ep-text-link" href="{{ $collectionUrl }}#trending">View the lookbook ↗</a></div></section>

        @if ($categories->isNotEmpty())
            <section class="ep-section ep-section-sand" id="categories" aria-labelledby="ep-categories-heading"><div class="ep-section-heading ep-centered"><p class="ep-kicker">Hand-curated silhouettes</p><h2 id="ep-categories-heading">Trending collections</h2><span class="ep-rule"></span></div><div class="ep-category-grid">@foreach ($categories->take(6)->values() as $index => $category) @php($categoryProduct = $catalogueProducts->firstWhere('category_id', $category->id)) @php($categoryImage = $categoryProduct?->images?->first() && ! $useDressPreviewImages ? Storage::url($categoryProduct->images->first()->path) : $dressFallbackImages[$index % count($dressFallbackImages)])<a class="ep-category-card" href="{{ $collectionUrl.'?category='.$category->id }}#catalogue"><span class="ep-category-image"><img src="{{ $categoryImage }}" alt="{{ $category->name }}" loading="lazy"></span><strong>{{ $category->name }}</strong><small>{{ $categoryProduct?->priceInRupees() !== null && $showPrices ? 'From ₹'.number_format($categoryProduct->priceInRupees(), 0) : 'Explore the edit' }}</small></a>@endforeach</div></section>
        @endif

        <section class="ep-section ep-section-soft" id="plus-size-spotlight" aria-labelledby="ep-inclusive-heading"><div class="ep-inclusive"><div class="ep-inclusive-copy"><p class="ep-kicker">EVERY SIZE MATTERS</p><h2 id="ep-inclusive-heading">An inclusive edit<br><em>for every body.</em></h2><p>Make space for more customers with thoughtfully selected silhouettes and clear fit conversations. Ask {{ $shop->name }} about measurements, packs and availability before you commit.</p><a class="ep-button ep-button-primary" href="{{ $collectionUrl }}#contact">Ask about fit ↗</a></div><div class="ep-inclusive-media"><img src="{{ $editorialImage }}" alt="Inclusive occasionwear edit" loading="lazy"><span>Inclusive occasionwear · {{ $categories->count() }} collections</span></div></div><div class="ep-mini-grid">@foreach ($catalogueProducts->take(4) as $product)<a href="{{ $product->publicUrl() }}" class="ep-mini-card"><span><img src="{{ $product->images->first() && ! $useDressPreviewImages ? Storage::url($product->images->first()->path) : $dressFallbackImages[$loop->index % count($dressFallbackImages)] }}" alt="{{ $product->name }}" loading="lazy"></span><strong>{{ $product->name }}</strong><small>{{ $priceLabel($product) }}</small></a>@endforeach</div></section>

        <section class="ep-section ep-section-sand" id="trending" aria-labelledby="ep-trending-heading"><div class="ep-section-heading"><div><p class="ep-kicker">Best seller &amp; trending atelier</p><h2 id="ep-trending-heading">The current edit.</h2></div><p>{{ $products->total() }} {{ Str::plural('style', $products->total()) }} · Updated from {{ $shop->name }}</p></div><div class="ep-catalogue-wrap" id="catalogue"><form class="ep-filterbar" method="get" action="{{ $collectionUrl }}" role="search"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search styles or codes" aria-label="Search products"><select name="category" aria-label="Filter by category"><option value="">All collections</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select><select name="fabric" aria-label="Filter by fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select><select name="sort" aria-label="Sort products"><option value="">Latest arrivals</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select><button type="submit">Apply filters</button></form>@if (request()->hasAny(['q', 'category', 'fabric', 'sort']))<a class="ep-filter-clear" href="{{ $collectionUrl }}#trending">Clear all filters ×</a>@endif
                @if ($products->isNotEmpty())
                    <div class="ep-product-grid">@foreach ($products as $product) @php($productImage = $product->images->first() && ! $useDressPreviewImages ? Storage::url($product->images->first()->path) : $dressFallbackImages[$loop->index % count($dressFallbackImages)])<article class="ep-product"><a class="ep-product-media" href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" width="720" height="960" loading="lazy"><span>{{ Str::headline($product->availability) }}</span><b>View details ↗</b></a><div class="ep-product-copy"><small>{{ $product->code }} · {{ $product->category?->name ?: 'Occasionwear' }}</small><h3><a href="{{ $product->publicUrl() }}">{{ $product->name }}</a></h3><p>{{ $product->description ?: 'A considered ethnicwear style selected for colour, finish and confident boutique merchandising.' }}</p><div class="ep-price-row"><strong>{{ $priceLabel($product) }}</strong><span>MOQ {{ $product->minimum_order_quantity }} · {{ $product->pack_size ?: 'Flexible pack' }}</span></div>@include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])</div></article>@endforeach</div>
                    @if ($products->hasPages())<nav class="ep-pagination" aria-label="Catalogue pages">@if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#trending">← Previous</a>@endif<span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>@if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#trending">Next →</a>@endif</nav>@endif
                @else
                    <div class="ep-empty"><span aria-hidden="true">✦</span><h3>Your next couture edit starts here.</h3><p>No styles match these filters yet. Clear the filters or contact {{ $shop->name }} for a private catalogue.</p></div>
                @endif
            </div><a class="ep-outline-link" href="{{ $collectionUrl }}#trending">Discover the complete catalogue ↗</a></section>

        <section class="ep-editorial"><div class="ep-editorial-media"><img src="{{ $editorialImage }}" alt="{{ $editorialProduct?->name ?: 'Couture occasionwear' }}" loading="lazy"><span class="ep-editorial-stamp">THE HERITAGE EDIT</span></div><div class="ep-editorial-copy"><p class="ep-kicker">A CONSIDERED OCCASION</p><h2>Details that<br><em>feel like heirlooms.</em></h2><p>From first glance to final pack, every style is presented with the details buyers need to make a confident choice.</p><ul><li><span>✓</span> Clear catalogue and availability</li><li><span>✓</span> Direct seller conversations</li><li><span>✓</span> Thoughtful fabrics and finishing</li></ul><a class="ep-button ep-button-primary" href="{{ $collectionUrl }}#contact">Speak with the studio ↗</a></div></section>

        <section class="ep-section ep-section-sand" id="tailor" aria-labelledby="ep-tailor-heading"><div class="ep-section-heading ep-centered"><p class="ep-kicker">Bespoke service</p><h2 id="ep-tailor-heading">Your virtual tailor.</h2><p>Bring a more confident fit conversation to every occasion edit.</p></div><div class="ep-steps"><div><span>01</span><h3>Choose a silhouette</h3><p>Share the occasion, fabric and finish your customer is looking for.</p></div><div><span>02</span><h3>Submit measurements</h3><p>Ask the studio for guided sizing and inclusive fit support.</p></div><div><span>03</span><h3>Curate with confidence</h3><p>Confirm pack, minimum and dispatch details before ordering.</p></div></div><div class="ep-centered"><a class="ep-button ep-button-primary" href="{{ $collectionUrl }}#contact">Request a fit conversation ↗</a></div></section>

        <section class="ep-section ep-section-soft ep-contact-section" id="about" aria-labelledby="ep-about-heading"><div class="ep-section-heading"><div><p class="ep-kicker">The atelier, behind the edit</p><h2 id="ep-about-heading">A wardrobe with a point of view.</h2></div><p>{{ $shop->description ?: 'A considered collection of Indian ethnicwear, presented with direct access to the people behind every style.' }}</p></div><div class="ep-contact-grid" id="contact"><div class="ep-contact-copy"><h3>Let’s build your next collection.</h3><p>Tell {{ $shop->name }} what you are sourcing and the team can share availability, packs and fit guidance.</p>@if ($whatsappUrl)<a class="ep-text-link" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp the studio ↗</a>@elseif ($shopEmail)<a class="ep-text-link" href="mailto:{{ $shopEmail }}">Email the studio ↗</a>@endif</div><form class="ep-enquiry-form" method="post" action="{{ route('enquiries.store', ['type' => $shop->type, 'shop' => $shop]) }}">@csrf<h3>Start a conversation.</h3>@if (session('status'))<p class="ep-form-status" role="status">{{ session('status') }}</p>@endif<label><span>Your name</span><input name="name" value="{{ old('name') }}" autocomplete="name" required></label><label><span>Email</span><input name="email" type="email" value="{{ old('email') }}" autocomplete="email"></label><label><span>Phone</span><input name="phone" value="{{ old('phone') }}" autocomplete="tel"></label><label><span>What are you looking for?</span><textarea name="message" rows="3" required>{{ old('message') }}</textarea></label><button type="submit">Send enquiry <span aria-hidden="true">→</span></button></form></div></section>
    </main>

    @include('shop.partials.storefront-footer', ['shop' => $shop, 'footerTagline' => 'Imperial occasionwear, thoughtfully presented for every boutique edit.', 'footerCollectionLabel' => 'Couture catalogue', 'footerCollectionAnchor' => 'trending', 'footerContactAnchor' => 'contact'])
@endsection
