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
        $whatsappUrl = $hasWhatsapp ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl().'. Shop link: '.$shop->publicUrl()) : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $phoneNumber = preg_replace('/\D/', '', $shop->phone ?? '');
        $phoneUrl = strlen($phoneNumber) >= 8 && strlen($phoneNumber) <= 15 ? 'tel:'.$phoneNumber : null;
    @endphp

    <div class="stp-shell">
        <div class="stp-topline"><span>{{ Str::upper($shop->name) }}</span><small>WHOLESALE CATALOGUE</small></div>
        <header class="stp-header stp-product-header"><a class="stp-brand" href="{{ $shop->publicUrl() }}"><span class="stp-brand-mark">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span><span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · SAREE WHOLESALE</small></span></a><nav class="stp-nav" aria-label="Shop navigation"><a href="{{ $shop->publicUrl() }}#collection">Back to collection</a><a href="{{ route('market.index') }}">Market</a></nav></header>
        <main class="stp-main">
            <section class="stp-product-detail">
                <img src="{{ $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$templateDetails['hero_image'] }}" alt="{{ $product->name }}">
                <div><div class="stp-eyebrow">{{ Str::upper($product->category?->name ?: 'SAREE COLLECTION') }} · {{ $product->code }}</div><h1>{{ $product->name }}</h1><div class="stp-price">{{ $product->wholesale_price !== null ? '₹'.number_format($product->wholesale_price, 2).' per piece' : 'Contact for price' }}</div><p>{{ $product->description ?: 'A considered saree for a distinctive wholesale collection.' }}</p><dl><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Contact for details' }}</dd><dt>Colour</dt><dd>{{ $product->colour ?: 'Contact for details' }}</dd><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd><dt>Availability</dt><dd>{{ Str::headline($product->availability) }}</dd></dl>@if ($whatsappUrl)<a class="stp-primary" href="{{ $whatsappUrl }}">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="stp-primary" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry ↗</a>@elseif ($phoneUrl)<a class="stp-primary" href="{{ $phoneUrl }}">Call the seller ↗</a>@endif @include('shop.partials.contact-icons', ['shop' => $shop])</div>
            </section>
        </main>
        <footer class="stp-footer"><a class="stp-footer-brand" href="{{ $shop->publicUrl() }}">{{ $shop->name }}</a><small>{{ $templateDetails['label'] }} · Wholesale catalogue by {{ $shop->name }}</small></footer>
    </div>
@endsection
