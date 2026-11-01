@extends('layouts.storefront')

@section('body_class', 'mavuris-edit')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/mavuris-edit/style.css') }}">
@endpush

@section('content')
    @php
        $shopUrl = $shop->publicUrl();
        $assetBase = asset('storefront');
        $fallbackImage = $assetBase.'/saree.jpg';
        $productImages = $product->images->map(fn ($image): string => Storage::url($image->path))->values();
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl().'. Shop link: '.$shopUrl)
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $instagramUrl = filter_var($shop->instagram_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->instagram_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->instagram_url : null;
        $facebookUrl = filter_var($shop->facebook_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->facebook_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->facebook_url : null;
    @endphp

    <div class="me-shell">
        <div class="me-promo">{{ $shop->name }} · {{ Str::upper($templateDetails['badge']) }} · Direct seller enquiries available</div>
        <header class="me-header">
            <div class="me-header-main">
                <a class="me-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="46" height="46">@else<span class="me-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>@endif<span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · ARTISANAL SAREES</small></span></a>
                <div></div><div class="me-tools"><a href="{{ $shopUrl }}#collection">Collection</a><a href="{{ route('market.index') }}">Market</a><a href="{{ $shopUrl }}#contact">Contact</a></div>
            </div>
            <nav class="me-nav" aria-label="Product navigation"><a href="{{ $shopUrl }}">Home</a><a href="{{ $shopUrl }}#collection">All sarees</a><a href="{{ $shopUrl }}#weaves">Shop by weave</a><a href="{{ $shopUrl }}#fabrics">Shop by fabric</a></nav>
        </header>

        <main>
            <section class="me-product-page">
                <div>
                    <div class="me-detail-photo"><img data-product-image src="{{ $productImages->first() ?: $fallbackImage }}" alt="{{ $product->name }}" width="900" height="1200"></div>
                    @if ($productImages->count() > 1)
                        <div class="me-thumbs" aria-label="Product images">@foreach ($productImages as $index => $image)<button type="button" data-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="68" height="90"></button>@endforeach</div>
                    @endif
                </div>
                <div class="me-detail-copy"><p class="me-eyebrow"><a href="{{ $shopUrl }}#collection">← Back to collection</a></p><p class="me-eyebrow">{{ Str::upper($product->category?->name ?: 'SAREE COLLECTION') }} · {{ $product->code }}</p><h1>{{ $product->name }}</h1><div class="me-detail-price">{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2).' per piece' : 'Price on enquiry' }}</div><p>{{ $product->description ?: 'Ask '.$shop->name.' for fabric, colour and availability details.' }}</p><dl class="me-detail-facts"><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Contact for details' }}</dd><dt>Colour</dt><dd>{{ $product->colour ?: 'Contact for details' }}</dd><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd><dt>Availability</dt><dd>{{ Str::headline($product->availability) }}</dd></dl>@if ($whatsappUrl)<a class="me-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="me-button" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry ↗</a>@endif<div class="me-detail-socials">@include('shop.partials.contact-icons', ['shop' => $shop, 'instagramUrl' => $instagramUrl, 'facebookUrl' => $facebookUrl, 'shopEmail' => $shopEmail])</div></div>
            </section>
        </main>
        @include('shop.partials.storefront-footer', ['shop' => $shop])
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-image]').forEach((button) => button.addEventListener('click', () => {
            const image = document.querySelector('[data-product-image]');
            if (image) image.src = button.dataset.image;
        }));
    </script>
@endpush
