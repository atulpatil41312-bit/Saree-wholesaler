@extends('layouts.storefront')

@section('body_class', 'ivory-weaves')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/ivory-weaves/style.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('templates/ivory-weaves');
        $shopUrl = $shop->publicUrl();
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $heroImage = $shop->banner_path ? Storage::url($shop->banner_path) : $assetBase.'/hero.png';
        $categoryImages = ['hero.png', 'products.png', 'hero.png', 'products.png'];
        $productFallbackImages = ['products.png', 'hero.png', 'products.png', 'hero.png'];
        $showPrices = $shop->settings?->show_prices ?? true;
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

    <div class="iw-shell">
        @if (! empty($previewingTemplate))
            <div class="iw-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
        @endif

        <a class="skip" href="#collection">Skip to collection</a>
        <div class="iw-announcement">Sarees for your store. Conversations that matter. <span>{{ Str::upper($templateDetails['badge']) }}</span></div>

        <header class="iw-header">
            <a class="iw-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                @if ($shop->logo_path)
                    <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="52" height="52">
                @else
                    <span class="iw-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                @endif
                <span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · SAREE ATELIER</small></span>
            </a>
            <nav class="iw-nav" aria-label="Shop navigation">
                <a href="#collection">Collection</a>
                @if ($categories->isNotEmpty())<a href="#categories">Shop by style</a>@endif
                <a href="#contact">Contact</a>
                <a href="{{ route('market.index') }}">Market</a>
            </nav>
            <span class="iw-saved" aria-live="polite">♡ <b data-favourite-count>0</b> saved</span>
        </header>

        <main class="iw-main">
            <section class="iw-hero" aria-label="{{ $shop->name }} collection">
                <div class="iw-hero-copy">
                    <p class="iw-eyebrow">{{ Str::upper($shop->market_category ?: 'THE WHOLEMARKET ATELIER') }}</p>
                    <h1>{{ $templateDetails['hero_heading'] }}<br><em>{{ $templateDetails['hero_emphasis'] }}</em></h1>
                    <p>{{ $shop->description ?: 'Beautiful drapes, quiet details and considered collections for every store.' }}</p>
                    <a class="iw-primary" href="#collection">Explore the collection <span aria-hidden="true">↗</span></a>
                    <div class="iw-hero-note">CURATED COLLECTIONS &nbsp; ✦ &nbsp; DIRECT ENQUIRIES &nbsp; ✦ &nbsp; MADE FOR YOUR STORE</div>
                </div>
                <div class="iw-hero-image">
                    <img src="{{ $heroImage }}" alt="{{ $shop->name }} saree collection" width="1536" height="1024">
                    <div class="iw-hero-label">{{ Str::upper($templateDetails['badge']) }}<b>Every drape begins a new story.</b></div>
                </div>
            </section>

            <div class="iw-ribbon"><span>Thoughtful sourcing</span><span>Distinctive colours</span><span>Wholesale-ready details</span><span>Direct seller connection</span></div>

            @include('shop.partials.expanding-gallery', ['shop' => $shop, 'products' => $products, 'assetBase' => $assetBase, 'productFallbackImages' => $productFallbackImages])

            @if ($categories->isNotEmpty())
                <section id="categories" class="iw-wrap" aria-labelledby="iw-categories-heading">
                    <div class="iw-section-heading"><div><p class="iw-eyebrow">SHOP BY STYLE</p><h2 id="iw-categories-heading">Find your signature.</h2></div><p>Explore curated collections and find the pieces that fit your store.</p></div>
                    <div class="iw-category-grid">
                        @foreach ($categories->take(4)->values() as $index => $category)
                            @php
                                $categoryUrl = ! empty($previewingTemplate)
                                    ? route('seller.design.preview', ['template' => $template, 'category' => $category->id])
                                    : $shopUrl.'?category='.$category->id;
                            @endphp
                            <a class="iw-category-card" href="{{ $categoryUrl }}#collection">
                                <img src="{{ $assetBase.'/'.$categoryImages[$index % count($categoryImages)] }}" alt="{{ $category->name }} saree collection" loading="lazy" width="720" height="960">
                                <span><small>{{ Str::upper($shop->name) }}</small>{{ $category->name }} <b aria-hidden="true">↗</b></span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section id="collection" class="iw-catalogue iw-wrap" aria-labelledby="iw-collection-heading">
                <div class="iw-section-heading"><div><p class="iw-eyebrow">YOUR STORE. YOUR SELECTION.</p><h2 id="iw-collection-heading">The considered collection.</h2></div><p>{{ $products->total() }} curated {{ Str::plural('piece', $products->total()) }} · Wholesale-ready details</p></div>
                <form class="iw-filters" method="get" action="{{ $collectionUrl }}" role="search">
                    <label>Search<input type="search" name="q" value="{{ request('q') }}" placeholder="Name, colour or product code"></label>
                    <label>Collection<select name="category"><option value="">All collections</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                    <label>Fabric<select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select></label>
                    <label>Sort<select name="sort"><option value="" @selected(! request('sort'))>Newest first</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select></label>
                    <button class="iw-filter-button" type="submit">Filter collection</button>
                </form>
                <div class="iw-result-line"><span>{{ $products->total() }} {{ Str::plural('piece', $products->total()) }} in the collection</span><a href="{{ $collectionUrl }}#collection">Clear filters ×</a></div>
                <div class="iw-product-grid">
                    @forelse ($products as $product)
                        @php
                            $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$productFallbackImages[$loop->index % count($productFallbackImages)];
                            $price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry';
                        @endphp
                        <article class="iw-product">
                            <div class="iw-product-photo">
                                <a href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy" width="720" height="960"></a>
                                <button class="iw-save" type="button" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">♡</button>
                            </div>
                            <span class="iw-tag">{{ Str::upper($product->category?->name ?: 'SAREE') }}</span>
                            <h3>{{ $product->name }}</h3>
                            <div class="iw-price">{{ $price }}</div>
                            <p class="iw-meta">{{ $product->colour ?: 'Colour on enquiry' }} · {{ $product->fabric?->name ?: 'Fabric on enquiry' }} · Minimum {{ $product->minimum_order_quantity }} pieces</p>
                            @include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])
                        </article>
                    @empty
                        <div class="iw-empty">No sarees match these filters. <a href="{{ $collectionUrl }}#collection">View the full collection</a></div>
                    @endforelse
                </div>
                @if ($products->hasPages())
                    <nav class="iw-pagination" aria-label="Collection pages">
                        @if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#collection">← Previous</a>@endif
                        <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                        @if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#collection">Next →</a>@endif
                    </nav>
                @endif
            </section>

            <section class="iw-story" aria-labelledby="iw-story-heading">
                <img src="{{ $assetBase }}/hero.png" alt="{{ $shop->name }} textile story" loading="lazy" width="1536" height="1024">
                <div class="iw-story-copy"><p class="iw-eyebrow">THE STORY BEHIND THE COLLECTION</p><h2 id="iw-story-heading">Every drape begins a new story.</h2><p>{{ $shop->description ?: 'A considered place to explore graceful silhouettes, rich textures and thoughtful colours. Connect directly with the people behind the collection.' }}</p></div>
            </section>

            <section id="contact" class="iw-contact" aria-labelledby="iw-contact-heading">
                <div><p class="iw-eyebrow">LET’S TALK SAREES</p><h2 id="iw-contact-heading">Start your next collection with a conversation.</h2><p>Ask about quantities, colours and current availability from {{ $shop->name }}.</p>@if ($whatsappUrl)<a class="iw-primary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp enquiry ↗</a>@elseif ($shopEmail)<a class="iw-primary" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>@elseif ($phoneUrl)<a class="iw-primary" href="{{ $phoneUrl }}">Call the seller ↗</a>@endif</div>
                <div class="iw-contact-card"><h3>{{ $shop->name }}</h3>@if ($shop->address || $shop->shop_number)<p>{{ collect([$shop->shop_number, $shop->address])->filter()->implode(', ') }}</p>@endif @if ($shop->opening_hours)<p><strong>Opening hours</strong><br>{{ $shop->opening_hours }}</p>@endif @if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail)<div class="iw-socials" aria-label="Contact links">@include('shop.partials.contact-icons', ['shop' => $shop])</div>@endif</div>
            </section>
        </main>

        @include('shop.partials.storefront-footer', ['shop' => $shop])
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const favouriteKey = {{ Illuminate\Support\Js::from('wholemarket:'.$shop->id.':'.$template.':favourites') }};
            let saved = [];
            try { saved = JSON.parse(localStorage.getItem(favouriteKey) || '[]'); } catch { saved = []; }
            const savedProducts = new Set(Array.isArray(saved) ? saved.map(String) : []);
            const updateSaved = () => {
                document.querySelectorAll('[data-product-id]').forEach((button) => {
                    const active = savedProducts.has(button.dataset.productId);
                    button.textContent = active ? '♥' : '♡';
                    button.setAttribute('aria-pressed', String(active));
                });
                document.querySelectorAll('[data-favourite-count]').forEach((count) => { count.textContent = String(savedProducts.size); });
            };
            document.querySelectorAll('[data-product-id]').forEach((button) => button.addEventListener('click', () => {
                const id = button.dataset.productId;
                savedProducts.has(id) ? savedProducts.delete(id) : savedProducts.add(id);
                localStorage.setItem(favouriteKey, JSON.stringify([...savedProducts]));
                updateSaved();
            }));
            updateSaved();
        })();
    </script>
@endpush
