@extends('layouts.storefront')

@section('body_class', 'ivory-weaves')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/ivory-weaves/style.css') }}">
    <style>
        .iw-product-detail { display: grid; grid-template-columns: 1.1fr 1fr; gap: 64px; max-width: 1380px; margin: 0 auto; padding: 78px 5%; }
        .iw-detail-photo { overflow: hidden; border-radius: 14px; background: #e6e0d3; }
        .iw-detail-photo > img { width: 100%; aspect-ratio: 3 / 4; object-fit: cover; }
        .iw-thumbs { display: flex; gap: 12px; padding-top: 14px; }
        .iw-thumbs button { width: 70px; padding: 0; border: 1px solid #b9c0b1; background: transparent; cursor: pointer; }
        .iw-thumbs img { width: 100%; aspect-ratio: 3 / 4; object-fit: cover; }
        .iw-detail-copy { padding-top: 18px; }
        .iw-detail-copy h1 { margin: 0 0 18px; color: var(--iw-forest); font-size: clamp(38px, 5vw, 68px); font-weight: 400; line-height: 1.03; letter-spacing: -1.7px; }
        .iw-detail-price { margin-bottom: 22px; font-size: 24px; }
        .iw-detail-copy > p:not(.iw-eyebrow) { color: #58604f; font-size: 17px; line-height: 1.7; }
        .iw-detail-facts { display: grid; grid-template-columns: 1fr 1fr; gap: 13px 22px; margin: 28px 0; padding: 24px 0; border-top: 1px solid var(--iw-line); border-bottom: 1px solid var(--iw-line); font: 13px/1.5 Arial, sans-serif; }
        .iw-detail-facts dt { color: #68735f; }
        .iw-detail-facts dd { margin: 0; }
        .iw-detail-copy .iw-primary { margin-top: 8px; }
        .iw-detail-socials { margin-top: 24px; }
        @media (max-width: 760px) { .iw-product-detail { grid-template-columns: 1fr; gap: 28px; padding: 52px 5%; } .iw-detail-copy { padding-top: 0; } }
    </style>
@endpush

@section('content')
    @php
        $assetBase = asset('templates/ivory-weaves');
        $shopUrl = $shop->publicUrl();
        $fallbackImage = $assetBase.'/hero.png';
        $productImages = $product->images->map(fn ($image): string => Storage::url($image->path))->values();
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl().'. Shop link: '.$shopUrl)
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    <div class="iw-shell">
        <div class="iw-announcement">Sarees for your store. Conversations that matter. <span>{{ Str::upper($templateDetails['badge']) }}</span></div>
        <header class="iw-header">
            <a class="iw-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                @if ($shop->logo_path)<img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="52" height="52">@else<span class="iw-brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>@endif
                <span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · SAREE ATELIER</small></span>
            </a>
            <nav class="iw-nav" aria-label="Shop navigation"><a href="{{ $shopUrl }}#collection">Collection</a><a href="{{ route('market.index') }}">Market</a><a href="{{ $shopUrl }}#contact">Contact</a></nav>
        </header>

        <main>
            <div class="iw-product-detail">
                <div>
                    <div class="iw-detail-photo"><img data-product-image src="{{ $productImages->first() ?: $fallbackImage }}" alt="{{ $product->name }}" width="720" height="960"></div>
                    @if ($productImages->count() > 1)
                        <div class="iw-thumbs" aria-label="Product images">
                            @foreach ($productImages as $index => $image)
                                <button type="button" data-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="70" height="90"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="iw-detail-copy">
                    <p class="iw-eyebrow"><a href="{{ $shopUrl }}#collection">← Back to collection</a></p>
                    <p class="iw-eyebrow">{{ Str::upper($product->category?->name ?: 'SAREE COLLECTION') }} · {{ $product->code }}</p>
                    <h1>{{ $product->name }}</h1>
                    <div class="iw-detail-price">{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2).' per piece' : 'Price on enquiry' }}</div>
                    <p>{{ $product->description ?: 'Ask '.$shop->name.' for fabric, colour and availability details.' }}</p>
                    <dl class="iw-detail-facts"><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Contact for details' }}</dd><dt>Colour</dt><dd>{{ $product->colour ?: 'Contact for details' }}</dd><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd><dt>Availability</dt><dd>{{ Str::headline($product->availability) }}</dd></dl>
                    @if ($whatsappUrl)<a class="iw-primary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="iw-primary" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry ↗</a>@endif
                    <div class="iw-detail-socials">@include('shop.partials.contact-icons', ['shop' => $shop])</div>
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
