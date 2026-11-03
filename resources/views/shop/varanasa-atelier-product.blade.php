@extends('layouts.storefront')

@section('body_class', 'varanasa-atelier')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/varanasa-atelier/style.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('storefront');
        $shopUrl = $shop->publicUrl();
        $fallbackImage = $assetBase.'/saree.jpg';
        $productImages = $product->images->map(fn ($image): string => Storage::url($image->path))->values();
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl())
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $instagramUrl = filter_var($shop->instagram_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->instagram_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->instagram_url : null;
        $facebookUrl = filter_var($shop->facebook_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->facebook_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->facebook_url : null;
    @endphp

    <div class="va-shell">
        <div class="va-utility"><span>{{ $shop->name }} · {{ Str::upper($templateDetails['badge']) }}</span><span class="va-utility-links">Direct seller enquiries available</span></div>
        <header class="va-header"><div class="va-header-inner"><a class="va-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="46" height="46">@else<span class="va-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>@endif<span><strong>{{ $shop->name }}</strong><small>{{ Str::upper($templateDetails['label']) }} · SAREE ATELIER</small></span></a><nav class="va-nav" aria-label="Product navigation"><a href="{{ $shopUrl }}#va-categories">Collections</a><a href="{{ $shopUrl }}#va-catalogue">The archive</a><a href="{{ $shopUrl }}#va-contact">Contact</a></nav><div class="va-actions"><a href="{{ $shopUrl }}#va-catalogue">Catalogue</a><a href="{{ $shopUrl }}#va-contact">Private viewing</a></div></div></header>

        <main>
            <section class="va-product-page">
                <div class="va-gallery"><div class="va-detail-photo"><img data-product-image src="{{ $productImages->first() ?: $fallbackImage }}" alt="{{ $product->name }}" width="1000" height="1300"></div>@if ($productImages->count() > 1)<div class="va-thumbs" aria-label="Product images">@foreach ($productImages as $index => $image)<button type="button" data-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="76" height="96"></button>@endforeach</div>@endif</div>
                <div class="va-detail-copy"><p class="va-eyebrow"><a href="{{ $shopUrl }}#va-catalogue">← Back to the archive</a></p><p class="va-eyebrow">{{ Str::upper($product->category?->name ?: 'SAREE COLLECTION') }} · {{ $product->code }}</p><h1>{{ $product->name }}</h1><p class="va-detail-description">{{ $product->description ?: 'A considered saree selected for colour, drape and finish.' }}</p><div class="va-detail-price">{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry' }}</div><dl class="va-detail-facts"><div><dt>Availability</dt><dd>{{ $product->availability ?: 'Available' }}</dd></div><div><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd></div><div><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Ask the seller' }}</dd></div><div><dt>Colour</dt><dd>{{ $product->colour ?: 'Ask the seller' }}</dd></div></dl>@if ($whatsappUrl)<a class="va-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp enquiry ↗</a>@elseif ($shopEmail)<a class="va-button" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>@endif<p class="va-detail-note">Wholesale quantities, dispatch timing and current availability are confirmed directly by {{ $shop->name }}.</p>@if ($whatsappUrl || $instagramUrl || $facebookUrl || $shopEmail)<div class="va-socials">@include('shop.partials.contact-icons', ['shop' => $shop, 'whatsappUrl' => $whatsappUrl, 'instagramUrl' => $instagramUrl, 'facebookUrl' => $facebookUrl, 'shopEmail' => $shopEmail])</div>@endif</div>
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
