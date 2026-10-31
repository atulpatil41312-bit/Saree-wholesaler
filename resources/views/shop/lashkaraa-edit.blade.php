@extends('layouts.storefront')

@section('body_class', 'lashkaraa-edit')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/lashkaraa-edit/style.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('storefront');
        $shopUrl = $shop->publicUrl();
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $heroImage = $shop->banner_path ? Storage::url($shop->banner_path) : asset('storefront/saree.jpg');
        $categoryImages = ['saree.jpg', 'saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
        $productFallbackImages = ['saree.jpg', 'saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
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

    <div class="le-shell">
        @if (! empty($previewingTemplate))
            <div class="le-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
        @endif

        <a class="skip" href="#collection">Skip to collection</a>
        <div class="le-promo">Free delivery on qualifying orders · Direct seller enquiries available</div>

        <header class="le-header">
            <div class="le-header-main">
                <a class="le-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                    @if ($shop->logo_path)
                        <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="46" height="46">
                    @else
                        <span class="le-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                    @endif
                    <span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · SAREE EDIT</small></span>
                </a>
                <form class="le-search" method="get" action="{{ $collectionUrl }}" role="search">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search sarees, colours or codes" aria-label="Search sarees">
                </form>
                <div class="le-tools"><a href="#contact">Account</a><a href="#saved">Saved <span aria-live="polite">(<b data-favourite-count>0</b>)</span></a><a href="{{ $shopUrl }}#collection">Bag</a></div>
            </div>
            <nav class="le-nav" aria-label="Main navigation">
                <a href="#categories">Women</a>
                <a href="#collection">New arrivals</a>
                <a href="#collection">Sarees</a>
                <a href="#categories">Collections</a>
                <a href="#story">Discover</a>
                <a href="#contact">Contact</a>
                <a href="{{ route('market.index') }}">Market</a>
            </nav>
        </header>

        <main>
            <section class="le-hero" aria-label="{{ $shop->name }} collection">
                <div class="le-hero-copy">
                    <p class="le-eyebrow">{{ Str::upper($shop->market_category ?: 'THE NEW COLLECTION') }}</p>
                    <h1>{{ $templateDetails['hero_heading'] }}<br><em>{{ $templateDetails['hero_emphasis'] }}</em></h1>
                    <p>{{ $shop->description ?: 'Discover elegant sarees, considered details and a collection made for memorable moments.' }}</p>
                    <a class="le-primary" href="#collection">Shop the collection <span aria-hidden="true">↗</span></a>
                </div>
                <div class="le-hero-image">
                    <img src="{{ $heroImage }}" alt="{{ $shop->name }} saree collection" width="1200" height="900">
                    <div class="le-hero-label">{{ Str::upper($templateDetails['badge']) }}<b>Beautifully dressed for every occasion.</b></div>
                </div>
            </section>

            @if ($categories->isNotEmpty())
                <nav id="categories" class="le-quick-nav" aria-label="Shop by collection">
                    @foreach ($categories->take(8)->values() as $category)
                        @php
                            $categoryUrl = ! empty($previewingTemplate)
                                ? route('seller.design.preview', ['template' => $template, 'category' => $category->id])
                                : $shopUrl.'?category='.$category->id;
                        @endphp
                        <a href="{{ $categoryUrl }}#collection">{{ $category->name }}</a>
                    @endforeach
                </nav>
            @endif

            @if ($categories->isNotEmpty())
                <section class="le-wrap" aria-labelledby="le-categories-heading">
                    <div class="le-section-heading"><div><p class="le-eyebrow">SHOP BY MOOD</p><h2 id="le-categories-heading">Find your next favourite.</h2></div><p>From everyday drapes to occasion-ready edits, explore the collections curated by {{ $shop->name }}.</p></div>
                    <div class="le-category-grid">
                        @foreach ($categories->take(4)->values() as $index => $category)
                            @php
                                $categoryUrl = ! empty($previewingTemplate)
                                    ? route('seller.design.preview', ['template' => $template, 'category' => $category->id])
                                    : $shopUrl.'?category='.$category->id;
                            @endphp
                            <a class="le-category-card" href="{{ $categoryUrl }}#collection">
                                <img src="{{ $assetBase.'/'.$categoryImages[$index % count($categoryImages)] }}" alt="{{ $category->name }} collection" loading="lazy" width="720" height="820">
                                <span><small>{{ Str::upper($shop->name) }}</small>{{ $category->name }} <b aria-hidden="true">↗</b></span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section id="collection" class="le-catalogue le-wrap" aria-labelledby="le-collection-heading">
                <div class="le-section-heading"><div><p class="le-eyebrow">THE COLLECTION</p><h2 id="le-collection-heading">New arrivals</h2></div><p>{{ $products->total() }} curated {{ Str::plural('piece', $products->total()) }} · Ask about current availability</p></div>
                <form class="le-filters" method="get" action="{{ $collectionUrl }}" role="search">
                    <label>Search<input type="search" name="q" value="{{ request('q') }}" placeholder="Name or product code"></label>
                    <label>Category<select name="category"><option value="">All categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                    <label>Fabric<select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select></label>
                    <label>Sort<select name="sort"><option value="" @selected(! request('sort'))>Newest first</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select></label>
                    <button class="le-filter-button" type="submit">Apply</button>
                </form>
                <div class="le-resultbar"><span>{{ $products->total() }} {{ Str::plural('piece', $products->total()) }} available</span><a href="{{ $collectionUrl }}#collection">Clear filters ×</a></div>
                <div class="le-product-grid">
                    @forelse ($products as $product)
                        @php
                            $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$productFallbackImages[$loop->index % count($productFallbackImages)];
                            $price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry';
                        @endphp
                        <article class="le-product">
                            <div class="le-product-image">
                                <a href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy" width="720" height="960"></a>
                                <button class="le-heart" type="button" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">♡</button>
                            </div>
                            <div class="le-product-info">
                                <div class="le-product-code">{{ $product->code }} · {{ Str::upper($product->category?->name ?: 'SAREE') }}</div>
                                <h3>{{ $product->name }}</h3>
                                <div class="le-price">{{ $price }}</div>
                                <p class="le-meta">{{ $product->colour ?: 'Colour on enquiry' }} · {{ $product->fabric?->name ?: 'Fabric on enquiry' }} · Minimum {{ $product->minimum_order_quantity }} pieces</p>
                                @include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])
                            </div>
                        </article>
                    @empty
                        <div class="le-empty">No sarees match these filters. <a href="{{ $collectionUrl }}#collection">View the full collection</a>.</div>
                    @endforelse
                </div>
                @if ($products->hasPages())
                    <nav class="le-pagination" aria-label="Collection pages">
                        @if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#collection">← Previous</a>@endif
                        <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                        @if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#collection">Next →</a>@endif
                    </nav>
                @endif
            </section>

            <section id="story" class="le-story" aria-labelledby="le-story-heading">
                <img src="{{ $assetBase }}/saree2.jpg" alt="{{ $shop->name }} editorial story" loading="lazy" width="1200" height="900">
                <div class="le-story-copy"><p class="le-eyebrow">THE EDITORIAL STORY</p><h2 id="le-story-heading">Details that make the occasion.</h2><p>{{ $shop->description ?: 'A thoughtfully edited collection for the moments you want to remember. Browse by mood, compare details and connect directly with the seller.' }}</p><a class="le-primary" href="#contact">Connect with the seller ↗</a></div>
            </section>

            <section class="le-newsletter" aria-labelledby="le-newsletter-heading">
                <div><p class="le-eyebrow">STAY IN THE KNOW</p><h2 id="le-newsletter-heading">New edits, thoughtfully shared.</h2></div><p>Save this shop to your favourites and return whenever you are ready to discover the next collection.</p>
            </section>

            <section id="contact" class="le-contact" aria-labelledby="le-contact-heading">
                <div><p class="le-eyebrow">LET’S CONNECT</p><h2 id="le-contact-heading">Ask about your next saree.</h2><p>Questions about quantities, colours or availability? {{ $shop->name }} makes it easy to ask.</p>@if ($whatsappUrl)<a class="le-primary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp enquiry ↗</a>@elseif ($shopEmail)<a class="le-primary" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>@elseif ($phoneUrl)<a class="le-primary" href="{{ $phoneUrl }}">Call the seller ↗</a>@endif</div>
                <div class="le-contact-card"><h3>{{ $shop->name }}</h3>@if ($shop->shop_number || $shop->address)<p>{{ collect([$shop->shop_number, $shop->address])->filter()->implode(', ') }}</p>@endif @if ($shop->opening_hours)<p><strong>Opening hours</strong><br>{{ $shop->opening_hours }}</p>@endif @if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail)<div class="le-socials" aria-label="Contact links">@include('shop.partials.contact-icons', ['shop' => $shop])</div>@endif</div>
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
