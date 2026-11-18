@extends('layouts.storefront')

@section('body_class', 'ethnic-plus-storefront')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/ethnic-plus-01/style.css') }}">
@endpush

@section('content')
    @php
        $shopUrl = $shop->publicUrl();
        $fallbackImage = asset('templates/ethnic-plus-01/fallback.svg');
        $productImages = $product->images->map(fn ($image): string => Storage::url($image->path))->values();
        $primaryImage = $productImages->first() ?: $fallbackImage;
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl().'. Shop link: '.$shopUrl)
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    @if (! empty($previewingTemplate))<div class="ep-preview" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>@endif
    <div class="ep-utility"><span>Imperial couture · inclusive occasionwear · direct sourcing</span><span>{{ $shop->name }} · INR ₹</span></div>
    <header class="ep-header"><div class="ep-header-main"><div class="ep-header-side ep-header-left"><span class="ep-currency">◎ INR ₹</span><form class="ep-search" method="get" action="{{ $shopUrl }}" role="search"><span aria-hidden="true">⌕</span><input type="search" name="q" placeholder="Search bridal, silk, occasionwear" aria-label="Search catalogue"></form></div><a class="ep-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home"><span class="ep-brand-mark">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="42" height="42">@else{{ Str::upper(Str::substr($shop->name, 0, 1)) }}@endif</span><span><strong>{{ $shop->name }}</strong><small>IMPERIAL COUTURE EDIT</small></span></a><div class="ep-header-side ep-header-right"><a href="{{ $shopUrl }}#trending" aria-label="Back to catalogue">♡</a>@if ($whatsappUrl)<a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" aria-label="WhatsApp the studio">◌</a>@endif<a href="{{ $shopUrl }}#contact" aria-label="Contact the shop">◎</a></div></div><nav class="ep-nav" aria-label="Product navigation"><a href="{{ $shopUrl }}#trending">Catalogue</a><a href="{{ $shopUrl }}#categories">Silhouettes</a><a href="{{ $shopUrl }}#tailor">Virtual tailor</a><a href="{{ $shopUrl }}#contact">Contact</a></nav></header>

    <main class="ep-detail"><p class="ep-breadcrumb"><a href="{{ $shopUrl }}">Home</a> / <a href="{{ $shopUrl }}#trending">Catalogue</a> / {{ $product->name }}</p><div class="ep-detail-grid"><div class="ep-detail-gallery"><div class="ep-detail-thumbs" aria-label="Product images">@foreach ($productImages as $index => $image)<button type="button" class="ep-detail-thumb {{ $index === 0 ? 'is-active' : '' }}" data-ep-gallery-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="100" height="100"></button>@endforeach</div><div class="ep-detail-main"><img data-ep-main-image src="{{ $primaryImage }}" alt="{{ $product->name }}" width="900" height="1200"></div></div><div class="ep-detail-copy"><p class="ep-kicker">{{ Str::upper($product->category?->name ?: 'IMPERIAL COUTURE') }} · {{ $product->code }}</p><h1>{{ $product->name }}</h1><p>{{ $product->description ?: 'A considered occasionwear style selected for colour, finish and confident boutique merchandising. Confirm current availability and fit details with '.$shop->name.'.' }}</p><div class="ep-detail-card"><div class="ep-detail-price"><div><small>Catalogue price</small><strong>{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry' }}</strong></div><span>{{ Str::headline($product->availability) }}</span></div><dl class="ep-specs"><div><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd></div><div><dt>Pack size</dt><dd>{{ $product->pack_size ?: 'Flexible' }}</dd></div><div><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Ask the seller' }}</dd></div><div><dt>Colour</dt><dd>{{ $product->colour ?: 'Ask the seller' }}</dd></div></dl></div><div class="ep-detail-actions">@if ($whatsappUrl)<a class="ep-button ep-button-primary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="ep-button ep-button-primary" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry ↗</a>@endif<a class="ep-button ep-button-outline" href="{{ $shopUrl }}#trending">Back to catalogue</a></div><p class="ep-detail-note"><strong>Buying note.</strong> Availability, pricing, pack and fit information are live catalogue details. Confirm colour, measurements and dispatch with {{ $shop->name }} before ordering.</p></div></div><section class="ep-detail-related"><p class="ep-kicker">THE IMPERIAL COUTURE STANDARD</p><h2>Designed for confident buying.</h2><p>Every style is presented with the material, minimum and direct contact details needed to build a considered occasionwear edit.</p></section></main>

    @include('shop.partials.storefront-footer', ['shop' => $shop, 'footerTagline' => 'Imperial occasionwear, thoughtfully presented for every boutique edit.', 'footerCollectionLabel' => 'Couture catalogue', 'footerCollectionAnchor' => 'trending', 'footerContactAnchor' => 'contact'])
@endsection

@push('scripts')
    <script>
        (() => {
            const main = document.querySelector('[data-ep-main-image]');
            document.querySelectorAll('[data-ep-gallery-image]').forEach((button) => button.addEventListener('click', () => {
                if (main) main.src = button.dataset.epGalleryImage;
                document.querySelectorAll('[data-ep-gallery-image]').forEach((item) => item.classList.remove('is-active'));
                button.classList.add('is-active');
            }));
        })();
    </script>
@endpush
