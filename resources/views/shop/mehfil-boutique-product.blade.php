@extends('layouts.storefront')

@section('body_class', 'mehfil-boutique')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/mehfil-boutique/style.css') }}">
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

    @if (! empty($previewingTemplate))
        <div class="mf-preview-bar" role="status">Previewing {{ $templateDetails['label'] }} · <a href="{{ route('seller.design') }}">Return to design studio</a></div>
    @endif

    <div class="mf-header">
        <div class="mf-ribbon"><div class="mf-ribbon-inner"><span>• Direct weavers &amp; master craftsmen · Verified partner</span><span><strong>Wholesale MOQ: {{ $product->minimum_order_quantity }} pcs/lot</strong> · {{ $shop->name }}</span></div></div>
        <header class="mf-header-main"><a class="mf-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home"><span class="mf-brand-mark">@if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="46" height="46">@else{{ Str::upper(Str::substr($shop->name, 0, 1)) }}@endif</span><span><span class="mf-brand-name">{{ $shop->name }}</span><small class="mf-brand-sub">Mehfil Boutique · Bespoke silks</small></span></a><form class="mf-search" method="get" action="{{ $shopUrl }}" role="search"><span aria-hidden="true">⌕</span><input type="search" name="q" placeholder="Search by weave, fabric code or colour" aria-label="Search sarees"><button type="submit" aria-label="Search">→</button></form><div class="mf-tools"><a class="mf-tool" href="{{ $shopUrl }}#catalogue">Back to catalogue ↗</a></div></header><nav class="mf-nav" aria-label="Main navigation"><a href="{{ $shopUrl }}">All sarees</a><a href="{{ $shopUrl }}#collections">Collections</a><a href="{{ $shopUrl }}#fabrics">Shop by fabric</a><a href="{{ $shopUrl }}#contact">Contact &amp; showroom</a></nav>
    </div>

    <main class="mf-detail"><div class="mf-detail-grid"><div class="mf-detail-gallery"><div class="mf-detail-main-image"><img data-main-gallery src="{{ $primaryImage }}" alt="{{ $product->name }}" width="1200" height="1500"></div>@if ($productImages->count() > 1)<div class="mf-detail-thumbs" aria-label="Product images">@foreach ($productImages as $index => $image)<button class="mf-detail-thumb {{ $index === 0 ? 'is-active' : '' }}" type="button" data-gallery-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="180" height="180"></button>@endforeach</div>@endif</div><div class="mf-detail-copy"><p class="mf-eyebrow">{{ Str::upper($product->category?->name ?: 'SAREE COLLECTION') }} · {{ $product->code }}</p><h1>{{ $product->name }}</h1><p>{{ $product->description ?: 'An editorial saree selected for colour, drape and finish. Ask '.$shop->name.' for current availability and styling guidance.' }}</p><div class="mf-detail-price-card"><div class="mf-detail-price"><div><small class="mf-product-code">Wholesale rate</small><strong>{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2) : 'Price on enquiry' }}</strong></div><span>{{ Str::headline($product->availability) }}</span></div><dl class="mf-detail-facts"><div><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd></div><div><dt>Pack size</dt><dd>{{ $product->pack_size ?: 'Flexible' }}</dd></div><div><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Ask the seller' }}</dd></div><div><dt>Colour</dt><dd>{{ $product->colour ?: 'Ask the seller' }}</dd></div></dl></div><div class="mf-detail-actions">@if ($whatsappUrl)<a class="mf-whatsapp" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="mf-whatsapp" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email the seller ↗</a>@endif<a class="mf-back" href="{{ $shopUrl }}#catalogue">Keep browsing</a></div><p class="mf-detail-note">Product names, images, pricing, minimums and availability are provided by {{ $shop->name }}. Contact the seller for lot composition, dispatch timing and any custom requirements.</p>@if ($whatsappUrl || $shopEmail || $shop->instagram_url || $shop->facebook_url)<div class="mt-5">@include('shop.partials.contact-icons', ['shop' => $shop, 'whatsappUrl' => $whatsappUrl, 'shopEmail' => $shopEmail])</div>@endif</div></div><section class="mf-story"><div class="mf-story-grid"><div><p class="mf-eyebrow">Curator technical dossier</p><h2>Quiet luxury, practical sourcing.</h2><p>Compare the material, minimum order and live availability before you make your next buying decision.</p></div><div class="mf-story-card"><strong>{{ $templateDetails['label'] }}</strong><h3>Direct seller access.</h3><ul><li>Clear product facts</li><li>Protected shop catalogue</li><li>WhatsApp conversation when configured</li></ul></div></div></section></main>
    @include('shop.partials.storefront-footer', ['shop' => $shop])
@endsection

@push('scripts')
    <script>
        (() => {
            const image = document.querySelector('[data-main-gallery]');
            document.querySelectorAll('[data-gallery-image]').forEach((button) => button.addEventListener('click', () => { if (image) image.src = button.dataset.galleryImage; document.querySelectorAll('[data-gallery-image]').forEach((item) => item.classList.remove('is-active')); button.classList.add('is-active'); }));
        })();
    </script>
@endpush
