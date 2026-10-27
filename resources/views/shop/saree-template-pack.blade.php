@extends('layouts.storefront')

@section('body_class', 'saree-template-pack '.$template)

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/saree-template-pack/style.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset($templateDetails['asset_path']);
        $whatsappNumber = preg_replace('/\D/', '', $shop->whatsapp ?? '');
        $hasWhatsapp = strlen($whatsappNumber) >= 8 && strlen($whatsappNumber) <= 15;
        $shopUrl = $shop->publicUrl();
        $whatsappUrl = $hasWhatsapp ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shopUrl) : null;
        $phoneNumber = preg_replace('/\D/', '', $shop->phone ?? '');
        $phoneUrl = strlen($phoneNumber) >= 8 && strlen($phoneNumber) <= 15 ? 'tel:'.$phoneNumber : null;
        $instagramUrl = filter_var($shop->instagram_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->instagram_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->instagram_url : null;
        $facebookUrl = filter_var($shop->facebook_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->facebook_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->facebook_url : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', $template) : $shopUrl;
        $heroImage = $shop->banner_path ? Storage::url($shop->banner_path) : asset('templates/shared/ivory-party.webp');
        $categoryImages = ['saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
        $productFallbackImages = ['saree0.jpg', 'saree1.jpg', 'saree2.jpg', 'saree.jpg'];
    @endphp

    @if (! empty($previewingTemplate))
        <div class="stp-preview-notice">Previewing {{ $templateDetails['label'] }}. <a href="{{ route('seller.design') }}">Return to template selection</a></div>
    @endif

    <div class="stp-shell">
        <a class="stp-skip" href="#collection">Skip to collection</a>
        <div class="stp-topline"><span>Sarees for your store. Conversations that matter.</span><small>WHOLESALE CATALOGUE</small></div>

        <header class="stp-header">
            <a class="stp-brand" href="{{ $shopUrl }}">
                @if ($shop->logo_path)
                    <img class="stp-logo" src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }}">
                @else
                    <span class="stp-brand-mark">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                @endif
                <span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · SAREE WHOLESALE</small></span>
            </a>
            <nav class="stp-nav" aria-label="Shop navigation">
                <a href="#collection">Collection</a>
                @if ($categories->isNotEmpty())
                    <a href="#edits">Shop by style</a>
                @endif
                <a href="#contact">Contact</a>
                <a href="{{ route('market.index') }}">Market</a>
            </nav>
            <span class="stp-saved" aria-live="polite">♡ <b data-favourite-count>0</b> saved</span>
        </header>

        <main class="stp-main">
            <section class="stp-hero" aria-label="{{ $shop->name }} collection">
                <div class="stp-hero-copy">
                    <div class="stp-eyebrow">{{ Str::upper($shop->market_category ?: 'THE WHOLESALE COLLECTION') }} · 01</div>
                    <h1>{{ $templateDetails['hero_heading'] }}<br><em>{{ $templateDetails['hero_emphasis'] }}</em></h1>
                    <p>{{ $shop->description ?: 'Explore a considered saree collection, compare wholesale details and connect directly with the seller.' }}</p>
                    <div class="stp-actions">
                        <a class="stp-primary" href="#collection">Explore sarees <span>↗</span></a>
                        <a class="stp-plain" href="#contact">Talk to the seller →</a>
                    </div>
                    <div class="stp-hero-caption">DISCOVER &nbsp; / &nbsp; SHORTLIST &nbsp; / &nbsp; ENQUIRE</div>
                </div>
                <div class="stp-hero-visual">
                    <img src="{{ $heroImage }}" alt="{{ $shop->name }} saree collection">
                    <div class="stp-photo-caption"><span>{{ Str::upper($templateDetails['badge']) }}</span><b>Details that make a difference.</b></div>
                </div>
                @if ($template === 'rose-atelier')
                    <div class="stp-hero-mini"><img src="{{ $assetBase }}/saree0.jpg" alt="{{ $shop->name }} edit"><span>A brighter kind of beautiful.</span></div>
                @endif
            </section>

            @if ($categories->isNotEmpty())
                <section id="edits" class="stp-edits">
                    <div class="stp-section-heading"><div><span class="stp-eyebrow">FIND YOUR NEXT FAVOURITE</span><h2>Shop the mood.</h2></div><a href="#collection" class="stp-text-link">View the collection ↗</a></div>
                    <div class="stp-edit-grid">
                        @foreach ($categories->take(3)->values() as $index => $category)
                            @php
                                $categoryUrl = ! empty($previewingTemplate)
                                    ? route('seller.design.preview', ['template' => $template, 'category' => $category->id])
                                    : $shop->publicUrl(['category' => $category->id]);
                            @endphp
                            <a class="stp-edit-card" href="{{ $categoryUrl }}#collection">
                                <img src="{{ $assetBase }}/{{ $categoryImages[$index] }}" alt="{{ $category->name }} saree collection">
                                <span><small>{{ Str::upper($shop->name) }}</small><b>{{ $category->name }}</b><i>↗</i></span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section id="collection" class="stp-collection">
                <div class="stp-section-heading">
                    <div><span class="stp-eyebrow">YOUR STORE. YOUR SELECTION.</span><h2>The saree collection.</h2></div>
                    <p>Wholesale prices · Enquire for availability</p>
                </div>
                <div class="stp-catalogue-layout">
                    <form class="stp-filters" method="get" action="{{ $collectionUrl }}">
                        <h3>Find your saree</h3>
                        <label>Search<input type="search" name="q" value="{{ request('q') }}" placeholder="Name, colour or code"></label>
                        <label>Collection<select name="category"><option value="">All collections</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                        <label>Fabric<select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select></label>
                        <label>Colour<select name="colour"><option value="">All colours</option>@foreach ($colours as $colour)<option value="{{ $colour }}" @selected(request('colour') === $colour)>{{ $colour }}</option>@endforeach</select></label>
                        <label>Min. price<input type="number" name="min_price" min="0" step="0.01" value="{{ request('min_price') }}" placeholder="₹"></label>
                        <label>Max. price<input type="number" name="max_price" min="0" step="0.01" value="{{ request('max_price') }}" placeholder="₹"></label>
                        <label>Sort<select name="sort"><option value="" @selected(! request('sort'))>Newest first</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select></label>
                        <div class="stp-filter-actions"><button class="stp-primary" type="submit">Filter collection</button><a href="{{ $collectionUrl }}#collection">Clear filters</a></div>
                    </form>
                    <div class="stp-catalogue-main">
                        <div class="stp-resultbar"><span>{{ $products->total() }} {{ Str::plural('piece', $products->total()) }} in the collection</span><span>{{ $products->currentPage() }} / {{ $products->lastPage() }}</span></div>
                        <div class="stp-products">
                            @forelse ($products as $product)
                                <article class="stp-product">
                                    <div class="stp-product-photo">
                                        <a href="{{ $product->publicUrl() }}"><img src="{{ $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$productFallbackImages[$loop->index % count($productFallbackImages)] }}" alt="{{ $product->name }}" loading="lazy"></a>
                                        <button class="stp-heart" type="button" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">♡</button>
                                        @if ($product->category)<span class="stp-badge">{{ $product->category->name }}</span>@endif
                                    </div>
                                    <div class="stp-code">{{ $product->code }}</div>
                                    <h3>{{ $product->name }}</h3>
                                    <div class="stp-price">{{ $product->wholesale_price !== null ? '₹'.number_format($product->wholesale_price, 2) : 'Contact for price' }}</div>
                                    <p class="stp-meta">{{ $product->colour ?: 'Colour on enquiry' }} · {{ $product->fabric?->name ?: 'Fabric on enquiry' }}</p>
                                    <div class="stp-minimum">Minimum order: {{ $product->minimum_order_quantity }} pieces</div>
                                    @include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])
                                </article>
                            @empty
                                <div class="stp-empty">No sarees match these filters. <a href="{{ $collectionUrl }}#collection">View the full collection</a></div>
                            @endforelse
                        </div>
                        @if ($products->hasPages())
                            <nav class="stp-pagination" aria-label="Collection pages">
                                @if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#collection">← Previous</a>@endif
                                <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                                @if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#collection">Next →</a>@endif
                            </nav>
                        @endif
                    </div>
                </div>
            </section>

            <section class="stp-sourcing">
                <div><span class="stp-eyebrow">A SIMPLER WAY TO SOURCE</span><h2>From a first look to your next collection.</h2><p>Browse at your pace, save the designs you love and start a conversation about quantities, colours and availability.</p><a class="stp-primary" href="#contact">Start an enquiry ↗</a></div>
                <ol><li><span>01</span><div><h3>Explore the collection</h3><p>Browse designs and compare current wholesale details.</p></div></li><li><span>02</span><div><h3>Make it your selection</h3><p>Save the pieces you want to revisit on this device.</p></div></li><li><span>03</span><div><h3>Connect with the seller</h3><p>Confirm product details and minimum quantities before ordering.</p></div></li></ol>
            </section>

            <section id="contact" class="stp-contact">
                <div><span class="stp-eyebrow">LET’S CONNECT</span><h2>A collection begins with a conversation.</h2><p>Questions about a design, bulk quantity or colour? {{ $shop->name }} makes it easy to ask.</p>
                    @if ($whatsappUrl)<a class="stp-primary" href="{{ $whatsappUrl }}">WhatsApp enquiry ↗</a>@elseif ($shopEmail)<a class="stp-primary" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>@elseif ($phoneUrl)<a class="stp-primary" href="{{ $phoneUrl }}">Call the seller ↗</a>@endif
                </div>
                <div class="stp-contact-card"><h3>{{ $shop->name }}</h3>@if ($shop->address)<p>{{ $shop->address }}</p>@endif
                    <dl>
                        @if ($shop->shop_number || $shop->address)<dt>Shop & market address</dt><dd>{{ $shop->shop_number }}{{ $shop->shop_number && $shop->address ? ', ' : '' }}{{ $shop->address }}</dd>@endif
                        @if ($shop->opening_hours)<dt>Business hours</dt><dd>{{ $shop->opening_hours }}</dd>@endif
                        @if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail)<dt>Connect with us</dt><dd class="stp-socials">@include('shop.partials.contact-icons', ['shop' => $shop])</dd>@endif
                    </dl>
                </div>
            </section>
        </main>

        <footer class="stp-footer"><a class="stp-footer-brand" href="{{ $shopUrl }}">{{ $shop->name }}</a><p>Curated for stores. Chosen with care.</p><small>{{ $templateDetails['label'] }} · Wholesale catalogue by {{ $shop->name }}</small></footer>
        <nav class="stp-mobile-nav" aria-label="Mobile shortcuts"><a href="{{ $shopUrl }}">⌂<span>Home</span></a><a href="#collection">▦<span>Collection</span></a><a href="#contact">✉<span>Contact</span></a></nav>
    </div>

    <script>
        const favouriteKey = {{ Illuminate\Support\Js::from('wholemarket:'.$shop->id.':'.$template.':favourites') }};
        let savedProductIds = [];
        try { savedProductIds = JSON.parse(localStorage.getItem(favouriteKey) || '[]'); } catch { savedProductIds = []; }
        const savedProducts = new Set(Array.isArray(savedProductIds) ? savedProductIds.map(String) : []);
        const updateSavedProducts = () => {
            document.querySelectorAll('[data-product-id]').forEach((button) => {
                const isSaved = savedProducts.has(button.dataset.productId);
                button.textContent = isSaved ? '♥' : '♡';
                button.setAttribute('aria-pressed', String(isSaved));
            });
            document.querySelectorAll('[data-favourite-count]').forEach((count) => count.textContent = String(savedProducts.size));
        };
        document.querySelectorAll('[data-product-id]').forEach((button) => button.addEventListener('click', () => {
            const productId = button.dataset.productId;
            savedProducts.has(productId) ? savedProducts.delete(productId) : savedProducts.add(productId);
            localStorage.setItem(favouriteKey, JSON.stringify([...savedProducts]));
            updateSavedProducts();
        }));
        updateSavedProducts();
    </script>
@endsection
