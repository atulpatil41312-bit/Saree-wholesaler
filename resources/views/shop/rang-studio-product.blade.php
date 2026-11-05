@extends('layouts.storefront')

@section('body_class', 'rang-studio')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/rang-studio/style.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('storefront');
        $shopUrl = $shop->publicUrl();
        $fallbackImages = ['saree.jpg', 'saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
        $productImages = $product->images->map(fn ($image): string => Storage::url($image->path))->values();
        $primaryImage = $productImages->first() ?: $assetBase.'/'.$fallbackImages[((int) $product->id - 1) % count($fallbackImages)];
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl().'. Shop link: '.$shopUrl)
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    @if (! empty($previewingTemplate))<div class="rs-preview-bar" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>@endif
    <header class="rs-header"><div class="rs-topline"><span>• Direct handloom weaves &amp; wholesale silk catalogues</span><span>{{ $shop->name }} · INR ₹</span></div><div class="rs-header-main"><a class="rs-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home"><span class="rs-mark">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="44" height="44">@else{{ Str::upper(Str::substr($shop->name, 0, 1)) }}@endif</span><span><strong>{{ $shop->name }}</strong><small>RANG STUDIO · WHOLEMARKET</small></span></a><nav class="rs-nav" aria-label="Main navigation"><a href="{{ $shopUrl }}#catalogue">Catalogue</a><a href="{{ $shopUrl }}#about">About studio</a><a href="{{ $shopUrl }}#contact">Contact</a></nav><form class="rs-search" method="get" action="{{ $shopUrl }}" role="search"><span aria-hidden="true">⌕</span><input type="search" name="q" placeholder="Search weaves, colours, codes" aria-label="Search catalogue"><button type="submit">Search</button></form></div></header>

    <main class="rs-detail"><div class="rs-detail-grid"><div class="rs-gallery"><div class="rs-detail-main"><img data-rs-main-image src="{{ $primaryImage }}" alt="{{ $product->name }}" width="1200" height="1500"></div>@if ($productImages->count() > 1)<div class="rs-thumbs" aria-label="Product images">@foreach ($productImages as $index => $image)<button type="button" class="rs-thumb {{ $index === 0 ? 'is-active' : '' }}" data-rs-gallery-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="160" height="160"></button>@endforeach</div>@endif</div><div class="rs-detail-copy"><p class="rs-kicker">{{ Str::upper($product->category?->name ?: 'SAREE COLLECTION') }} · {{ $product->code }}</p><h1>{{ $product->name }}</h1><p>{{ $product->description ?: 'A considered saree selected for colour, drape and finish. Ask '.$shop->name.' for current availability and styling guidance.' }}</p><div class="rs-detail-card"><div class="rs-detail-price"><div><small>Wholesale rate</small><strong>{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry' }}</strong></div><span>{{ Str::headline($product->availability) }}</span></div><dl><div><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd></div><div><dt>Pack size</dt><dd>{{ $product->pack_size ?: 'Flexible' }}</dd></div><div><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Ask the seller' }}</dd></div><div><dt>Colour</dt><dd>{{ $product->colour ?: 'Ask the seller' }}</dd></div></dl></div><div class="rs-detail-actions">@if ($whatsappUrl)<a class="rs-button rs-button-dark" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="rs-button rs-button-dark" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry ↗</a>@endif<a class="rs-button rs-button-light" href="{{ $shopUrl }}#catalogue">Back to catalogue</a></div><div class="rs-detail-note"><strong>Atelier note.</strong> Availability and wholesale pricing are live catalogue details. Confirm current stock, colour and dispatch with {{ $shop->name }} before ordering.</div></div></div><section class="rs-detail-story"><p class="rs-kicker">Craft narrative · atelier standards</p><h2>The heritage behind every warp &amp; weft</h2><p>Each {{ $product->fabric?->name ?: 'textile' }} piece is presented with the material, minimum and contact details needed for confident sourcing.</p></section></main>
    @include('shop.partials.storefront-footer', ['shop' => $shop])
@endsection

@push('scripts')
    <script>
        (() => { const main = document.querySelector('[data-rs-main-image]'); document.querySelectorAll('[data-rs-gallery-image]').forEach((button) => button.addEventListener('click', () => { if (main) main.src = button.dataset.rsGalleryImage; document.querySelectorAll('[data-rs-gallery-image]').forEach((item) => item.classList.remove('is-active')); button.classList.add('is-active'); })); })();
    </script>
@endpush
