@extends('layouts.storefront')

@section('body_class', 'rang-studio')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/rang-studio/style.css') }}">
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
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $instagramUrl = filter_var($shop->instagram_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->instagram_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->instagram_url : null;
        $facebookUrl = filter_var($shop->facebook_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->facebook_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->facebook_url : null;
    @endphp

    @if (! empty($previewingTemplate))
        <div class="rs-preview-bar" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
    @endif

    <a class="rs-skip" href="#catalogue">Skip to catalogue</a>
    <header class="rs-header">
        <div class="rs-topline"><span>• Direct handloom weaves &amp; wholesale silk catalogues · Pan-India dispatch</span><span>{{ $shop->name }} · INR ₹</span></div>
        <div class="rs-header-main">
            <a class="rs-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                <span class="rs-mark">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="44" height="44">@else{{ Str::upper(Str::substr($shop->name, 0, 1)) }}@endif</span>
                <span><strong>{{ $shop->name }}</strong><small>RANG STUDIO · WHOLEMARKET</small></span>
            </a>
            <nav class="rs-nav" aria-label="Main navigation"><a href="{{ $shopUrl }}#catalogue">Collections</a><a href="{{ $shopUrl }}#catalogue">Drapes &amp; silks</a><a href="{{ $shopUrl }}#categories">Occasions</a><a href="{{ $shopUrl }}#about">About studio</a><a href="{{ $shopUrl }}#contact">Contact</a></nav>
            <form class="rs-search" method="get" action="{{ $collectionUrl }}" role="search"><span aria-hidden="true">⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search weaves, colours, codes" aria-label="Search catalogue"><button type="submit">Search</button></form>
        </div>
    </header>

    <main>
        <section class="rs-hero" aria-label="{{ $shop->name }} collection">
            <div class="rs-hero-copy"><p class="rs-kicker">Wholesale &amp; retail catalogue · {{ Str::upper($templateDetails['badge']) }}</p><h1>{{ $templateDetails['hero_heading'] }}<br><em>{{ $templateDetails['hero_emphasis'] }}</em></h1><p>{{ $shop->description ?: 'Heritage weaves translated for the modern wardrobe. Discover considered sarees with clarity, warmth and a direct path to the atelier.' }}</p><div class="rs-actions"><a class="rs-button rs-button-dark" href="#catalogue">Explore catalogue <span>↗</span></a>@if ($whatsappUrl)<a class="rs-button rs-button-light" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Talk to the seller <span>↗</span></a>@endif</div><div class="rs-stats"><span><b>{{ $products->total() }}</b> curated pieces</span><span><b>{{ $categories->count() }}</b> collections</span><span><b>Live</b> availability</span></div></div>
            <div class="rs-hero-media"><img src="{{ $heroImage }}" alt="{{ $shop->name }} saree collection" width="1200" height="1000"><div class="rs-hero-caption"><small>RANG EDIT · {{ Str::upper($shop->market_category ?: 'SAREE ATELIER') }}</small><strong>Stories in every drape.</strong></div></div>
        </section>

        @if ($categories->isNotEmpty())
            <section class="rs-section" id="categories" aria-labelledby="categories-heading"><div class="rs-section-head"><div><p class="rs-kicker">Curated chapters</p><h2 id="categories-heading">Find your occasion</h2></div><p>Browse the live collections already curated by {{ $shop->name }}.</p></div><div class="rs-category-grid">@foreach ($categories->take(5)->values() as $index => $category) @php $categoryProduct = $products->getCollection()->firstWhere('category_id', $category->id); $categoryImage = $categoryProduct?->images?->first() ? Storage::url($categoryProduct->images->first()->path) : $assetBase.'/'.$fallbackImages[$index % count($fallbackImages)]; @endphp<a class="rs-category" href="{{ $collectionUrl.'?category='.$category->id }}#catalogue"><img src="{{ $categoryImage }}" alt="{{ $category->name }} sarees" loading="lazy"><span><small>{{ Str::upper($shop->market_category ?: 'COLLECTION') }}</small><strong>{{ $category->name }} ↗</strong></span></a>@endforeach</div></section>
        @endif

        <section class="rs-section rs-catalogue" id="catalogue" aria-labelledby="catalogue-heading"><div class="rs-section-head"><div><p class="rs-kicker">Live studio catalogue</p><h2 id="catalogue-heading">Sculpted by ancient looms</h2></div><p>{{ $products->total() }} pieces · Wholesale-ready details</p></div><div class="rs-filter-wrap"><form class="rs-filterbar" method="get" action="{{ $collectionUrl }}" role="search"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search the catalogue" aria-label="Search catalogue"><select name="category"><option value="">All collections</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select><select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select><select name="sort"><option value="">Latest arrivals</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select><button type="submit">Filter</button></form><a class="rs-clear" href="{{ $collectionUrl }}#catalogue">Clear filters ×</a></div>
            @if ($products->isNotEmpty())<div class="rs-product-grid">@foreach ($products as $product) @php $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$fallbackImages[$loop->index % count($fallbackImages)]; $price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry'; @endphp<article class="rs-product"><a class="rs-product-media" href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy"><span>{{ Str::upper($product->fabric?->name ?: 'SAREE') }}</span></a><div class="rs-product-body"><small>{{ $product->code }} · {{ $product->category?->name ?: 'Saree collection' }}</small><h3><a href="{{ $product->publicUrl() }}">{{ $product->name }}</a></h3><p>{{ $product->description ?: 'A considered saree selected for colour, drape and finish.' }}</p><div class="rs-price"><strong>{{ $price }}</strong><span>{{ Str::headline($product->availability) }} · MOQ {{ $product->minimum_order_quantity }}</span></div>@include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])</div></article>@endforeach</div>@if ($products->hasPages())<nav class="rs-pagination" aria-label="Catalogue pages">@if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#catalogue">← Previous</a>@endif<span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>@if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#catalogue">Next →</a>@endif</nav>@endif @else<div class="rs-empty">No sarees match these filters. Clear the filters or contact {{ $shop->name }} for a private catalogue.</div>@endif
        </section>

        <section class="rs-story" id="about"><div class="rs-story-inner"><div><p class="rs-kicker">The atelier journal</p><h2>Architecture of the drape.</h2><p>{{ $shop->description ?: 'Every saree carries the patient rhythm of a loom. Explore colour, weave and finish, then speak directly with the people behind the collection.' }}</p><a class="rs-button rs-button-light" href="#contact">Discover the studio <span>↗</span></a></div><div class="rs-story-card"><strong>Craft-led sourcing</strong><h3>Clear enough for business. Warm enough for people.</h3><ul><li>Live product images, prices and availability</li><li>Minimums made clear for every piece</li><li>Direct WhatsApp enquiries when configured</li></ul></div></div></section>

        @if ($fabrics->isNotEmpty())<section class="rs-section" id="fabrics" aria-labelledby="fabrics-heading"><div class="rs-section-head"><div><p class="rs-kicker">Texture &amp; fall</p><h2 id="fabrics-heading">Shop by fabric &amp; guild</h2></div><p>Choose the hand-feel your next collection needs.</p></div><div class="rs-fabric-grid">@foreach ($fabrics->take(5)->values() as $index => $fabric)<a href="{{ $collectionUrl.'?fabric='.$fabric->id }}#catalogue" class="rs-fabric"><img src="{{ $assetBase.'/'.$fallbackImages[$index % count($fallbackImages)] }}" alt="{{ $fabric->name }} sarees" loading="lazy"><span>{{ $fabric->name }} <b>↗</b></span></a>@endforeach</div></section>@endif

        <section class="rs-contact" id="contact"><div><p class="rs-kicker">Atelier concierge</p><h2>Begin a considered conversation.</h2><p>Ask about quantities, colours, prices and availability from {{ $shop->name }}.</p>@if ($whatsappUrl)<a class="rs-button rs-button-dark" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Enquire with atelier ↗</a>@elseif ($shopEmail)<a class="rs-button rs-button-dark" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>@endif</div><div class="rs-contact-card"><h3>{{ $shop->name }}</h3>@if ($shop->shop_number || $shop->address)<p>{{ collect([$shop->shop_number, $shop->address])->filter()->implode(', ') }}</p>@endif @if ($shop->opening_hours)<p><strong>Hours</strong><br>{{ $shop->opening_hours }}</p>@endif @if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail) @include('shop.partials.contact-icons', ['shop' => $shop, 'whatsappUrl' => $whatsappUrl, 'instagramUrl' => $instagramUrl, 'facebookUrl' => $facebookUrl, 'shopEmail' => $shopEmail]) @endif</div></section>
    </main>

    @include('shop.partials.storefront-footer', ['shop' => $shop])
@endsection
