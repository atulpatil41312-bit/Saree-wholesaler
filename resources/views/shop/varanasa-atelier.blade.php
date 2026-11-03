@extends('layouts.storefront')

@section('body_class', 'varanasa-atelier')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/varanasa-atelier/style.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('storefront');
        $shopUrl = $shop->publicUrl();
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $fallbackImages = ['saree.jpg', 'saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
        $heroImage = $shop->banner_path ? Storage::url($shop->banner_path) : $assetBase.'/saree.jpg';
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

    <div class="va-shell">
        @if (! empty($previewingTemplate))
            <div class="va-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
        @endif

        <div class="va-utility">
            <span>Direct seller enquiries for considered collections</span>
            <span class="va-utility-links">
                @if ($phoneUrl)<a href="{{ $phoneUrl }}">{{ $shop->phone }}</a>@endif
                @if ($shopEmail)<a href="mailto:{{ $shopEmail }}">{{ $shopEmail }}</a>@endif
                @if ($instagramUrl)<a href="{{ $instagramUrl }}" target="_blank" rel="noopener">Instagram</a>@endif
                @if ($facebookUrl)<a href="{{ $facebookUrl }}" target="_blank" rel="noopener">Facebook</a>@endif
            </span>
        </div>

        <header class="va-header">
            <div class="va-header-inner">
                <a class="va-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                    @if ($shop->logo_path)
                        <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="46" height="46">
                    @else
                        <span class="va-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                    @endif
                    <span><strong>{{ $shop->name }}</strong><small>{{ Str::upper($templateDetails['label']) }} · SAREE ATELIER</small></span>
                </a>
                <nav class="va-nav" aria-label="Main navigation">
                    <a href="#va-collections">Collections</a>
                    <a href="#va-categories">Heritage weaves</a>
                    <a href="#va-catalogue">The archive</a>
                    <a href="#va-atelier">Atelier services</a>
                    <a href="#va-contact">Contact</a>
                </nav>
                <div class="va-actions">
                    <form class="va-search" method="get" action="{{ $collectionUrl }}" role="search">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search catalogue" aria-label="Search sarees">
                        <button type="submit" aria-label="Search">⌕</button>
                    </form>
                    <a href="#va-catalogue" aria-label="Saved favourites">♡ <span data-favourite-count>0</span></a>
                    <a href="#va-contact" aria-label="Private enquiry">♢</a>
                </div>
            </div>
        </header>

        <main>
            <section id="va-collections" class="va-hero" aria-label="{{ $shop->name }} collection">
                <div class="va-hero-media"><img src="{{ $heroImage }}" alt="{{ $shop->name }} saree collection" width="2200" height="1400"></div>
                <div class="va-hero-overlay"></div>
                <div class="va-hero-copy">
                    <p class="va-eyebrow">{{ Str::upper($shop->market_category ?: 'THE MASTER GUILD COLLECTION') }}</p>
                    <h1>{{ $templateDetails['hero_heading'] }}<br><em>{{ $templateDetails['hero_emphasis'] }}</em></h1>
                    <p>{{ $shop->description ?: 'Rare weaves, considered colour and the details buyers need to choose their next collection.' }}</p>
                    <div class="va-hero-buttons"><a class="va-button" href="#va-catalogue">Explore the archive <span>↗</span></a>@if ($whatsappUrl)<a class="va-button va-button-quiet" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Book a private viewing</a>@endif</div>
                    <div class="va-trust-row"><span>✦ Pure silk details</span><span>✦ Direct seller access</span><span>✦ Wholesale-ready minimums</span></div>
                </div>
                <div class="va-hero-caption"><small>{{ Str::upper($templateDetails['badge']) }}</small><strong>{{ $shop->name }}<br>with provenance.</strong></div>
            </section>

            <section class="va-pillars" aria-label="Atelier assurances">
                <div><b>◈</b><span><strong>Purity assured</strong><small>Clear fabric and finish details</small></span></div>
                <div><b>✂</b><span><strong>Atelier finishing</strong><small>Made-to-measure conversations</small></span></div>
                <div><b>✧</b><span><strong>Worldwide enquiries</strong><small>Reach the seller directly</small></span></div>
                <div><b>◇</b><span><strong>Thoughtfully sourced</strong><small>Curated for your next edit</small></span></div>
            </section>

            @if ($categories->isNotEmpty())
                <section id="va-categories" class="va-section">
                    <div class="va-section-head"><div><p class="va-eyebrow">THE MASTER GUILDS</p><h2>Archival weaves of the collection</h2></div><p>Each chapter is shaped by the categories and products already in this shop.</p></div>
                    <div class="va-category-grid">
                        @foreach ($categories->take(4)->values() as $index => $category)
                            @php
                                $categoryProduct = $products->getCollection()->firstWhere('category_id', $category->id);
                                $categoryImage = $categoryProduct?->images?->first() ? Storage::url($categoryProduct->images->first()->path) : $assetBase.'/'.$fallbackImages[$index % count($fallbackImages)];
                                $categoryUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template, 'category' => $category->id]) : $shopUrl.'?category='.$category->id;
                            @endphp
                            <a class="va-category" href="{{ $categoryUrl }}#va-catalogue"><span class="va-category-image"><img src="{{ $categoryImage }}" alt="{{ $category->name }} sarees" loading="lazy" width="720" height="900"><i>{{ Str::upper($shop->market_category ?: 'SAREE EDIT') }}</i><strong>{{ $category->name }}</strong></span><span>{{ Str::limit($shop->description ?: 'A considered collection for your next edit.', 90) }} <b>↗</b></span></a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section id="va-catalogue" class="va-catalogue">
                <div class="va-section-head"><div><p class="va-eyebrow">COLLECTOR EDITIONS</p><h2>The imperial saree archive</h2></div><p>{{ $products->total() }} curated {{ Str::plural('piece', $products->total()) }} · Live catalogue</p></div>
                <div class="va-filterbar"><form method="get" action="{{ $collectionUrl }}" role="search"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or code"><select name="category"><option value="">All collections</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select><select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select><button type="submit">Filter</button></form><a href="{{ $collectionUrl }}#va-catalogue">Clear ×</a></div>
                @if ($products->isNotEmpty())
                    <div class="va-product-grid">
                        @foreach ($products as $product)
                            @php
                                $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$fallbackImages[$loop->index % count($fallbackImages)];
                                $price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry';
                            @endphp
                            <article class="va-product">
                                <div class="va-product-photo"><a href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy" width="720" height="960"></a><span class="va-product-badge">{{ Str::upper($product->fabric?->name ?: 'SAREE') }}</span><button type="button" class="va-save" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">♡</button></div>
                                <div class="va-product-copy"><p class="va-code">{{ $product->code }} · {{ Str::upper($product->category?->name ?: 'ARCHIVE') }}</p><h3><a href="{{ $product->publicUrl() }}">{{ $product->name }}</a></h3><p class="va-description">{{ $product->description ?: 'A considered saree selected for colour, drape and finish.' }}</p><div class="va-product-meta"><strong>{{ $price }}</strong><span>{{ $product->availability ?: 'Available' }} · Min {{ $product->minimum_order_quantity }}</span></div>@include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])</div>
                            </article>
                        @endforeach
                    </div>
                    @if ($products->hasPages())<nav class="va-pagination" aria-label="Catalogue pages">@if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#va-catalogue">← Previous</a>@endif<span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>@if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#va-catalogue">Next →</a>@endif</nav>@endif
                @else
                    <div class="va-empty">The archive is being prepared. Check back soon or contact {{ $shop->name }} for a private catalogue.</div>
                @endif
            </section>

            <section id="va-atelier" class="va-atelier">
                <div class="va-atelier-copy"><p class="va-eyebrow">MADE-TO-MEASURE COUTURE</p><h2>The blouse atelier studio</h2><p>A saree is only as magnificent as its finishing. Ask {{ $shop->name }} about blouse coordination, fall and pico, styling notes or a private curation for your store.</p><div class="va-atelier-steps"><span><b>01</b> Choose the drape</span><span><b>02</b> Refine the finish</span><span><b>03</b> Confirm the collection</span></div>@if ($whatsappUrl)<a class="va-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Book an atelier conversation ↗</a>@endif</div><div class="va-atelier-card"><p class="va-eyebrow">THE ATELIER PROMISE</p><h3>Tailored to your next edit.</h3><p>Share quantities, colours and occasion notes. The seller can guide you to the right weave and availability.</p><ul><li>Direct WhatsApp conversation</li><li>Wholesale-ready product facts</li><li>Collection guidance from the seller</li></ul></div>
            </section>

            <section class="va-concierge"><div class="va-concierge-image"><img src="{{ $assetBase.'/saree2.jpg' }}" alt="{{ $shop->name }} bridal collection" loading="lazy" width="1200" height="1000"></div><div class="va-concierge-copy"><p class="va-eyebrow">PRIVATE TROUSSEAU CONCIERGE</p><h2>A private viewing for your next celebration.</h2><p>For buyers seeking an unforgettable edit, connect directly with {{ $shop->name }} and shape a considered selection around your occasion, budget and delivery needs.</p><div class="va-checks"><span>✓ Curated collection guidance</span><span>✓ Clear availability and minimums</span><span>✓ Direct seller follow-up</span></div>@if ($whatsappUrl)<a class="va-button va-button-light" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Inquire privately ↗</a>@endif</div></section>

            <section class="va-proof"><p class="va-eyebrow">THE WHOLEMARKET STANDARD</p><h2>Quiet luxury, practical sourcing.</h2><p>{{ $shop->description ?: 'A considered storefront for browsing the collection, comparing product details and starting a direct conversation.' }}</p><div class="va-proof-grid"><span><b>01</b><strong>Live product data</strong><small>Names, images, prices and availability come from this shop.</small></span><span><b>02</b><strong>Seller connection</strong><small>WhatsApp, email and social links appear only when configured.</small></span><span><b>03</b><strong>Protected catalogue</strong><small>Subscription and storefront rules remain enforced.</small></span></div></section>

            <section id="va-contact" class="va-contact"><div><p class="va-eyebrow">PRIVATE VIEWING</p><h2>Begin a considered conversation.</h2><p>Ask about quantities, colours, prices and current availability from {{ $shop->name }}.</p>@if ($whatsappUrl)<a class="va-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp the seller ↗</a>@elseif ($shopEmail)<a class="va-button" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>@elseif ($phoneUrl)<a class="va-button" href="{{ $phoneUrl }}">Call the seller ↗</a>@endif</div><div class="va-contact-card"><h3>{{ $shop->name }}</h3>@if ($shop->shop_number || $shop->address)<p>{{ collect([$shop->shop_number, $shop->address])->filter()->implode(', ') }}</p>@endif @if ($shop->opening_hours)<p><strong>Opening hours</strong><br>{{ $shop->opening_hours }}</p>@endif @if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail)<div class="va-socials">@include('shop.partials.contact-icons', ['shop' => $shop, 'whatsappUrl' => $whatsappUrl, 'instagramUrl' => $instagramUrl, 'facebookUrl' => $facebookUrl, 'shopEmail' => $shopEmail])</div>@endif</div></section>
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
