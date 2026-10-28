@extends('layouts.seller')

@section('seller_content')
    @if (session('status'))<div class="seller-alert seller-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>@endif
    <a class="seller-back-link" href="{{ route('seller.products.index') }}">← Back to products</a>
    <section class="seller-page-heading seller-editor-heading"><div><p class="seller-overline">Catalogue editor</p><h1>Add a new product</h1><p class="seller-page-intro">Create a complete saree listing once, then share it across every storefront design.</p></div><span class="seller-editor-step">Step 1 of 1</span></section>

    @if ($shop->categories->isEmpty())
        <section class="seller-editor-empty"><span class="seller-empty-icon" aria-hidden="true">◌</span><div><p class="seller-overline">Start with a collection</p><h2>Create a category before adding a product.</h2><p>Categories help buyers browse your sarees and are required for every catalogue item.</p></div><a class="seller-primary-button" href="{{ route('seller.dashboard') }}#catalogue-setup">Create a category <span aria-hidden="true">→</span></a></section>
    @else
        <section class="seller-editor-card">@include('seller.products._form')</section>
    @endif
@endsection
