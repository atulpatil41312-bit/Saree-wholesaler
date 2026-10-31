@extends('layouts.storefront')

@section('body_class', 'lashkaraa-edit')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/lashkaraa-edit/style.css') }}">
@endpush

@section('content')
    @php
        $shopUrl = $shop->publicUrl();
        $fallbackImage = asset('storefront/saree.jpg');
        $productImages = $product->images->map(fn ($image): string => Storage::url($image->path))->values();
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl().'. Shop link: '.$shopUrl)
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    <div class="le-shell">
        <div class="le-promo">Free delivery on qualifying orders · Direct seller enquiries available</div>
        <header class="le-header">
            <div class="le-header-main">
                <a class="le-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                    @if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="46" height="46">@else<span class="le-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>@endif
                    <span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · SAREE EDIT</small></span>
                </a>
                <form class="le-search" method="get" action="{{ $shopUrl }}" role="search"><span aria-hidden="true">⌕</span><input type="search" name="q" placeholder="Search sarees, colours or codes" aria-label="Search sarees"></form>
                <div class="le-tools"><a href="{{ $shopUrl }}#contact">Account</a><a href="{{ $shopUrl }}#collection">Bag</a></div>
            </div>
            <nav class="le-nav" aria-label="Main navigation"><a href="{{ $shopUrl }}#categories">Women</a><a href="{{ $shopUrl }}#collection">New arrivals</a><a href="{{ $shopUrl }}#collection">Sarees</a><a href="{{ $shopUrl }}#story">Discover</a><a href="{{ route('market.index') }}">Market</a></nav>
        </header>

        <main>
            <div class="le-product-detail">
                <div>
                    <div class="le-detail-photo"><img data-product-image src="{{ $productImages->first() ?: $fallbackImage }}" alt="{{ $product->name }}" width="720" height="960"></div>
                    @if ($productImages->count() > 1)
                        <div class="le-thumbs" aria-label="Product images">
                            @foreach ($productImages as $index => $image)
                                <button type="button" data-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="70" height="90"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="le-detail-copy">
                    <p class="le-eyebrow"><a href="{{ $shopUrl }}#collection">← Back to collection</a></p>
                    <p class="le-eyebrow">{{ Str::upper($product->category?->name ?: 'SAREE COLLECTION') }} · {{ $product->code }}</p>
                    <h1>{{ $product->name }}</h1>
                    <div class="le-detail-price">{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2).' per piece' : 'Price on enquiry' }}</div>
                    <p>{{ $product->description ?: 'Ask '.$shop->name.' for fabric, colour and availability details.' }}</p>
                    <dl class="le-detail-facts"><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Contact for details' }}</dd><dt>Colour</dt><dd>{{ $product->colour ?: 'Contact for details' }}</dd><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd><dt>Availability</dt><dd>{{ Str::headline($product->availability) }}</dd></dl>
                    @if ($whatsappUrl)<a class="le-primary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="le-primary" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry ↗</a>@endif
                    <div class="le-detail-socials">@include('shop.partials.contact-icons', ['shop' => $shop])</div>
                </div>
            </div>
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
