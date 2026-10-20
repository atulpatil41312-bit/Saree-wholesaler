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
        $whatsappUrl = $hasWhatsapp ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.route('shops.products.show', [$shop, $product])) : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    <div class="ve-announcement">{{ Str::upper($shop->name) }} <span>WHOLESALE COLLECTION</span></div>
    <header class="ve-header">
        <a class="ve-brand" href="{{ route('shops.show', $shop) }}"><span class="ve-emblem">V</span><span>{{ Str::upper($shop->name) }}<small>VASTRA EMERALD</small></span></a>
        <nav class="ve-nav" aria-label="Shop navigation"><a href="{{ route('shops.show', $shop) }}#collection">Back to collection</a><a href="{{ route('market.index') }}">Market</a></nav>
    </header>

    <main class="ve-main">
        <section class="ve-product-detail">
            <img src="{{ $product->images->first() ? Storage::url($product->images->first()->path) : asset('templates/vastra-emerald/saree.jpg') }}" alt="{{ $product->name }}">
            <div><div class="ve-eyebrow">{{ Str::upper($product->category?->name ?: 'SAREE COLLECTION') }} · {{ $product->code }}</div><h1>{{ $product->name }}</h1><div class="ve-price">{{ $product->wholesale_price ? '₹'.number_format($product->wholesale_price, 2).' per piece' : 'Contact for price' }}</div><p>{{ $product->description ?: 'A considered saree for a distinctive wholesale collection.' }}</p><dl><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Contact for details' }}</dd><dt>Colour</dt><dd>{{ $product->colour ?: 'Contact for details' }}</dd><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd><dt>Availability</dt><dd>{{ Str::headline($product->availability) }}</dd></dl>@if ($whatsappUrl)<a class="ve-primary" href="{{ $whatsappUrl }}">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="ve-primary" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry ↗</a>@endif</div>
        </section>
    </main>
@endsection
