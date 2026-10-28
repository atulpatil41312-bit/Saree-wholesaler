<aside class="seller-sidebar">
    <a class="seller-brand" href="{{ route('seller.dashboard') }}" aria-label="{{ $shop->name }} dashboard">
        <span class="seller-brand-mark">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
        <span><strong>{{ $shop->name }}</strong><small>{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</small></span>
    </a>
    <nav class="seller-nav" aria-label="Seller navigation">
        <span class="seller-nav-label">Workspace</span>
        <a class="seller-nav-link {{ ($sellerSection ?? '') === 'overview' ? 'is-active' : '' }}" href="{{ route('seller.dashboard') }}"><span aria-hidden="true">⌂</span>Overview</a>
        <a class="seller-nav-link {{ ($sellerSection ?? '') === 'profile' ? 'is-active' : '' }}" href="{{ route('seller.profile.edit') }}"><span aria-hidden="true">◉</span>Shop profile</a>
        <a class="seller-nav-link {{ ($sellerSection ?? '') === 'design' ? 'is-active' : '' }}" href="{{ route('seller.design') }}"><span aria-hidden="true">◇</span>Design</a>
        <a class="seller-nav-link {{ ($sellerSection ?? '') === 'enquiries' ? 'is-active' : '' }}" href="{{ route('seller.enquiries.index') }}"><span aria-hidden="true">↗</span>Enquiries</a>
        @if ($shop->isWholesaler())
            <a class="seller-nav-link {{ ($sellerSection ?? '') === 'sponsors' ? 'is-active' : '' }}" href="{{ route('seller.sponsorships.index') }}"><span aria-hidden="true">♡</span>Retailer sponsors</a>
        @endif
        <span class="seller-nav-label seller-nav-label-spaced">Shortcuts</span>
        <a class="seller-nav-link {{ ($sellerSection ?? '') === 'catalogue' ? 'is-active' : '' }}" href="{{ route('seller.products.create') }}"><span aria-hidden="true">＋</span>Add catalogue item</a>
        <a class="seller-nav-link {{ ($sellerSection ?? '') === 'products' ? 'is-active' : '' }}" href="{{ route('seller.products.index') }}"><span aria-hidden="true">▦</span>Manage products</a>
        <a class="seller-nav-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener"><span aria-hidden="true">↗</span>View storefront</a>
    </nav>
    <div class="seller-sidebar-footer">
        <div class="seller-user-chip"><span class="seller-user-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span></div>
        <form method="post" action="{{ route('logout') }}">@csrf<button class="seller-signout" type="submit">Sign out <span aria-hidden="true">↗</span></button></form>
    </div>
</aside>
