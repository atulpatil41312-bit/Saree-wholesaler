@extends('layouts.storefront')

@section('body_class', 'aarohi-storefront')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/aarohi-01/style.css') }}">
@endpush

@section('content')
    @php
        $shopUrl = $shop->publicUrl();
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $dressFallbackImages = collect(range(1, 8))->map(fn (int $index): string => asset('templates/dress-models/dress-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).'.jpg'))->all();
        $fallbackImage = $dressFallbackImages[0];
        $useDressPreviewImages = ! empty($previewingTemplate);
        $bannerImage = filled($shop->banner_path) && Storage::disk('public')->exists($shop->banner_path)
            ? Storage::url($shop->banner_path)
            : null;
        $heroImage = $bannerImage ?: $fallbackImage;
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shopUrl)
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    @if (! empty($previewingTemplate))
        <div class="aarohi-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
    @endif

    <div class="aarohi-utility">
        <span>Regal ethnicwear · wholesale ready · direct sourcing</span>
        <span>{{ $shop->name }} · INR ₹</span>
    </div>

    <header class="aarohi-header">
        <div class="aarohi-header-main">
            <a class="aarohi-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                <span class="aarohi-brand-mark">
                    @if ($shop->logo_path)
                        <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="44" height="44">
                    @else
                        {{ Str::upper(Str::substr($shop->name, 0, 1)) }}
                    @endif
                </span>
                <span><strong>{{ $shop->name }}</strong><small>{{ $templateDetails['label'] }} · wholesale edit</small></span>
            </a>
            <nav class="aarohi-nav" aria-label="Main navigation">
                <a href="{{ $collectionUrl }}#categories">Collections</a>
                <a href="{{ $collectionUrl }}#catalogue">Wholesale catalogue</a>
                <a href="{{ $collectionUrl }}#bulk-inquiry">Bulk enquiry</a>
                <a href="{{ $collectionUrl }}#about">About</a>
            </nav>
            <div class="aarohi-header-actions">
                <form class="aarohi-search" method="get" action="{{ $collectionUrl }}" role="search">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search styles" aria-label="Search catalogue">
                    <button type="submit" aria-label="Search">⌕</button>
                </form>
                <span class="aarohi-currency">₹</span>
            </div>
        </div>
    </header>

    <main class="aarohi-main">
        <section class="aarohi-hero" aria-labelledby="aarohi-hero-heading">
            <div class="aarohi-hero-copy">
                <p class="aarohi-kicker">AAROHI ETHNIC FASHION BOUTIQUE</p>
                <h1 id="aarohi-hero-heading">Curated ethnic<br><em>wholesale style.</em></h1>
                <p>{{ $shop->description ?: 'Discover considered dresses, kurta sets and occasionwear designed for modern Indian boutiques. Clear packs, live availability and direct seller access.' }}</p>
                <div class="aarohi-actions">
                    <a class="aarohi-button aarohi-button-primary" href="{{ $collectionUrl }}#catalogue">Explore catalogue <span aria-hidden="true">↗</span></a>
                    @if ($whatsappUrl)
                        <a class="aarohi-button aarohi-button-secondary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Talk to the seller <span aria-hidden="true">↗</span></a>
                    @endif
                </div>
                <div class="aarohi-hero-stats">
                    <div><strong>{{ $products->total() }}</strong><span>live styles</span></div>
                    <div><strong>{{ $categories->count() }}</strong><span>collections</span></div>
                    <div><strong>Pack</strong><span>wholesale ready</span></div>
                </div>
            </div>
            <div class="aarohi-hero-media">
                <img src="{{ $heroImage }}" alt="{{ $shop->name }} ethnicwear collection" width="1200" height="900" fetchpriority="high" decoding="async" onerror="this.onerror=null;this.src='{{ $fallbackImage }}';">
                <div class="aarohi-hero-caption"><small>THE AAROHI EDIT</small><strong>Details that make a difference.</strong></div>
            </div>
        </section>

        <section class="aarohi-proof-strip" aria-label="Wholesale benefits">
            <div><span>01</span><strong>Pack clarity</strong><small>MOQ and pack details on every style</small></div>
            <div><span>02</span><strong>Live catalogue</strong><small>Browse your seller’s current availability</small></div>
            <div><span>03</span><strong>Direct access</strong><small>Ask the studio before you commit</small></div>
        </section>

        @if ($categories->isNotEmpty())
            <section class="aarohi-section" id="categories" aria-labelledby="aarohi-categories-heading">
                <div class="aarohi-section-head">
                    <div><p class="aarohi-kicker">Wholesale by category</p><h2 id="aarohi-categories-heading">Build your next edit.</h2></div>
                    <p>Browse the live collections already curated by {{ $shop->name }}. Every category keeps the same direct enquiry flow.</p>
                </div>
                <div class="aarohi-category-grid">
                    @foreach ($categories->take(6)->values() as $index => $category)
                        @php($categoryProduct = $products->getCollection()->firstWhere('category_id', $category->id))
                        @php($categoryImage = $categoryProduct?->images?->first() && ! $useDressPreviewImages ? Storage::url($categoryProduct->images->first()->path) : $dressFallbackImages[$index % count($dressFallbackImages)])
                        <a class="aarohi-category-card" href="{{ $collectionUrl.'?category='.$category->id }}#catalogue">
                            <img src="{{ $categoryImage }}" alt="{{ $category->name }}" loading="lazy">
                            <span><small>Collection {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</small><strong>{{ $category->name }} ↗</strong></span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="aarohi-section aarohi-section-soft" id="catalogue" aria-labelledby="aarohi-catalogue-heading">
            <div class="aarohi-section-head">
                <div><p class="aarohi-kicker">Live wholesale catalogue</p><h2 id="aarohi-catalogue-heading">Featured styles &amp; best-sellers.</h2></div>
                <p>{{ $products->total() }} {{ Str::plural('style', $products->total()) }} · Direct buyer details</p>
            </div>
            <div class="aarohi-catalogue-wrap">
                <form class="aarohi-filterbar" method="get" action="{{ $collectionUrl }}" role="search">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or code" aria-label="Search products">
                    <select name="category" aria-label="Filter by category"><option value="">All categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
                    <select name="fabric" aria-label="Filter by fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select>
                    <select name="sort" aria-label="Sort products"><option value="">Latest arrivals</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select>
                    <button type="submit">Apply filters</button>
                </form>
                @if (request()->hasAny(['q', 'category', 'fabric', 'sort']))
                    <a class="aarohi-filter-clear" href="{{ $collectionUrl }}#catalogue">Clear all filters <span aria-hidden="true">×</span></a>
                @endif

                @if ($products->isNotEmpty())
                    <div class="aarohi-product-grid">
                        @foreach ($products as $product)
                            @php($productImage = $product->images->first() && ! $useDressPreviewImages ? Storage::url($product->images->first()->path) : $dressFallbackImages[$loop->index % count($dressFallbackImages)])
                            @php($price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry')
                            <article class="aarohi-product">
                                <a class="aarohi-product-media" href="{{ $product->publicUrl() }}">
                                    <img src="{{ $productImage }}" alt="{{ $product->name }}" width="720" height="960" loading="lazy">
                                    <span>{{ Str::headline($product->availability) }}</span>
                                    <b>View style <span aria-hidden="true">↗</span></b>
                                </a>
                                <div class="aarohi-product-copy">
                                    <small>{{ $product->code }} · {{ $product->category?->name ?: 'Ethnicwear' }}</small>
                                    <h3><a href="{{ $product->publicUrl() }}">{{ $product->name }}</a></h3>
                                    <p>{{ $product->description ?: 'A considered ethnicwear style with wholesale-ready details.' }}</p>
                                    <div class="aarohi-price-row"><strong>{{ $price }}</strong><span>MOQ {{ $product->minimum_order_quantity }} · {{ $product->pack_size ?: 'Flexible pack' }}</span></div>
                                    @include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($products->hasPages())
                        <nav class="aarohi-pagination" aria-label="Catalogue pages">@if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#catalogue">← Previous</a>@endif<span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>@if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#catalogue">Next →</a>@endif</nav>
                    @endif
                @else
                    <div class="aarohi-category-empty"><span aria-hidden="true">✦</span><h3>Your next collection starts here.</h3><p>No styles match these filters yet. Clear the filters or contact {{ $shop->name }} for a private catalogue.</p></div>
                @endif
            </div>
        </section>

        <section class="aarohi-callout" id="bulk-inquiry" aria-labelledby="aarohi-inquiry-heading">
            <div id="about">
                <p class="aarohi-kicker" style="color:#ffd9d4">Institutional bulk indent</p>
                <h2 id="aarohi-inquiry-heading">Your next drop, thoughtfully sourced.</h2>
                <p>{{ $shop->name }} helps boutique buyers compare packs, minimums, dispatch timelines and colour stories before they commit. Share your requirement and the team will respond with a considered shortlist.</p>
                <div class="aarohi-actions">
                    @if ($whatsappUrl)<a class="aarohi-button aarohi-button-secondary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp the studio ↗</a>@elseif ($shopEmail)<a class="aarohi-button aarohi-button-secondary" href="mailto:{{ $shopEmail }}">Email the studio ↗</a>@endif
                </div>
            </div>
            <form class="aarohi-enquiry-form" method="post" action="{{ route('enquiries.store', ['type' => $shop->type, 'shop' => $shop]) }}">
                @csrf
                <h3>Request a bulk conversation.</h3>
                @if (session('status'))<p class="aarohi-form-status" role="status">{{ session('status') }}</p>@endif
                <label><span>Your name</span><input name="name" value="{{ old('name') }}" autocomplete="name" required></label>
                <label><span>Email</span><input name="email" type="email" value="{{ old('email') }}" autocomplete="email"></label>
                <label><span>Phone</span><input name="phone" value="{{ old('phone') }}" autocomplete="tel"></label>
                <label><span>What are you looking for?</span><textarea name="message" rows="3" required>{{ old('message') }}</textarea></label>
                <button type="submit">Send bulk enquiry <span aria-hidden="true">→</span></button>
            </form>
        </section>
    </main>

    @include('shop.partials.storefront-footer', ['shop' => $shop, 'footerTagline' => 'Considered ethnicwear, thoughtfully presented for your next boutique edit.', 'footerCollectionLabel' => 'Ethnicwear catalogue', 'footerCollectionAnchor' => 'catalogue', 'footerContactAnchor' => 'bulk-inquiry'])
@endsection
