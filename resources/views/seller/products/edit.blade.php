@extends('layouts.seller')

@section('seller_content')
    @if (session('status'))<div class="seller-alert seller-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>@endif
    <a class="seller-back-link" href="{{ route('seller.products.index') }}">← Back to products</a>
    <section class="seller-page-heading seller-editor-heading"><div><p class="seller-overline">Catalogue editor · {{ $product->code }}</p><h1>Edit product</h1><p class="seller-page-intro">Keep your product details and photography current without changing its public link.</p></div><a class="seller-heading-button" href="{{ $product->publicUrl() }}" target="_blank" rel="noopener">View storefront <span aria-hidden="true">↗</span></a></section>
    <section class="seller-editor-card">@include('seller.products._form')</section>
@endsection
