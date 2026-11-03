@extends('layouts.storefront')

@section('body_class', 'haute-heritage')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/haute-heritage/style.css') }}">
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

    <div class="hh-shell">
        <header class="hh-header"><div class="hh-ribbon"><span>{{ $shop->name }} · {{ Str::upper($templateDetails['badge']) }}</span><span>Private collection viewing available</span></div><div class="hh-header-main"><a class="hh-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="42" height="42">@else<span class="hh-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>@endif<span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · SAREE ATELIER</small></span></a><div class="hh-monogram">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</div><div class="hh-tools"><a href="{{ $shopUrl }}#collection">Collection</a><a href="{{ route('market.index') }}">Market</a><a href="{{ $shopUrl }}#contact">Contact</a></div></div><nav class="hh-nav" aria-label="Product navigation"><a href="{{ $shopUrl }}">Home</a><a href="{{ $shopUrl }}#collections">Collections</a><a href="{{ $shopUrl }}#collection">Catalogue</a><a href="{{ $shopUrl }}#contact">Contact</a></nav></header>
        <main><section class="hh-product-page"><div><div class="hh-detail-photo"><img data-product-image src="{{ $productImages->first() ?: $fallbackImage }}" alt="{{ $product->name }}" width="900" height="1125"></div>@if ($productImages->count() > 1)<div class="hh-thumbs" aria-label="Product images">@foreach ($productImages as $index => $image)<button type="button" data-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="68" height="85"></button>@endforeach</div>@endif</div><div class="hh-detail-copy"><p class="hh-eyebrow"><a href="{{ $shopUrl }}#collection">← Back to catalogue</a></p><p class="hh-eyebrow">{{ Str::upper($product->category?->name ?: 'SAREE COLLECTION') }} · {{ $product->code }}</p><h1>{{ $product->name }}</h1><div class="hh-detail-price">{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2).' per piece' : 'Price on enquiry' }}</div><p>{{ $product->description ?: 'Ask '.$shop->name.' for fabric, colour and availability details.' }}</p><dl class="hh-facts"><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Contact for details' }}</dd><dt>Colour</dt><dd>{{ $product->colour ?: 'Contact for details' }}</dd><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd><dt>Availability</dt><dd>{{ Str::headline($product->availability) }}</dd></dl>@if ($whatsappUrl)<a class="hh-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Request private viewing ↗</a>@elseif ($shopEmail)<a class="hh-button" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Private viewing: '.$product->code) }}">Email the atelier ↗</a>@endif<div class="hh-detail-socials">@include('shop.partials.contact-icons', ['shop' => $shop, 'instagramUrl' => $instagramUrl, 'facebookUrl' => $facebookUrl, 'shopEmail' => $shopEmail])</div></div></section></main>
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
