@extends('layouts.storefront')

@section('body_class', 'shree-storefront')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/shree-01/style.css') }}">
@endpush

@section('content')
    @php
        $shopUrl = $shop->publicUrl();
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $dressFallbackImages = collect(range(1, 8))->map(fn (int $index): string => asset('templates/dress-models/dress-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).'.jpg'))->all();
        $fallbackImage = $dressFallbackImages[0];
        $useDressPreviewImages = ! empty($previewingTemplate);
        $heroProduct = $products->getCollection()->first();
        $bannerImage = filled($shop->banner_path) && Storage::disk('public')->exists($shop->banner_path)
            ? Storage::url($shop->banner_path)
            : null;
        $heroImage = $bannerImage
            ?: ($heroProduct?->images?->first() && ! $useDressPreviewImages ? Storage::url($heroProduct->images->first()->path) : $fallbackImage);
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shopUrl)
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    @if (! empty($previewingTemplate))
        <div class="shree-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
    @endif

    <div class="shree-utility"><span>Festive ethnicwear · considered silhouettes · direct sourcing</span><span>{{ $shop->name }} · INR ₹</span></div>

    <header class="shree-header">
        <div class="shree-header-main">
            <a class="shree-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                <span class="shree-brand-mark">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="42" height="42">@else{{ Str::upper(Str::substr($shop->name, 0, 1)) }}@endif</span>
                <span><strong>{{ $shop->name }}</strong><small>SHREE · ready-to-wear</small></span>
            </a>
            <nav class="shree-nav" aria-label="Main navigation">
                <a href="{{ $collectionUrl }}#silhouettes">Silhouettes</a><a href="{{ $collectionUrl }}#deals">Best deals</a><a href="{{ $collectionUrl }}#stories">Stories</a><a href="{{ $collectionUrl }}#about">About</a>
            </nav>
            <form class="shree-search" method="get" action="{{ $collectionUrl }}" role="search"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search styles" aria-label="Search catalogue"><button type="submit" aria-label="Search">⌕</button><span>₹</span></form>
        </div>
    </header>

    <main class="shree-main">
        <section class="shree-hero" aria-labelledby="shree-hero-heading">
            <div class="shree-hero-copy"><p class="shree-kicker">SHREE — STYLE IS SPECIAL</p><h1 id="shree-hero-heading">Elegance in every<br><em>weave and flare.</em></h1><p>{{ $shop->description ?: 'Discover Indian ethnicwear with a festive point of view: expressive dresses, easy co-ords and boutique-ready details for every occasion.' }}</p><div class="shree-actions"><a class="shree-button shree-button-primary" href="{{ $collectionUrl }}#catalogue">Explore the edit <span aria-hidden="true">↗</span></a>@if ($whatsappUrl)<a class="shree-button shree-button-secondary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Talk to the studio <span aria-hidden="true">↗</span></a>@endif</div><div class="shree-hero-meta"><span><b>{{ $products->total() }}</b> live styles</span><span><b>{{ $categories->count() }}</b> collections</span><span><b>60%</b> seasonal drops</span></div></div>
            <div class="shree-hero-media"><img src="{{ $heroImage }}" alt="{{ $shop->name }} ethnicwear collection" width="1200" height="900" fetchpriority="high" decoding="async" onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"><div class="shree-hero-overlay"><small>THE SHREE EDIT</small><strong>Festive dressing,<br>made effortless.</strong></div></div>
        </section>

        <section class="shree-benefits" aria-label="Shop benefits"><div><b>01</b><strong>Silhouette-led</strong><span>Find a look for every mood</span></div><div><b>02</b><strong>Wholesale ready</strong><span>Clear MOQ and live pricing</span></div><div><b>03</b><strong>Direct access</strong><span>Ask the studio before you commit</span></div></section>

        @if ($categories->isNotEmpty())
            <section class="shree-section" id="silhouettes" aria-labelledby="shree-silhouette-heading"><div class="shree-section-head"><div><p class="shree-kicker">Find your fit</p><h2 id="shree-silhouette-heading">Shop by dress silhouette</h2></div><p>Curated categories from {{ $shop->name }}, connected to the live catalogue.</p></div><div class="shree-silhouette-grid">@foreach ($categories->take(6)->values() as $index => $category) @php($categoryProduct = $products->getCollection()->firstWhere('category_id', $category->id)) @php($categoryImage = $categoryProduct?->images?->first() && ! $useDressPreviewImages ? Storage::url($categoryProduct->images->first()->path) : $dressFallbackImages[$index % count($dressFallbackImages)])<a class="shree-silhouette-card" href="{{ $collectionUrl.'?category='.$category->id }}#catalogue"><img src="{{ $categoryImage }}" alt="{{ $category->name }}" loading="lazy"><span><small>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }} · COLLECTION</small><strong>{{ $category->name }}</strong><em>Explore ↗</em></span></a>@endforeach</div></section>
        @endif

        <section class="shree-section shree-section-cream" id="deals" aria-labelledby="shree-deals-heading"><div class="shree-section-head"><div><p class="shree-kicker">Limited-time edit</p><h2 id="shree-deals-heading">Best deals</h2></div><p>{{ $products->total() }} {{ Str::plural('style', $products->total()) }} · Updated from your catalogue</p></div><div class="shree-catalogue-wrap" id="catalogue"><form class="shree-filterbar" method="get" action="{{ $collectionUrl }}" role="search"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or code" aria-label="Search products"><select name="category" aria-label="Filter by category"><option value="">All categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select><select name="fabric" aria-label="Filter by fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select><select name="sort" aria-label="Sort products"><option value="">Latest arrivals</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select><button type="submit">Apply filters</button></form>@if (request()->hasAny(['q', 'category', 'fabric', 'sort']))<a class="shree-filter-clear" href="{{ $collectionUrl }}#catalogue">Clear all filters ×</a>@endif
                @if ($products->isNotEmpty())
                    <div class="shree-product-grid">@foreach ($products as $product) @php($productImage = $product->images->first() && ! $useDressPreviewImages ? Storage::url($product->images->first()->path) : $dressFallbackImages[$loop->index % count($dressFallbackImages)]) @php($price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry')<article class="shree-product"><a class="shree-product-media" href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" width="720" height="960" loading="lazy"><span>{{ Str::headline($product->availability) }}</span><b>View style ↗</b></a><div class="shree-product-copy"><small>{{ $product->code }} · {{ $product->category?->name ?: 'Ethnicwear' }}</small><h3><a href="{{ $product->publicUrl() }}">{{ $product->name }}</a></h3><p>{{ $product->description ?: 'A considered ethnicwear style with boutique-ready detail.' }}</p><div class="shree-price-row"><strong>{{ $price }}</strong><span>MOQ {{ $product->minimum_order_quantity }} · {{ $product->pack_size ?: 'Flexible pack' }}</span></div>@include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])</div></article>@endforeach</div>
                    @if ($products->hasPages())<nav class="shree-pagination" aria-label="Catalogue pages">@if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#catalogue">← Previous</a>@endif<span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>@if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#catalogue">Next →</a>@endif</nav>@endif
                @else
                    <div class="shree-empty"><span aria-hidden="true">✦</span><h3>Your next collection starts here.</h3><p>No styles match these filters yet. Clear the filters or contact {{ $shop->name }} for a private catalogue.</p></div>
                @endif
            </div>
        </section>

        @if ($categories->isNotEmpty())
            <section class="shree-section" id="stories" aria-labelledby="shree-stories-heading"><div class="shree-section-head"><div><p class="shree-kicker">Dress for every emotion</p><h2 id="shree-stories-heading">Stories in colour.</h2></div><p>From festive radiance to a free spirit, create your own edit.</p></div><div class="shree-story-grid">@foreach ($categories->take(4)->values() as $index => $category) @php($storyProduct = $products->getCollection()->get($index)) @php($storyImage = $storyProduct?->images?->first() && ! $useDressPreviewImages ? Storage::url($storyProduct->images->first()->path) : $dressFallbackImages[$index % count($dressFallbackImages)])<a class="shree-story-card" href="{{ $collectionUrl.'?category='.$category->id }}#catalogue"><img src="{{ $storyImage }}" alt="{{ $category->name }}" loading="lazy"><span><small>{{ ['FESTIVE RADIANCE', 'DAILY RHYTHM', 'GLAM SOUL', 'FREE SPIRIT'][$index] ?? 'THE EDIT' }}</small><strong>{{ $category->name }}</strong><em>Shop the mood ↗</em></span></a>@endforeach</div></section>
        @endif

        <section class="shree-callout" id="about" aria-labelledby="shree-callout-heading"><div><p class="shree-kicker">Join the SHREE sisterhood</p><h2 id="shree-callout-heading">A considered wardrobe,<br><em>made for your store.</em></h2><p>{{ $shop->name }} helps boutique buyers compare packs, minimums, dispatch timelines and colour stories before they commit.</p>@if ($whatsappUrl)<a class="shree-button shree-button-secondary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp the studio ↗</a>@elseif ($shopEmail)<a class="shree-button shree-button-secondary" href="mailto:{{ $shopEmail }}">Email the studio ↗</a>@endif</div><form class="shree-enquiry-form" method="post" action="{{ route('enquiries.store', ['type' => $shop->type, 'shop' => $shop]) }}"><h3>Start a bulk conversation.</h3>@if (session('status'))<p class="shree-form-status" role="status">{{ session('status') }}</p>@endif<label><span>Your name</span><input name="name" value="{{ old('name') }}" autocomplete="name" required></label><label><span>Email</span><input name="email" type="email" value="{{ old('email') }}" autocomplete="email"></label><label><span>Phone</span><input name="phone" value="{{ old('phone') }}" autocomplete="tel"></label><label><span>What are you looking for?</span><textarea name="message" rows="3" required>{{ old('message') }}</textarea></label><button type="submit">Send enquiry <span aria-hidden="true">→</span></button>@csrf</form></section>
    </main>

    @include('shop.partials.storefront-footer', ['shop' => $shop, 'footerTagline' => 'Contemporary ethnicwear, thoughtfully presented for every boutique edit.', 'footerCollectionLabel' => 'Ethnicwear catalogue', 'footerCollectionAnchor' => 'catalogue', 'footerContactAnchor' => 'about'])
@endsection
