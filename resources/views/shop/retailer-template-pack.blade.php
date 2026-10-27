@extends('layouts.storefront')

@section('body_class', 'retailer-template-pack')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/retailer-pack/storefront.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('templates/retailer-pack');
        $heroImage = $shop->banner_path ? Storage::url($shop->banner_path) : asset('templates/shared/ivory-party.webp');
        $categoryImages = ['models/look-1.jpg', 'models/look-2.jpg', 'models/look-3.jpg', 'models/hero.jpg'];
        $productFallbackImages = ['models/hero.jpg', 'models/look-1.jpg', 'models/look-2.jpg', 'models/look-3.jpg'];
        $whatsappNumber = preg_replace('/\D/', '', $shop->whatsapp ?? '');
        $hasWhatsapp = strlen($whatsappNumber) >= 8 && strlen($whatsappNumber) <= 15;
        $shopUrl = $shop->publicUrl();
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $phoneNumber = preg_replace('/\D/', '', $shop->phone ?? '');
        $phoneUrl = strlen($phoneNumber) >= 8 && strlen($phoneNumber) <= 15 ? 'tel:'.$phoneNumber : null;
        $instagramUrl = filter_var($shop->instagram_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->instagram_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->instagram_url : null;
        $facebookUrl = filter_var($shop->facebook_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->facebook_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->facebook_url : null;
        $showPrices = $shop->settings?->show_prices ?? true;
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $whatsappUrl = $hasWhatsapp ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shopUrl) : null;
    @endphp

    <div class="wm">
      <div class="{{ $template }}">
        @if (! empty($previewingTemplate))
            <div class="notice">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
        @endif
        <a class="skip" href="#main">Skip to content</a>
        <header>
            <a class="brand" href="{{ $shopUrl }}">
                @if ($shop->logo_path)
                    <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="48" height="48">
                @else
                    <span aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                @endif
                <span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · RETAILER COLLECTION</small></span>
            </a>
            <button class="menu" type="button" aria-expanded="false" aria-controls="retailer-navigation">Menu</button>
            <nav id="retailer-navigation" aria-label="Main navigation">
                <a href="#collection">Collection</a>
                @if ($categories->isNotEmpty())<a href="#categories">Categories</a>@endif
                <a href="#contact">Contact</a>
                <a href="{{ route('market.index') }}">Market</a>
            </nav>
        </header>

        <main id="main" tabindex="-1">
            <section class="hero" aria-label="{{ $shop->name }} collection">
                <div class="hero-copy">
                    <p class="eyebrow">{{ Str::upper($shop->market_category ?: 'THE RETAIL COLLECTION') }}</p>
                    <h1>{{ $templateDetails['hero_heading'] }}<br><em>{{ $templateDetails['hero_emphasis'] }}</em></h1>
                    <p>{{ $shop->description ?: "Discover considered sarees from {$shop->name} and enquire directly for availability." }}</p>
                    <a class="button" href="#collection">Explore the collection →</a>
                    <small>{{ Str::upper($templateDetails['badge']) }} · DIRECT RETAILER ENQUIRIES</small>
                </div>
                <img src="{{ $heroImage }}" alt="{{ $shop->name }} collection" width="720" height="900">
            </section>

            @if ($categories->isNotEmpty())
                <section id="categories" class="wrap" aria-labelledby="category-heading">
                    <p class="eyebrow">SHOP BY MOOD</p><h2 id="category-heading">Find your next favourite.</h2>
                    <div class="categories">
                        @foreach ($categories->take(4)->values() as $index => $category)
                            @php
                                $categoryUrl = ! empty($previewingTemplate)
                                    ? route('seller.design.preview', ['template' => $template, 'category' => $category->id])
                                    : $shopUrl.'?category='.$category->id;
                            @endphp
                            <a class="category" href="{{ $categoryUrl }}#collection">
                                <img src="{{ $assetBase.'/'.($categoryImages[$index] ?? 'models/look-1.jpg') }}" alt="{{ $category->name }} collection" loading="lazy" width="720" height="500">
                                <span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }} / {{ $category->name }} →</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section id="collection" class="wrap" aria-labelledby="collection-heading">
                <div class="section-head"><div><p class="eyebrow">{{ Str::upper($shop->name) }}</p><h2 id="collection-heading">The collection.</h2></div><p class="muted">{{ $products->total() }} {{ Str::plural('piece', $products->total()) }} · Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</p></div>
                <form class="filters" method="get" action="{{ $collectionUrl }}" role="search">
                    <label>Search<input type="search" name="q" value="{{ request('q') }}" placeholder="Name or product code"></label>
                    <label>Category<select name="category"><option value="">All categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                    <label>Fabric<select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select></label>
                    <label>Colour<select name="colour"><option value="">All colours</option>@foreach ($colours as $colour)<option value="{{ $colour }}" @selected(request('colour') === $colour)>{{ $colour }}</option>@endforeach</select></label>
                    <label>Sort<select name="sort"><option value="" @selected(! request('sort'))>Newest first</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select></label>
                    <button type="submit">Filter collection</button>
                </form>
                <div class="grid">
                    @forelse ($products as $product)
                        @php
                            $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$productFallbackImages[$loop->index % count($productFallbackImages)];
                            $price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry';
                        @endphp
                        <article class="card">
                            <a href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy" width="720" height="900"></a>
                            <span class="tag">{{ $product->category?->name ?: 'SAREE' }}</span>
                            <h3>{{ $product->name }}</h3>
                            <p>{{ $product->fabric?->name ?: 'Fabric on enquiry' }} / {{ $product->colour ?: 'Colour on enquiry' }}</p>
                            <p class="price">{{ $price }}</p>
                            <p class="muted">{{ Str::headline($product->availability) }} · Minimum {{ $product->minimum_order_quantity }} pieces</p>
                            @include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])
                        </article>
                    @empty
                        <p class="empty">No products match these filters. <a href="{{ $collectionUrl }}#collection">View the full collection</a>.</p>
                    @endforelse
                </div>
                @if ($products->hasPages())
                    <nav aria-label="Collection pages" class="section-head"><span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span><span>@if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#collection">← Previous</a>@endif @if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#collection">Next →</a>@endif</span></nav>
                @endif
            </section>

            <section id="contact" class="contact wrap" aria-labelledby="contact-heading">
                <div><p class="eyebrow">LET’S TALK</p><h2 id="contact-heading">A collection begins with a conversation.</h2><p>Ask about quantities, colours and current availability from {{ $shop->name }}.</p>@if ($whatsappUrl)<a class="button" href="{{ $whatsappUrl }}">WhatsApp the retailer ↗</a>@elseif ($shopEmail)<a class="button" href="mailto:{{ $shopEmail }}">Email the retailer ↗</a>@elseif ($phoneUrl)<a class="button" href="{{ $phoneUrl }}">Call the retailer ↗</a>@endif</div>
                <div class="panel"><h3>{{ $shop->name }}</h3>@if ($shop->address)<p>{{ $shop->address }}</p>@endif @if ($shop->shop_number)<p>{{ $shop->shop_number }}</p>@endif @if ($shop->opening_hours)<p><strong>Opening hours</strong><br>{{ $shop->opening_hours }}</p>@endif @if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail)<div aria-label="Contact links">@include('shop.partials.contact-icons', ['shop' => $shop])</div>@endif</div>
            </section>
        </main>

        <footer><span>{{ $shop->name }} · {{ $templateDetails['label'] }}</span><nav aria-label="Footer"><a href="#collection">Collection</a><a href="#contact">Contact</a><a href="{{ route('market.index') }}">WholeMarket</a></nav></footer>
      </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const menu = document.querySelector('.retailer-template-pack .menu');
            const nav = document.querySelector('.retailer-template-pack header nav');
            if (!menu || !nav) return;
            menu.addEventListener('click', () => { const open = nav.classList.toggle('open'); menu.setAttribute('aria-expanded', String(open)); });
        })();
    </script>
@endpush
