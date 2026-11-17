@extends('layouts.storefront')

@section('body_class', 'aarohi-storefront')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/aarohi-01/style.css') }}">
@endpush

@section('content')
    @php
        $shopUrl = $shop->publicUrl();
        $fallbackImage = asset('templates/aarohi-01/product.png');
        $productImages = $product->images->map(fn ($image): string => Storage::url($image->path))->values();
        $primaryImage = $productImages->first() ?: $fallbackImage;
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl().'. Shop link: '.$shopUrl)
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    @if (! empty($previewingTemplate))
        <div class="aarohi-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
    @endif

    <div class="aarohi-utility"><span>Regal ethnicwear · wholesale ready · direct sourcing</span><span>{{ $shop->name }} · INR ₹</span></div>
    <header class="aarohi-header">
        <div class="aarohi-header-main">
            <a class="aarohi-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                <span class="aarohi-brand-mark">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="44" height="44">@else{{ Str::upper(Str::substr($shop->name, 0, 1)) }}@endif</span>
                <span><strong>{{ $shop->name }}</strong><small>{{ $templateDetails['label'] }} · wholesale edit</small></span>
            </a>
            <nav class="aarohi-nav" aria-label="Product navigation"><a href="{{ $shopUrl }}#catalogue">Catalogue</a><a href="{{ $shopUrl }}#bulk-inquiry">Bulk enquiry</a><a href="{{ $shopUrl }}#about">About</a></nav>
            <div class="aarohi-header-actions"><form class="aarohi-search" method="get" action="{{ $shopUrl }}" role="search"><input type="search" name="q" placeholder="Search styles" aria-label="Search catalogue"><button type="submit" aria-label="Search">⌕</button></form><span class="aarohi-currency">₹</span></div>
        </div>
    </header>

    <main class="aarohi-detail">
        <p class="aarohi-breadcrumb"><a href="{{ $shopUrl }}">Home</a> / <a href="{{ $shopUrl }}#catalogue">Catalogue</a> / {{ $product->name }}</p>
        <div class="aarohi-detail-grid">
            <div class="aarohi-detail-gallery">
                <div class="aarohi-detail-thumbs" aria-label="Product images">
                    @foreach ($productImages as $index => $image)
                        <button type="button" class="aarohi-detail-thumb {{ $index === 0 ? 'is-active' : '' }}" data-aarohi-gallery-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="100" height="100"></button>
                    @endforeach
                </div>
                <div class="aarohi-detail-main"><img data-aarohi-main-image src="{{ $primaryImage }}" alt="{{ $product->name }}" width="900" height="1200"></div>
            </div>
            <div class="aarohi-detail-copy">
                <p class="aarohi-kicker">{{ Str::upper($product->category?->name ?: 'ETHNICWEAR COLLECTION') }} · {{ $product->code }}</p>
                <h1>{{ $product->name }}</h1>
                <p>{{ $product->description ?: 'A considered ethnicwear style selected for colour, finish and effortless boutique merchandising. Confirm current availability and pack details with '.$shop->name.'.' }}</p>
                <div class="aarohi-detail-card">
                    <div class="aarohi-detail-price"><div><small>Wholesale rate</small><strong>{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry' }}</strong></div><span>{{ Str::headline($product->availability) }}</span></div>
                    <dl class="aarohi-specs">
                        <div><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd></div>
                        <div><dt>Pack size</dt><dd>{{ $product->pack_size ?: 'Flexible' }}</dd></div>
                        <div><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Ask the seller' }}</dd></div>
                        <div><dt>Colour</dt><dd>{{ $product->colour ?: 'Ask the seller' }}</dd></div>
                    </dl>
                </div>
                <div class="aarohi-detail-actions">
                    @if ($whatsappUrl)<a class="aarohi-button aarohi-button-primary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="aarohi-button aarohi-button-primary" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry ↗</a>@endif
                    <a class="aarohi-button aarohi-button-secondary" href="{{ $shopUrl }}#catalogue">Back to catalogue</a>
                </div>
                <p class="aarohi-detail-note"><strong>Wholesale note.</strong> Availability, pricing and pack information are live catalogue details. Confirm colour, stock and dispatch with {{ $shop->name }} before ordering.</p>
            </div>
        </div>
        <section class="aarohi-related"><p class="aarohi-kicker">AAROHI wholesale standard</p><h2>Designed for confident buying.</h2><p>Each style is presented with the material, minimum and direct contact details needed to build a considered ethnicwear edit.</p></section>
    </main>

    @include('shop.partials.storefront-footer', ['shop' => $shop, 'footerTagline' => 'Considered ethnicwear, thoughtfully presented for your next boutique edit.', 'footerCollectionLabel' => 'Ethnicwear catalogue', 'footerCollectionAnchor' => 'catalogue', 'footerContactAnchor' => 'bulk-inquiry'])
@endsection

@push('scripts')
    <script>
        (() => {
            const main = document.querySelector('[data-aarohi-main-image]');
            document.querySelectorAll('[data-aarohi-gallery-image]').forEach((button) => button.addEventListener('click', () => {
                if (main) main.src = button.dataset.aarohiGalleryImage;
                document.querySelectorAll('[data-aarohi-gallery-image]').forEach((item) => item.classList.remove('is-active'));
                button.classList.add('is-active');
            }));
        })();
    </script>
@endpush
