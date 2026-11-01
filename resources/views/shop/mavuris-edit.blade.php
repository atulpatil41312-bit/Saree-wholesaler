@extends('layouts.storefront')

@section('body_class', 'mavuris-edit')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/mavuris-edit/style.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('storefront');
        $shopUrl = $shop->publicUrl();
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $heroImage = $shop->banner_path ? Storage::url($shop->banner_path) : asset('templates/shared/ivory-party.webp');
        $fallbackImages = ['saree.jpg', 'saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
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
        $categoryImages = ['saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
    @endphp

    <div class="me-shell">
        @if (! empty($previewingTemplate))
            <div class="me-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
        @endif
        <a class="me-skip" href="#collection">Skip to collection</a>
        <div class="me-promo">Free delivery on qualifying wholesale orders · Direct seller enquiries available</div>
        <div class="me-contactbar">
            <div class="me-contactbar__links">
                @if ($phoneUrl)<a href="{{ $phoneUrl }}">☎ {{ $shop->phone }}</a>@endif
                @if ($shopEmail)<a href="mailto:{{ $shopEmail }}">✉ {{ $shopEmail }}</a>@endif
                @if ($instagramUrl)<a href="{{ $instagramUrl }}" target="_blank" rel="noopener">Instagram</a>@endif
                @if ($facebookUrl)<a href="{{ $facebookUrl }}" target="_blank" rel="noopener">Facebook</a>@endif
            </div>
            <span>{{ $shop->address ?: 'Wholesale saree collection · India' }}</span>
        </div>

        <header class="me-header">
            <div class="me-header-main">
                <a class="me-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                    @if ($shop->logo_path)
                        <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="46" height="46">
                    @else
                        <span class="me-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                    @endif
                    <span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · ARTISANAL SAREES</small></span>
                </a>
                <form class="me-search" method="get" action="{{ $collectionUrl }}" role="search">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products, colours or codes" aria-label="Search sarees">
                    <button type="submit" aria-label="Search">⌕</button>
                </form>
                <div class="me-tools"><a href="#contact">Account</a><a href="#saved">Saved (<b data-favourite-count>0</b>)</a><a href="#collection">Bag</a></div>
            </div>
            <nav class="me-nav" aria-label="Main navigation">
                <a href="{{ $shopUrl }}">Home</a><a href="#weaves">Shop by weave</a><a href="#fabrics">Shop by fabric</a><a href="#collection">New arrivals</a><a href="#collection">All sarees</a><a href="#story">Our story</a><a href="#contact">Contact</a>
            </nav>
        </header>

        <main class="me-main">
            <section class="me-hero" aria-label="{{ $shop->name }} collection">
                <div class="me-hero-media"><img src="{{ $heroImage }}" alt="{{ $shop->name }} saree collection" width="1800" height="950"></div>
                <div class="me-hero-copy"><p class="me-kicker">{{ Str::upper($shop->market_category ?: 'THE WHOLEMARKET COLLECTION') }}</p><h1>{{ $templateDetails['hero_heading'] }}<br><em>{{ $templateDetails['hero_emphasis'] }}</em></h1><p>{{ $shop->description ?: 'Explore considered sarees, heritage weaves and contemporary drapes selected for your store.' }}</p><a class="me-button" href="#collection">Explore the collection <span aria-hidden="true">↗</span></a><div class="me-hero-note"><span>DISCOVER</span><span>✦</span><span>WEAVES</span><span>✦</span><span>DIRECT ENQUIRIES</span></div></div>
                <div class="me-hero-caption"><small>{{ Str::upper($templateDetails['badge']) }}</small><strong>Details that make a difference.</strong></div>
            </section>

            <section class="me-intro" id="story"><h2>Where timeless elegance meets contemporary fashion.</h2><p>{{ $shop->description ?: 'Beautiful sarees, thoughtfully sourced from makers and presented with the details your buyers need.' }}</p><a class="me-outline" href="#contact">Read the collection story</a></section>

            @if ($products->isNotEmpty())
                <section id="collection" class="me-section" aria-labelledby="me-products-heading">
                    <div class="me-heading-row"><div><p class="me-eyebrow">CURATED CATALOGUE</p><h2 id="me-products-heading">Best sellers</h2></div><p>{{ $products->total() }} curated {{ Str::plural('piece', $products->total()) }} · Wholesale-ready details</p></div>
                    <div class="me-filterbar"><form method="get" action="{{ $collectionUrl }}" role="search"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search collection"><select name="category"><option value="">All categories</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select><select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select><button type="submit">Filter</button></form><a href="{{ $collectionUrl }}#collection">Clear filters ×</a></div>
                    <div class="me-products">
                        @foreach ($products as $product)
                            @php
                                $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$fallbackImages[$loop->index % count($fallbackImages)];
                                $price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry';
                            @endphp
                            <article class="me-product">
                                <div class="me-product-photo"><a href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy" width="720" height="960"></a><div class="me-product-actions"><button type="button" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">♡</button><a href="{{ $product->publicUrl() }}" aria-label="View {{ $product->name }}">↗</a></div></div>
                                <div class="me-product-info"><div class="me-product-code">{{ $product->code }} · {{ Str::upper($product->category?->name ?: 'SAREE') }}</div><h3>{{ $product->name }}</h3><div class="me-price">{{ $price }}</div><p class="me-meta">{{ $product->colour ?: 'Colour on enquiry' }} · {{ $product->fabric?->name ?: 'Fabric on enquiry' }} · Minimum {{ $product->minimum_order_quantity }} pieces</p>@include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])</div>
                            </article>
                        @endforeach
                    </div>
                    @if ($products->hasPages())<nav class="me-pagination" aria-label="Collection pages">@if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#collection">← Previous</a>@endif<span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>@if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#collection">Next →</a>@endif</nav>@endif
                </section>
            @endif

            @if ($categories->isNotEmpty())
                <section class="me-section" aria-labelledby="me-faves-heading"><div class="me-heading-row"><div><p class="me-eyebrow">EDITORIAL COLLECTIONS</p><h2 id="me-faves-heading">Top faves</h2></div><p>Find a direction for your next edit.</p></div><div class="me-category-grid">@foreach ($categories->take(3)->values() as $index => $category)@php $categoryUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template, 'category' => $category->id]) : $shopUrl.'?category='.$category->id; @endphp<a class="me-category-card" href="{{ $categoryUrl }}#collection"><img src="{{ $assetBase.'/'.$categoryImages[$index % count($categoryImages)] }}" alt="{{ $category->name }} collection" loading="lazy" width="720" height="820"><span><small>{{ Str::upper($shop->name) }}</small>{{ $category->name }} <b aria-hidden="true">↗</b></span></a>@endforeach</div></section>
            @endif

            @if ($categories->isNotEmpty())
                <section id="weaves" class="me-section"><div class="me-heading-row"><div><p class="me-eyebrow">SHOP THE EDIT</p><h2>Shop by weave</h2></div><p>Explore the textures your customers love.</p></div><div class="me-weave-grid">@foreach ($categories->take(8)->values() as $index => $category)@php $categoryUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template, 'category' => $category->id]) : $shopUrl.'?category='.$category->id; @endphp<a class="me-weave-card" href="{{ $categoryUrl }}#collection"><img src="{{ $assetBase.'/'.$fallbackImages[$index % count($fallbackImages)] }}" alt="{{ $category->name }} sarees" loading="lazy" width="720" height="820"><h3>{{ $category->name }}</h3></a>@endforeach</div></section>
            @endif

            <section class="me-editorial"><div class="me-editorial-copy"><p class="me-eyebrow">THE WHOLEMARKET EDIT</p><h2>Drapes beyond the ordinary.</h2><p>From first look to final enquiry, give every saree the space it deserves. Compare details, save favourites and connect directly with {{ $shop->name }}.</p><a class="me-button" href="#collection">Discover the edit <span aria-hidden="true">↗</span></a></div><div class="me-editorial-art"><img src="{{ $assetBase.'/saree2.jpg' }}" alt="{{ $shop->name }} editorial saree" loading="lazy" width="1200" height="1100"><span>For your next best collection.</span></div></section>

            @if ($categories->isNotEmpty())
                <section class="me-section"><div class="me-heading-row"><div><p class="me-eyebrow">ESSENTIALS</p><h2>Essential sarees for your wardrobe</h2></div><p>Everyday colour, celebration and craft.</p></div><div class="me-essential-grid">@foreach ($categories->take(3)->values() as $index => $category)@php $categoryUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template, 'category' => $category->id]) : $shopUrl.'?category='.$category->id; @endphp<a class="me-essential-card" href="{{ $categoryUrl }}#collection"><img src="{{ $assetBase.'/'.$fallbackImages[($index + 1) % count($fallbackImages)] }}" alt="{{ $category->name }} edit" loading="lazy" width="720" height="820"><h3>{{ $category->name }}</h3></a>@endforeach</div></section>
            @endif

            @if ($fabrics->isNotEmpty())
                <section id="fabrics" class="me-section"><div class="me-heading-row"><div><p class="me-eyebrow">TEXTURE & FALL</p><h2>Shop by fabric</h2></div><p>Build a collection with a considered hand-feel.</p></div><div class="me-fabric-rail">@foreach ($fabrics->take(10)->values() as $index => $fabric)<a class="me-fabric-card" href="{{ $collectionUrl.'?fabric='.$fabric->id }}#collection"><img src="{{ $assetBase.'/'.$fallbackImages[$index % count($fallbackImages)] }}" alt="{{ $fabric->name }} sarees" loading="lazy" width="520" height="620"><h3>{{ $fabric->name }}</h3></a>@endforeach</div></section>
            @endif

            <section class="me-benefits" aria-label="Shop benefits"><div class="me-benefit"><span aria-hidden="true">✦</span><div><strong>Wholesale-ready</strong><p>Clear minimums, prices and availability.</p></div></div><div class="me-benefit"><span aria-hidden="true">◌</span><div><strong>Direct connection</strong><p>Ask {{ $shop->name }} about your next edit.</p></div></div><div class="me-benefit"><span aria-hidden="true">↗</span><div><strong>Thoughtfully presented</strong><p>Every product detail in one calm place.</p></div></div></section>
            <section class="me-newsletter"><p class="me-eyebrow">STAY UP TO DATE</p><h2>New edits, thoughtfully shared.</h2><p>Save this shop and return whenever you are ready to discover the next collection.</p><a class="me-outline" href="#contact">Request collection updates</a></section>

            <section id="contact" class="me-contact"><div><p class="me-eyebrow">LET’S CONNECT</p><h2>Start your next collection with a conversation.</h2><p>Ask about quantities, colours, pricing and current availability from {{ $shop->name }}.</p>@if ($whatsappUrl)<a class="me-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp enquiry ↗</a>@elseif ($shopEmail)<a class="me-button" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>@elseif ($phoneUrl)<a class="me-button" href="{{ $phoneUrl }}">Call the seller ↗</a>@endif</div><div class="me-contact-card"><h3>{{ $shop->name }}</h3>@if ($shop->shop_number || $shop->address)<p>{{ collect([$shop->shop_number, $shop->address])->filter()->implode(', ') }}</p>@endif @if ($shop->opening_hours)<p><strong>Opening hours</strong><br>{{ $shop->opening_hours }}</p>@endif @if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail)<div class="me-contact-icons">@include('shop.partials.contact-icons', ['shop' => $shop, 'whatsappUrl' => $whatsappUrl, 'instagramUrl' => $instagramUrl, 'facebookUrl' => $facebookUrl, 'shopEmail' => $shopEmail])</div>@endif</div></section>
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
