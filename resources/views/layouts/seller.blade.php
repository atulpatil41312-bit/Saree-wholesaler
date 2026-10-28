@extends('layouts.storefront')

@section('body_class', 'seller-dashboard-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/seller-dashboard.css') }}?v={{ filemtime(public_path('storefront/seller-dashboard.css')) }}">
@endpush

@section('content')
    <div class="seller-shell">
        @include('seller.partials.sidebar', ['sellerSection' => $sellerSection ?? 'catalogue'])

        <div class="seller-workspace">
            @include('seller.partials.topbar', ['sellerPageLabel' => $sellerPageLabel ?? 'Catalogue'])
            <main class="seller-content">
                @yield('seller_content')
            </main>
        </div>
    </div>
@endsection
