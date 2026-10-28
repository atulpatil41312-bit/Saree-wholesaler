@extends('layouts.storefront')

@section('body_class', 'seller-dashboard-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/seller-dashboard.css') }}?v={{ filemtime(public_path('storefront/seller-dashboard.css')) }}">
@endpush

@section('content')
    <div class="seller-shell">
        <aside class="seller-sidebar">
            <a class="seller-brand" href="{{ route('seller.dashboard') }}" aria-label="{{ $shop->name }} dashboard">
                <span class="seller-brand-mark">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                <span><strong>{{ $shop->name }}</strong><small>{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</small></span>
            </a>
            <nav class="seller-nav" aria-label="Seller navigation">
                <span class="seller-nav-label">Workspace</span>
                <a class="seller-nav-link is-active" href="{{ route('seller.dashboard') }}"><span aria-hidden="true">⌂</span>Overview</a>
                <a class="seller-nav-link" href="{{ route('seller.profile.edit') }}"><span aria-hidden="true">◉</span>Shop profile</a>
                <a class="seller-nav-link" href="{{ route('seller.design') }}"><span aria-hidden="true">◇</span>Design</a>
                <a class="seller-nav-link" href="{{ route('seller.enquiries.index') }}"><span aria-hidden="true">↗</span>Enquiries</a>
                @if ($shop->isWholesaler())
                    <a class="seller-nav-link" href="{{ route('seller.sponsorships.index') }}"><span aria-hidden="true">♡</span>Retailer sponsors</a>
                @endif
                <span class="seller-nav-label seller-nav-label-spaced">Shortcuts</span>
                <a class="seller-nav-link" href="#add-product"><span aria-hidden="true">＋</span>Add catalogue item</a>
                <a class="seller-nav-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener"><span aria-hidden="true">↗</span>View storefront</a>
            </nav>
            <div class="seller-sidebar-footer">
                <div class="seller-user-chip"><span class="seller-user-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span></div>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="seller-signout" type="submit">Sign out <span aria-hidden="true">↗</span></button></form>
            </div>
        </aside>

        <div class="seller-workspace">
            <header class="seller-topbar"><div><p class="seller-overline">{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</p><p class="seller-breadcrumb">{{ $shop->name }} / Overview</p></div><a class="seller-store-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">Open storefront <span aria-hidden="true">↗</span></a></header>
            <main class="seller-content">
                @if (session('status'))<div class="seller-alert seller-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="seller-alert seller-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                <section class="seller-page-heading"><div><p class="seller-overline">Your {{ $shop->isRetailer() ? 'retail' : 'wholesale' }} catalogue</p><h1>Your catalogue</h1><p class="seller-page-intro">Welcome back, {{ Str::before($shop->name, ' ') }}. Add polished product listings, keep your storefront fresh, and turn enquiries into business.</p></div><div class="seller-heading-actions"><span class="seller-status seller-status-{{ $shop->suspended_at ? 'suspended' : $shop->status }}">{{ $shop->suspended_at ? 'Suspended' : Str::headline($shop->status) }}</span><a class="seller-heading-button" href="#add-product">Add a product <span aria-hidden="true">＋</span></a></div></section>

                <section class="seller-shop-banner"><div><span class="seller-shop-banner-label">Public shop address</span><a href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">{{ $shop->publicUrl() }} <span aria-hidden="true">↗</span></a></div><div class="seller-plan-summary"><span>Current plan</span><strong>{{ $subscription?->plan?->name ?? 'Legacy access' }}</strong><small>{{ $subscription ? Str::headline($subscription->status) : 'Access inherited' }}</small></div></section>

                <section class="seller-metrics" aria-label="Catalogue metrics"><article class="seller-metric-card seller-metric-primary"><span class="seller-metric-icon" aria-hidden="true">▦</span><div><span class="seller-metric-label">Products</span><strong>{{ $shop->products->count() }}<small> / {{ $limits['product_limit'] }}</small></strong><p>Pieces in your catalogue</p></div></article><article class="seller-metric-card"><span class="seller-metric-icon" aria-hidden="true">◌</span><div><span class="seller-metric-label">Categories</span><strong>{{ $shop->categories->count() }}<small> / {{ $limits['category_limit'] }}</small></strong><p>Organised collections</p></div></article><article class="seller-metric-card"><span class="seller-metric-icon" aria-hidden="true">▧</span><div><span class="seller-metric-label">Images per product</span><strong>{{ $limits['product_image_limit'] }}</strong><p>Upload up to {{ $limits['product_image_limit'] }} photos</p></div></article><article class="seller-metric-card"><span class="seller-metric-icon" aria-hidden="true">◒</span><div><span class="seller-metric-label">Media storage</span><strong>{{ number_format($shop->storage_used_bytes / 1048576, 1) }}<small> MB</small></strong><p>of {{ number_format($shop->storage_quota_bytes / 1048576, 0) }} MB available</p></div></article></section>

                <section class="seller-section-heading" id="catalogue-setup"><div><p class="seller-overline">Catalogue setup</p><h2>Keep your catalogue moving</h2></div><span class="seller-section-note">Create a collection, add a product, then publish it.</span></section>
                <section class="seller-action-grid">
                    <article class="seller-panel seller-form-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">01 · Collection</span><h3>Add a category</h3></div><span class="seller-panel-number">01</span></div><p class="seller-panel-copy">Group your sarees so buyers can browse your catalogue with ease.</p><form method="post" action="{{ route('seller.categories.store') }}" class="seller-form">@csrf<label>Category name<input name="name" value="{{ old('name') }}" placeholder="Wedding sarees" required></label><button class="seller-primary-button" type="submit">Create category <span aria-hidden="true">→</span></button></form></article>

                    <article class="seller-panel seller-product-panel" id="add-product"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">02 · Catalogue</span><h3>Add a saree</h3></div><span class="seller-panel-number">02</span></div><p class="seller-panel-copy">Add the essentials buyers need, then upload up to {{ $limits['product_image_limit'] }} clear product photos.</p>@if ($shop->categories->isEmpty())<div class="seller-empty-inline"><span aria-hidden="true">◌</span><strong>Create a category first.</strong><p>Your product form will appear here once the catalogue has a collection.</p><a class="seller-inline-link" href="#catalogue-setup">Create a category <span aria-hidden="true">→</span></a></div>@else<form method="post" action="{{ route('seller.products.store') }}" class="seller-form seller-product-form" enctype="multipart/form-data">@csrf<div class="seller-form-grid"><label>Category<select name="category_id" required>@foreach($shop->categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label><label>Product name<input name="name" value="{{ old('name') }}" placeholder="Royal silk saree" required></label><label>Design code<input name="code" value="{{ old('code') }}" placeholder="RS-001" required></label><label>Wholesale price (₹)<input name="wholesale_price" value="{{ old('wholesale_price') }}" inputmode="decimal" placeholder="1250"></label><label>Minimum order quantity<input name="minimum_order_quantity" type="number" min="1" value="{{ old('minimum_order_quantity', 1) }}" required></label><label>Pack size <span class="seller-label-muted">optional</span><input name="pack_size" type="number" min="1" value="{{ old('pack_size') }}" placeholder="6"></label><label>Availability<select name="availability"><option value="available">Available</option><option value="out_of_stock">Out of stock</option><option value="on_enquiry">On enquiry</option></select></label></div><label class="seller-upload-field" for="product-images"><span class="seller-upload-label"><span>Product photos</span><small>JPG, PNG, GIF or WebP · up to 5 MB each</small></span><span class="seller-upload-dropzone"><span class="seller-upload-icon" aria-hidden="true">↑</span><span><strong>Choose product images</strong><small>Upload up to {{ $limits['product_image_limit'] }} photos · images are resized automatically</small></span><span class="seller-upload-button">Browse</span></span><input id="product-images" name="images[]" type="file" accept="image/png,image/jpeg,image/gif,image/webp" multiple data-image-picker data-max-images="{{ $limits['product_image_limit'] }}"><span class="seller-upload-preview" data-image-preview aria-live="polite"></span></label><button class="seller-primary-button" type="submit">Add product <span aria-hidden="true">→</span></button></form>@endif</article>

                    <article class="seller-panel seller-tools-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">03 · Storefront</span><h3>Present your business</h3></div><span class="seller-panel-number">03</span></div><p class="seller-panel-copy">Make your shop feel complete with a clear profile, a strong design, and a live preview.</p><div class="seller-link-stack"><a href="{{ route('seller.profile.edit') }}"><span><strong>Edit shop profile</strong><small>Contact, address, social links</small></span><b aria-hidden="true">↗</b></a><a href="{{ route('seller.design') }}"><span><strong>Choose design</strong><small>Pick your storefront template</small></span><b aria-hidden="true">↗</b></a><a href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener"><span><strong>Preview storefront</strong><small>See your public catalogue</small></span><b aria-hidden="true">↗</b></a></div></article>
                </section>

                <section class="seller-recent-section" aria-labelledby="recent-products-title"><div class="seller-section-heading"><div><p class="seller-overline">Your catalogue</p><h2 id="recent-products-title">Recently added products</h2></div><span class="seller-section-note">{{ $shop->products->count() }} of {{ $limits['product_limit'] }} product slots used</span></div>@if ($shop->products->isEmpty())<div class="seller-recent-empty"><span class="seller-empty-icon" aria-hidden="true">✦</span><div><strong>Your first saree is one step away.</strong><p>Add a category and product above to start building your public catalogue.</p></div><a class="seller-secondary-button" href="#add-product">Add a product <span aria-hidden="true">→</span></a></div>@else<div class="seller-product-list">@foreach ($shop->products->sortByDesc('created_at')->take(6) as $product)<article class="seller-product-card"><a class="seller-product-thumb" href="{{ $product->publicUrl() }}" target="_blank" rel="noopener">@if ($product->images->first())<img src="{{ Storage::url($product->images->first()->path) }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}" loading="lazy">@else<span aria-hidden="true">✦</span>@endif</a><div class="seller-product-card-body"><div class="seller-product-card-topline"><span>{{ $product->code }}</span><span class="seller-product-availability seller-product-availability-{{ $product->availability }}">{{ Str::headline($product->availability) }}</span></div><h3>{{ $product->name }}</h3><p>{{ $product->category?->name ?: 'Uncategorised' }} · {{ $product->images->count() }} {{ Str::plural('photo', $product->images->count()) }}</p><a class="seller-text-link" href="{{ $product->publicUrl() }}" target="_blank" rel="noopener">View storefront <span aria-hidden="true">↗</span></a></div></article>@endforeach</div>@endif</section>

                @if ($shop->isWholesaler())<section class="seller-sponsor-panel"><div><p class="seller-overline">Grow your network</p><h2>{{ $sponsorships->count() }} retailer sponsorships</h2><p>Invite retailers to sell your catalogue with a Pro plan sponsorship. Track active relationships and upcoming renewals in one place.</p></div><a class="seller-secondary-button" href="{{ route('seller.sponsorships.index') }}">Manage retailer sponsors <span aria-hidden="true">→</span></a></section>@endif
            </main>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const picker = document.querySelector('[data-image-picker]');
            const preview = document.querySelector('[data-image-preview]');

            if (!picker || !preview) {
                return;
            }

            picker.addEventListener('change', () => {
                preview.replaceChildren();
                const files = Array.from(picker.files || []).slice(0, Number(picker.dataset.maxImages || 1));

                files.forEach((file) => {
                    if (!file.type.startsWith('image/')) {
                        return;
                    }

                    const item = document.createElement('span');
                    item.className = 'seller-upload-preview-item';
                    const image = document.createElement('img');
                    image.alt = file.name;
                    image.src = URL.createObjectURL(file);
                    image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });
                    item.append(image);
                    preview.append(item);
                });

                if (files.length > 0) {
                    const count = document.createElement('small');
                    count.className = 'seller-upload-count';
                    count.textContent = `${files.length} ${files.length === 1 ? 'photo' : 'photos'} selected`;
                    preview.append(count);
                }
            });
        })();
    </script>
@endpush
