@extends('layouts.storefront')

@section('body_class', 'vastra-emerald')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/vastra-emerald/style.css') }}">
    <link rel="stylesheet" href="{{ asset('templates/vastra-emerald/overrides.css') }}">
@endpush

@section('content')
    @php
        $whatsappNumber = preg_replace('/\D/', '', $shop->whatsapp ?? '');
        $hasWhatsapp = strlen($whatsappNumber) >= 8 && strlen($whatsappNumber) <= 15;
        $shopUrl = $shop->publicUrl();
        $whatsappUrl = $hasWhatsapp ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shopUrl) : null;
        $instagramUrl = filter_var($shop->instagram_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->instagram_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->instagram_url : null;
        $facebookUrl = filter_var($shop->facebook_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->facebook_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->facebook_url : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    @if (! empty($previewingTemplate))
        <div class="ve-preview-notice">Previewing Vastra Emerald. <a href="{{ route('seller.design') }}">Return to template selection</a></div>
    @endif

    <div class="ve-announcement">For the stores that celebrate beautiful Indian textiles. <span>WHOLESALE COLLECTION</span></div>
    <header class="ve-header">
        <a class="ve-brand" href="{{ $shopUrl }}">
            <span class="ve-emblem">V</span>
            <span>{{ Str::upper($shop->name) }}<small>VASTRA EMERALD</small></span>
        </a>
        <nav class="ve-nav" aria-label="Shop navigation">
            <a href="#collection">Collection</a>
            <a href="#edits">Shop by style</a>
            <a href="#contact">Contact</a>
            <a href="{{ route('market.index') }}">Market</a>
        </nav>
        <div class="ve-tools"><span aria-live="polite"><b data-favourite-count>0</b> saved</span></div>
    </header>

    <main class="ve-main">
        <section class="ve-hero">
            <div class="ve-hero-copy">
                <div class="ve-eyebrow">THE WHOLESALE ATELIER / {{ Str::upper($shop->market_category ?: 'COLLECTION') }}</div>
                <h1>{{ $shop->name }}<br><em>in timeless style.</em></h1>
                <p>{{ $shop->description ?: 'Beautiful sarees. Considered collections. Discover distinctive pieces for your store and connect directly with the people behind them.' }}</p>
                <a class="ve-primary" href="#collection">Discover the edit <span>↗</span></a>
                <a class="ve-text-link" href="#contact">Connect with {{ $shop->name }} ↗</a>
                <div class="ve-hero-note">CURATED COLLECTIONS <i>✧</i> DIRECT ENQUIRIES <i>✧</i> BULK SOURCING</div>
            </div>
            <div class="ve-hero-image">
                <span class="ve-editorial-number">01 — THE EMERALD EDIT</span>
                <img src="{{ $shop->banner_path ? Storage::url($shop->banner_path) : asset('templates/vastra-emerald/saree.jpg') }}" alt="{{ $shop->name }} collection">
                <div class="ve-image-label">THE {{ Str::upper($shop->name) }} EDIT <span>For your next best collection.</span></div>
            </div>
        </section>

        <div class="ve-ribbon">TIMELESS WEAVES <i>✳</i> DISTINCTIVE COLOURS <i>✳</i> THOUGHTFUL SOURCING <i>✳</i> MADE FOR YOUR STORE</div>

        <section class="ve-benefits" aria-label="Wholesale benefits">
            <div><span>✧</span><b>Wholesale, simplified</b><small>Prices and quantities upfront</small></div>
            <div><span>◇</span><b>A considered collection</b><small>Filter by category or fabric</small></div>
            <div><span>↗</span><b>A personal connection</b><small>Enquire directly with the seller</small></div>
        </section>

        @if ($categories->isNotEmpty())
            <section id="edits" class="ve-edits">
                <div class="ve-section-heading"><div class="ve-eyebrow">FIND YOUR INSPIRATION</div><h2>Find your signature.</h2></div>
                <div class="ve-category-grid">
                    @foreach ($categories->take(3)->values() as $index => $category)
                        <a class="ve-category-tile" href="{{ $shop->publicUrl(['category' => $category->id]) }}#collection">
                            <img src="{{ asset('templates/vastra-emerald/saree'.$index.'.jpg') }}" alt="{{ $category->name }} saree collection">
                            <span><small>{{ Str::upper($shop->name) }}</small><b>{{ $category->name }}</b><i>↗</i></span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="collection" class="ve-collection">
            <div class="ve-collection-heading">
                <div><div class="ve-eyebrow">CURATED FOR YOUR NEXT COLLECTION</div><h2>The considered collection.</h2></div>
                <p>Thoughtfully selected. Beautifully presented.<br>Find the next favourite for your store.</p>
            </div>
            <form class="ve-controls" method="get" action="{{ $shopUrl }}">
                <label>Category<select name="category"><option value="">All sarees</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                <label>Fabric<select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected(request('fabric') == $fabric->id)>{{ $fabric->name }}</option>@endforeach</select></label>
                <label class="ve-search">⌕ <input name="q" value="{{ request('q') }}" placeholder="Search sarees or product code" aria-label="Search sarees"></label>
                <button class="ve-filter-button">Filter collection</button>
            </form>
            <div class="ve-result-line"><span>{{ $products->count() }} {{ Str::plural('piece', $products->count()) }} in the collection</span><a href="{{ $shopUrl }}#collection">Clear filters ×</a></div>
            <div class="ve-products">
                @forelse ($products as $product)
                    <article class="ve-product">
                        <div class="ve-product-image">
                            <img src="{{ $product->images->first() ? Storage::url($product->images->first()->path) : asset('templates/vastra-emerald/saree1.jpg') }}" alt="{{ $product->name }}">
                            <button class="ve-save" type="button" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">♡</button>
                            @if ($product->category)<span class="ve-tag">{{ Str::upper($product->category->name) }}</span>@endif
                        </div>
                        <div class="ve-product-code">{{ $product->code }}</div>
                        <h3>{{ $product->name }}</h3>
                        <div class="ve-price">{{ $product->wholesale_price ? '₹'.number_format($product->wholesale_price, 2) : 'Contact for price' }}</div>
                        <div class="ve-moq">Minimum order: {{ $product->minimum_order_quantity }} pieces</div>
                        <a class="ve-details" href="{{ $product->publicUrl() }}">View saree <span>↗</span></a>
                    </article>
                @empty
                    <div class="ve-empty">No sarees match these filters. <a href="{{ $shopUrl }}#collection">View the full collection</a></div>
                @endforelse
            </div>
        </section>

        <section class="ve-story">
            <div class="ve-story-art"><strong>V</strong><span>{{ Str::upper($shop->name) }}<small>A WORLD OF WEAVES</small></span></div>
            <div><div class="ve-eyebrow">BEHIND THE COLLECTION</div><h2>A thoughtful way to source beautiful sarees.</h2><p>A dedicated place for retailers to explore designs, compare details and connect directly with a wholesaler.</p><a href="#contact" class="ve-text-link">Let’s connect ↗</a></div>
        </section>

        <section id="contact" class="ve-contact">
            <div>
                <div class="ve-eyebrow">WE’RE HERE TO HELP</div>
                <h2>Let’s talk sarees.</h2>
                <p>Ask about bulk quantities, available colours and the collection that is right for your store.</p>
                @if ($whatsappUrl)
                    <a class="ve-primary" href="{{ $whatsappUrl }}">WhatsApp enquiry ↗</a>
                @elseif ($shopEmail)
                    <a class="ve-primary" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>
                @elseif ($shop->phone)
                    <a class="ve-primary" href="tel:{{ preg_replace('/\D/', '', $shop->phone) }}">Call the seller ↗</a>
                @endif
            </div>
            <div class="ve-contact-info">
                <h3>{{ $shop->name }}</h3>
                @if ($shop->address)
                    <p>{{ $shop->address }}</p>
                @endif
                <dl>
                    @if ($shop->shop_number)
                        <dt>Shop & market address</dt>
                        <dd>{{ $shop->shop_number }}{{ $shop->address ? ', '.$shop->address : '' }}</dd>
                    @endif
                    @if ($shop->opening_hours)
                        <dt>Business hours</dt>
                        <dd>{{ $shop->opening_hours }}</dd>
                    @endif
                    @if ($hasWhatsapp || $instagramUrl || $facebookUrl || $shopEmail)
                        <dt>Connect with us</dt>
                        <dd class="ve-socials">
                            @if ($whatsappUrl)
                                <a href="{{ $whatsappUrl }}">WhatsApp</a>
                            @endif
                            @if ($instagramUrl)
                                <a href="{{ $instagramUrl }}" rel="noopener noreferrer">Instagram</a>
                            @endif
                            @if ($facebookUrl)
                                <a href="{{ $facebookUrl }}" rel="noopener noreferrer">Facebook</a>
                            @endif
                            @if ($shopEmail)
                                <a href="mailto:{{ $shopEmail }}">Email</a>
                            @endif
                        </dd>
                    @endif
                </dl>
            </div>
        </section>
    </main>

    <footer class="ve-footer"><a class="ve-brand" href="{{ $shopUrl }}"><span class="ve-emblem">V</span><span>{{ Str::upper($shop->name) }}<small>VASTRA EMERALD</small></span></a><p>Beautiful collections. Meaningful connections.</p><small>Wholesale catalogue by {{ $shop->name }}</small></footer>

    <script>
        const favouriteKey = 'wholemarket:{{ $shop->id }}:vastra-emerald:favourites';
        const savedProductIds = new Set(JSON.parse(localStorage.getItem(favouriteKey) || '[]'));
        const updateSavedProducts = () => {
            document.querySelectorAll('[data-product-id]').forEach((button) => {
                const isSaved = savedProductIds.has(button.dataset.productId);
                button.textContent = isSaved ? '♥' : '♡';
                button.setAttribute('aria-pressed', isSaved);
            });
            document.querySelectorAll('[data-favourite-count]').forEach((count) => count.textContent = savedProductIds.size);
        };

        document.querySelectorAll('[data-product-id]').forEach((button) => button.addEventListener('click', () => {
            const productId = button.dataset.productId;
            savedProductIds.has(productId) ? savedProductIds.delete(productId) : savedProductIds.add(productId);
            localStorage.setItem(favouriteKey, JSON.stringify([...savedProductIds]));
            updateSavedProducts();
        }));

        updateSavedProducts();
    </script>
@endsection
