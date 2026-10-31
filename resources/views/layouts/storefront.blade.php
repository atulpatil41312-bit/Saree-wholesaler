@php
    $isStorefrontPage = isset($shop) && (request()->routeIs('shops.*') || request()->routeIs('retailer.shops.*') || request()->routeIs('seller.design.preview'));
    $bodyClass = trim(implode(' ', array_filter([
        $isStorefrontPage ? 'wm-storefront-page' : null,
        trim($__env->yieldContent('body_class')),
    ])));
    $fallbackSlides = [
        asset('storefront/saree.jpg'),
        asset('storefront/saree0.jpg'),
        asset('storefront/saree1.jpg'),
        asset('storefront/saree2.jpg'),
    ];
@endphp
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#7b293e"><title>{{ $title ?? 'WholeMarket' }}</title>@if(isset($shop) && $shop->seo_description)<meta name="description" content="{{ $shop->seo_description }}">@endif<link rel="manifest" href="/manifest.webmanifest"><link rel="preconnect" href="https://fonts.bunny.net"><link rel="stylesheet" href="https://fonts.bunny.net/css?family=jura:300,500"><link rel="stylesheet" href="{{ asset('storefront/style.css') }}">@stack('styles')<link rel="stylesheet" href="{{ asset('storefront/hero-overrides.css') }}"><link rel="stylesheet" href="{{ asset('storefront/contact-icons.css') }}"><link rel="stylesheet" href="{{ asset('storefront/storefront-footer.css') }}"><link rel="stylesheet" href="{{ asset('storefront/premium-overrides.css') }}"><link rel="stylesheet" href="{{ asset('storefront/hover-effects.css') }}"><link rel="stylesheet" href="{{ asset('storefront/expanding-gallery.css') }}">@if($isStorefrontPage)<link rel="stylesheet" href="{{ asset('storefront/modern-overrides.css') }}">@endif</head><body class="{{ $bodyClass }}">@yield('content')@if(isset($shop) && (request()->routeIs('shops.*') || request()->routeIs('retailer.shops.*') || request()->routeIs('seller.design.preview'))) @include('shop.partials.whatsapp-float', ['shop' => $shop]) @endif<script>if('serviceWorker'in navigator){navigator.serviceWorker.register('/sw.js')}</script>@if($isStorefrontPage)<script>window.WM_FALLBACK_SLIDES = {{ Illuminate\Support\Js::from($fallbackSlides) }};</script><script src="{{ asset('storefront/modern-enhancements.js') }}" defer></script>@endif @stack('scripts')</body></html>
