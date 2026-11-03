@extends('layouts.storefront')

@section('body_class', 'haute-heritage')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/haute-heritage/style.css') }}">
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
    @endphp

    <div class="hh-shell">
        @if (! empty($previewingTemplate))
            <div class="hh-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
        @endif
        <a class="hh-skip" href="#collection">Skip to collection</a>
        <header class="hh-header">
            <div class="hh-ribbon"><span>Private collection viewing available for <strong>{{ $shop->name }}</strong></span><span>Wholesale sourcing · Direct enquiries</span></div>
            <div class="hh-header-main">
                <a class="hh-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                    @if ($shop->logo_path)
                        <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="42" height="42">
                    @else
                        <span class="hh-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                    @endif
                    <span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · SAREE ATELIER</small></span>
                </a>
                <div class="hh-monogram">{{ Str::upper(Str::substr($shop->name, 0, 1)) }} · {{ now()->format('Y') }}</div>
                <div class="hh-tools"><a href="#collection">Collection</a><a href="#saved">Saved (<b data-favourite-count>0</b>)</a><a href="#contact">Concierge</a></div>
            </div>
            <nav class="hh-nav" aria-label="Shop navigation"><a href="{{ $shopUrl }}">Home</a><a href="#collections">Collections</a><a href="#collection">Catalogue</a><a href="#provenance">Provenance</a><a href="#atelier">Atelier</a><a href="#contact">Contact</a><a href="{{ route('market.index') }}">Market</a></nav>
        </header>

        <main class="hh-main">
            <section class="hh-hero" aria-label="{{ $shop->name }} haute heritage collection">
                <div class="hh-hero-media"><img src="{{ $heroImage }}" alt="{{ $shop->name }} heritage saree collection" width="1800" height="1100"></div>
                <div class="hh-hero-copy"><p class="hh-eyebrow">{{ Str::upper($shop->market_category ?: 'THE HAUTE HERITAGE EDIT') }} · {{ Str::upper($templateDetails['badge']) }}</p><h1>{{ $templateDetails['hero_heading'] }}<br><em>{{ $templateDetails['hero_emphasis'] }}</em></h1><p>{{ $shop->description ?: 'Rare weaves, considered colour and heirloom craftsmanship selected for discerning stores and collectors.' }}</p><div class="hh-actions"><a class="hh-button" href="#collection">Explore the collection <span aria-hidden="true">↗</span></a><a class="hh-button hh-button--ghost" href="#contact">Request private viewing</a></div><div class="hh-hero-meta"><span>Craft-led selection</span><span>Wholesale-ready details</span><span>Direct atelier connection</span></div></div>
                <div class="hh-hero-caption"><small class="hh-eyebrow">{{ Str::upper($templateDetails['badge']) }}</small><strong>A collection made for the moments that matter.</strong></div>
            </section>

            <section id="provenance" class="hh-assurance" aria-label="Collection assurances"><article><i aria-hidden="true">✦</i><div><b>Purity assured</b><p>Clear fabric, colour and availability details.</p></div></article><article><i aria-hidden="true">◈</i><div><b>Atelier connection</b><p>Speak directly with {{ $shop->name }}.</p></div></article><article><i aria-hidden="true">↗</i><div><b>Private viewing</b><p>Save a piece and enquire when ready.</p></div></article><article><i aria-hidden="true">◇</i><div><b>Heirloom intent</b><p>Presented with quiet, editorial care.</p></div></article></section>

            @if ($categories->isNotEmpty())
                <section id="collections" class="hh-section" aria-labelledby="hh-collections-heading"><div class="hh-section-heading"><div><p class="hh-eyebrow">THE MASTER GUILDS</p><h2 id="hh-collections-heading">Archival weaves for the modern wardrobe.</h2></div><p>Explore the collections curated by {{ $shop->name }}. Each category is a doorway into colour, craft and occasion.</p></div><div class="hh-category-grid">@foreach ($categories->take(4)->values() as $index => $category)@php $categoryUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template, 'category' => $category->id]) : $shopUrl.'?category='.$category->id; @endphp<a class="hh-category-card" href="{{ $categoryUrl }}#collection"><div class="hh-category-media"><img src="{{ $assetBase.'/'.$fallbackImages[$index % count($fallbackImages)] }}" alt="{{ $category->name }} collection" loading="lazy" width="720" height="960"><div class="hh-category-copy"><small>{{ Str::upper($shop->name) }}</small><strong>{{ $category->name }}</strong></div></div><div class="hh-category-body"><p>Considered pieces with wholesale-ready details for your next edit.</p><span class="hh-ghost-link">Explore chapter ↗</span></div></a>@endforeach</div></section>
            @endif

            <section id="collection" class="hh-section hh-catalogue" aria-labelledby="hh-catalogue-heading"><div class="hh-section-heading"><div><p class="hh-eyebrow">THE PRIVATE CATALOGUE</p><h2 id="hh-catalogue-heading">Curated sarees.</h2></div><p>{{ $products->total() }} curated {{ Str::plural('piece', $products->total()) }} · Ask about current availability</p></div><div class="hh-filterbar"><form method="get" action="{{ $collectionUrl }}" role="search"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search the archive" aria-label="Search sarees"><select name="category"><option value="">All collections</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select><select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((string) request('fabric') === (string) $fabric->id)>{{ $fabric->name }}</option>@endforeach</select><button type="submit">Filter archive</button></form><a href="{{ $collectionUrl }}#collection">Clear filters ×</a></div>
                <div class="hh-products">@forelse ($products as $product)@php $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$fallbackImages[$loop->index % count($fallbackImages)]; $price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry'; @endphp<article class="hh-product"><div class="hh-product-photo"><a href="{{ $product->publicUrl() }}"><img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy" width="720" height="900"></a><button class="hh-quick-view" type="button" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">Save for private viewing ♡</button></div><div class="hh-product-info"><small>{{ Str::upper($product->category?->name ?: 'SAREE') }} · {{ $product->code }}</small><h3>{{ $product->name }}</h3><div class="hh-price">{{ $price }}</div><p class="hh-meta">{{ $product->fabric?->name ?: 'Fabric on enquiry' }} · {{ $product->colour ?: 'Colour on enquiry' }} · Minimum {{ $product->minimum_order_quantity }} pieces</p>@include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])</div></article>@empty<div class="hh-empty">No pieces match the current archive filters. <a href="{{ $collectionUrl }}#collection">View the complete collection</a>.</div>@endforelse</div>@if ($products->hasPages())<nav class="hh-pagination" aria-label="Catalogue pages">@if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#collection">← Previous</a>@endif<span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>@if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#collection">Next →</a>@endif</nav>@endif
            </section>

            <section id="atelier" class="hh-editorial"><div><p class="hh-eyebrow">BESPOKE ATELIER NOTES</p><h2>The story lives in the details.</h2><p>From the first warp to the final drape, ask {{ $shop->name }} about fabric, colour, minimums and availability. Your buyers can move from editorial inspiration to a private enquiry in one calm step.</p><a class="hh-button" href="#contact">Connect with the atelier ↗</a></div><div class="hh-editorial-art"><img src="{{ $assetBase.'/saree2.jpg' }}" alt="{{ $shop->name }} editorial saree story" loading="lazy" width="1200" height="1250"><span>For collections with provenance.</span></div></section>
            <section class="hh-quote"><p>“A saree is not simply worn. It is remembered.”</p><small>{{ Str::upper($shop->name) }} · {{ Str::upper($templateDetails['label']) }}</small></section>
            <section id="contact" class="hh-contact"><div><p class="hh-eyebrow">PRIVATE VIEWING</p><h2>Begin a considered conversation.</h2><p>Ask about quantities, colours, pricing or availability from {{ $shop->name }}. We will help you find the right chapter for your store.</p>@if ($whatsappUrl)<a class="hh-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp enquiry ↗</a>@elseif ($shopEmail)<a class="hh-button" href="mailto:{{ $shopEmail }}">Email the atelier ↗</a>@elseif ($phoneUrl)<a class="hh-button" href="{{ $phoneUrl }}">Call the atelier ↗</a>@endif</div><div class="hh-contact-card"><h3>{{ $shop->name }}</h3>@if ($shop->shop_number || $shop->address)<p>{{ collect([$shop->shop_number, $shop->address])->filter()->implode(', ') }}</p>@endif @if ($shop->opening_hours)<p><strong>Opening hours</strong><br>{{ $shop->opening_hours }}</p>@endif @if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail)<div class="hh-socials">@include('shop.partials.contact-icons', ['shop' => $shop, 'whatsappUrl' => $whatsappUrl, 'instagramUrl' => $instagramUrl, 'facebookUrl' => $facebookUrl, 'shopEmail' => $shopEmail])</div>@endif</div></section>
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
                    button.textContent = active ? 'Saved for private viewing ♥' : 'Save for private viewing ♡';
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
