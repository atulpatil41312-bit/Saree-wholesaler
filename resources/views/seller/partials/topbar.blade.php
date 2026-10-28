<header class="seller-topbar">
    <div><p class="seller-overline">{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</p><p class="seller-breadcrumb">{{ $shop->name }} / {{ $sellerPageLabel ?? 'Catalogue' }}</p></div>
    <a class="seller-store-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">Open storefront <span aria-hidden="true">↗</span></a>
</header>
