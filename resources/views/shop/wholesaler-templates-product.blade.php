@extends('layouts.storefront')

@section('body_class', 'wholesaler-templates')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/wholesaler-templates/style.css') }}">
@endpush

@section('content')
    @php
        $settings = $shop->settings;
        $availableThemes = ['heritage', 'ruby', 'indigo'];
        $availableFonts = ['editorial', 'classic', 'modern'];
        $colourKey = in_array($settings?->colour_key, $availableThemes, true) ? $settings->colour_key : 'heritage';
        $fontKey = in_array($settings?->font_key, $availableFonts, true) ? $settings->font_key : 'editorial';
        $assetBase = asset('templates/wholesaler-templates');
        $shopUrl = $shop->publicUrl();
        $productUrl = $shop->productUrl($product);
        $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/models/look-3.jpg';
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $hasWhatsapp = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1;
        $whatsappUrl = $hasWhatsapp ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Product link: '.$productUrl.'. Shop link: '.$shopUrl) : null;
        $phoneNumber = preg_replace('/\D/', '', (string) $shop->phone);
        $phoneUrl = preg_match('/^\d{8,15}$/', $phoneNumber) === 1 ? 'tel:'.$phoneNumber : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $showPrices = $settings?->show_prices ?? true;
    @endphp

    <div class="wta-shell" data-theme="{{ $colourKey }}" data-font="{{ $fontKey }}">
        <a class="wta-skip-link" href="#product-details">Skip to product details</a>

        <div class="wta-utility" aria-label="Store information">
            <span>WHOLESALE CATALOGUE</span><span>{{ $product->code }}</span><span>DIRECT BUYER ENQUIRIES</span>
        </div>

        <header class="wta-header">
            <a class="wta-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} homepage">
                @if ($shop->logo_path)
                    <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="96" height="96">
                @else
                    <span class="wta-monogram" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                @endif
                <span><strong>{{ $shop->name }}</strong><small>{{ $shop->isRetailer() ? 'RETAILER STORE' : 'WHOLESALE STUDIO' }}</small></span>
            </a>
            <nav class="wta-nav" aria-label="Primary navigation"><a href="{{ $shopUrl }}#catalogue">Catalogue</a><a href="{{ $shopUrl }}#about">About</a><a href="{{ $shopUrl }}#contact">Contact</a><a href="{{ route('market.index') }}">WholeMarket</a></nav>
            <details class="wta-mobile-menu"><summary><span class="wta-visually-hidden">Open navigation</span>Menu</summary><nav aria-label="Mobile navigation"><a href="{{ $shopUrl }}#catalogue">Catalogue</a><a href="{{ $shopUrl }}#about">About</a><a href="{{ $shopUrl }}#contact">Contact</a><a href="{{ route('market.index') }}">WholeMarket</a></nav></details>
        </header>

        <main id="product-details" class="wta-product-page">
            <nav class="wta-breadcrumb" aria-label="Breadcrumb"><a href="{{ $shopUrl }}">{{ $shop->name }}</a><span aria-hidden="true">/</span><a href="{{ $shopUrl }}#catalogue">Catalogue</a><span aria-hidden="true">/</span><span>{{ $product->code }}</span></nav>
            <section class="wta-product-layout" aria-labelledby="product-title">
                <div class="wta-product-art"><img src="{{ $productImage }}" alt="{{ $product->name }}" width="960" height="1280"></div>
                <div class="wta-product-detail">
                    <p class="wta-kicker">{{ $product->category?->name ?: 'SAREE COLLECTION' }} · {{ $product->code }}</p>
                    <h1 id="product-title">{{ $product->name }}</h1>
                    <p class="wta-lead">{{ $product->description ?: 'Request the latest availability, colour options and wholesale information directly from the seller.' }}</p>
                    <div class="wta-detail-price">{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2).' per piece' : 'Price on request' }}</div>
                    <dl class="wta-specifications">
                        <div><dt>Design code</dt><dd>{{ $product->code }}</dd></div>
                        <div><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Ask the seller' }}</dd></div>
                        <div><dt>Colour</dt><dd>{{ $product->colour ?: 'Ask the seller' }}</dd></div>
                        <div><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} {{ Str::plural('piece', $product->minimum_order_quantity) }}</dd></div>
                        @if ($product->pack_size)<div><dt>Pack size</dt><dd>{{ $product->pack_size }} {{ Str::plural('piece', $product->pack_size) }}</dd></div>@endif
                        <div><dt>Availability</dt><dd>{{ Str::headline($product->availability) }}</dd></div>
                    </dl>
                    <div class="wta-product-cta">
                        @if ($whatsappUrl)
                            <a class="wta-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Enquire on WhatsApp <span aria-hidden="true">↗</span></a>
                        @endif
                        @if ($shopEmail)
                            <a class="wta-text-link" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email the seller <span aria-hidden="true">→</span></a>
                        @endif
                        @if ($phoneUrl)
                            <a class="wta-text-link" href="{{ $phoneUrl }}">Call {{ $shop->phone }} <span aria-hidden="true">→</span></a>
                        @endif
                    </div>
                </div>
            </section>
        </main>

        <footer class="wta-footer"><a class="wta-brand" href="{{ $shopUrl }}"><span class="wta-monogram" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span><span><strong>{{ $shop->name }}</strong><small>WHOLESALE ATELIER</small></span></a><p>Independent catalogue on <a href="{{ route('market.index') }}">WholeMarket</a>.</p></footer>

        @if ($whatsappUrl)
            <a class="wta-whatsapp-float" href="{{ $whatsappUrl }}" target="_blank" rel="noopener" aria-label="Enquire with {{ $shop->name }} on WhatsApp"><span aria-hidden="true">◔</span><span>WhatsApp</span></a>
        @endif
    </div>
@endsection
