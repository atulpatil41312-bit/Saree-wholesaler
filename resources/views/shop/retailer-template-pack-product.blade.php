@extends('layouts.storefront')

@section('body_class', 'retailer-template-pack')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/retailer-pack/storefront.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('templates/retailer-pack');
        $fallbackImage = $assetBase.'/models/look-3.jpg';
        $whatsappNumber = preg_replace('/\D/', '', $shop->whatsapp ?? '');
        $hasWhatsapp = strlen($whatsappNumber) >= 8 && strlen($whatsappNumber) <= 15;
        $whatsappUrl = $hasWhatsapp ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl().'. Shop link: '.$shop->publicUrl()) : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $showPrices = $shop->settings?->show_prices ?? true;
        $productImages = $product->images->map(fn ($image) => Storage::url($image->path))->values();
    @endphp
    <div class="wm">
      <div class="{{ $template }}">
        <a class="skip" href="#main">Skip to content</a>
        <header>
            <a class="brand" href="{{ $shop->publicUrl() }}"><span aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span><span>{{ $shop->name }}<small>{{ Str::upper($templateDetails['label']) }} · RETAILER COLLECTION</small></span></a>
            <nav aria-label="Main navigation"><a href="{{ $shop->publicUrl() }}#collection">Collection</a><a href="{{ route('market.index') }}">Market</a></nav>
        </header>
        <main id="main" class="wrap" tabindex="-1">
            <p class="eyebrow"><a href="{{ $shop->publicUrl() }}#collection">← Back to collection</a></p>
            <section class="detail">
                <div>
                    <img class="main-image" data-product-image src="{{ $productImages->first() ?: $fallbackImage }}" alt="{{ $product->name }}" width="720" height="900">
                    @if ($productImages->count() > 1)
                        <div class="thumbs" aria-label="Product images">@foreach ($productImages as $index => $image)<button type="button" data-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="65" height="80"></button>@endforeach</div>
                    @endif
                </div>
                <div><p class="eyebrow">{{ $product->category?->name ?: 'SAREE COLLECTION' }} · {{ $product->code }}</p><h1>{{ $product->name }}</h1><p class="price">{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 2).' per piece' : 'Price on enquiry' }}</p><p>{{ $product->description ?: 'Ask the retailer for fabric, colour and availability details.' }}</p><dl><dt>Fabric</dt><dd>{{ $product->fabric?->name ?: 'Contact for details' }}</dd><dt>Colour</dt><dd>{{ $product->colour ?: 'Contact for details' }}</dd><dt>Minimum order</dt><dd>{{ $product->minimum_order_quantity }} pieces</dd><dt>Availability</dt><dd>{{ Str::headline($product->availability) }}</dd></dl>@if ($whatsappUrl)<a class="button" href="{{ $whatsappUrl }}">Enquire on WhatsApp ↗</a>@elseif ($shopEmail)<a class="button" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry ↗</a>@endif @include('shop.partials.contact-icons', ['shop' => $shop])</div>
            </section>
        </main>
        <footer><span>{{ $shop->name }} · {{ $templateDetails['label'] }}</span><nav aria-label="Footer"><a href="{{ $shop->publicUrl() }}#contact">Contact</a><a href="{{ route('market.index') }}">WholeMarket</a></nav></footer>
      </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-image]').forEach((button) => button.addEventListener('click', () => { const image = document.querySelector('[data-product-image]'); image.src = button.dataset.image; }));
    </script>
@endpush
