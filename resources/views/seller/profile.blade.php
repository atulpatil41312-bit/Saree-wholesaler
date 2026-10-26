@extends('layouts.storefront')

@section('body_class', 'seller-dashboard-page seller-profile-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/seller-dashboard.css') }}?v={{ filemtime(public_path('storefront/seller-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('storefront/seller-profile.css') }}?v={{ filemtime(public_path('storefront/seller-profile.css')) }}">
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
                <a class="seller-nav-link" href="{{ route('seller.dashboard') }}"><span aria-hidden="true">⌂</span>Overview</a>
                <a class="seller-nav-link is-active" href="{{ route('seller.profile.edit') }}"><span aria-hidden="true">◉</span>Shop profile</a>
                <a class="seller-nav-link" href="{{ route('seller.design') }}"><span aria-hidden="true">◇</span>Design</a>
                <a class="seller-nav-link" href="{{ route('seller.enquiries.index') }}"><span aria-hidden="true">↗</span>Enquiries</a>
                @if ($shop->isWholesaler())<a class="seller-nav-link" href="{{ route('seller.sponsorships.index') }}"><span aria-hidden="true">♡</span>Retailer sponsors</a>@endif
                <span class="seller-nav-label seller-nav-label-spaced">Shortcuts</span>
                <a class="seller-nav-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener"><span aria-hidden="true">↗</span>View storefront</a>
            </nav>
            <div class="seller-sidebar-footer"><div class="seller-user-chip"><span class="seller-user-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span></div><form method="post" action="{{ route('logout') }}">@csrf<button class="seller-signout" type="submit">Sign out <span aria-hidden="true">↗</span></button></form></div>
        </aside>

        <div class="seller-workspace">
            <header class="seller-topbar"><div><p class="seller-overline">{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</p><p class="seller-breadcrumb">{{ $shop->name }} / Shop profile</p></div><a class="seller-store-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">Open storefront <span aria-hidden="true">↗</span></a></header>
            <main class="seller-content profile-content">
                @if (session('status'))<div class="seller-alert seller-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="seller-alert seller-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                <section class="seller-page-heading profile-page-heading"><div><p class="seller-overline">Public shop details</p><h1>Shop profile</h1><p class="seller-page-intro">Keep your public information clear and up to date so buyers know who they are contacting.</p></div><span class="seller-status seller-status-{{ $shop->suspended_at ? 'suspended' : $shop->status }}">{{ $shop->suspended_at ? 'Suspended' : Str::headline($shop->status) }}</span></section>

                <form method="post" action="{{ route('seller.profile.update') }}" data-shop-slug-form enctype="multipart/form-data" class="profile-layout">
                    @csrf
                    @method('put')
                    <div class="profile-form-column">
                        <section class="seller-panel profile-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">01 · Public presence</span><h2>How buyers find you</h2></div><span class="seller-panel-number">01</span></div><p class="seller-panel-copy">Your shop name and address appear in the marketplace directory and on your storefront.</p><div class="profile-fields"><label>Shop name <span class="profile-required">Required</span><input name="name" value="{{ old('name', $shop->name) }}" required data-shop-name autocomplete="organization"></label><label>Directory category <span class="profile-optional">Optional</span><input name="market_category" value="{{ old('market_category', $shop->market_category) }}" placeholder="Silk sarees, wedding wear"></label><label class="profile-field-wide">Public shop URL <span class="profile-optional">Share this link with buyers</span><div class="profile-url-field"><span>{{ request()->getSchemeAndHttpHost() }}/{{ $shop->isRetailer() ? 'retailer' : 'wholesaler' }}/</span><input name="slug" value="{{ old('slug', $shop->slug) }}" required data-shop-slug></div><small class="profile-url-preview">Live preview: <a data-shop-url-preview href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">{{ $shop->publicUrl() }} ↗</a></small></label><label class="profile-field-wide">About your shop <span class="profile-optional">A short introduction</span><textarea name="description" rows="5" maxlength="5000" placeholder="Tell buyers what makes your collection special...">{{ old('description', $shop->description) }}</textarea></label></div></section>

                        <section class="seller-panel profile-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">02 · Contact details</span><h2>Help buyers reach you</h2></div><span class="seller-panel-number">02</span></div><p class="seller-panel-copy">Add accurate contact and location details for enquiries and wholesale visits.</p><div class="profile-fields"><label class="profile-field-wide">Address <span class="profile-optional">Showroom or warehouse address</span><textarea name="address" rows="3" placeholder="Building, street, city, state">{{ old('address', $shop->address) }}</textarea></label><label>Shop / unit number <span class="profile-optional">Optional</span><input name="shop_number" value="{{ old('shop_number', $shop->shop_number) }}" placeholder="Unit 12"></label><label>Opening hours <span class="profile-optional">Optional</span><input name="opening_hours" value="{{ old('opening_hours', $shop->opening_hours) }}" placeholder="Mon–Sat, 10am–7pm"></label><label>Phone <span class="profile-optional">Optional</span><input name="phone" value="{{ old('phone', $shop->phone) }}" inputmode="tel" placeholder="+91 98765 43210"></label><label>WhatsApp <span class="profile-optional">Shows the floating enquiry icon</span><input name="whatsapp" value="{{ old('whatsapp', $shop->whatsapp) }}" inputmode="tel" placeholder="+91 98765 43210"></label><label class="profile-field-wide">Public email <span class="profile-optional">For buyer enquiries</span><input name="email" type="email" value="{{ old('email', $shop->email) }}" placeholder="sales@example.com"></label><label>Instagram URL <span class="profile-optional">Optional social link</span><input name="instagram_url" type="url" value="{{ old('instagram_url', $shop->instagram_url) }}" placeholder="https://instagram.com/your-shop"></label><label>Facebook URL <span class="profile-optional">Optional social link</span><input name="facebook_url" type="url" value="{{ old('facebook_url', $shop->facebook_url) }}" placeholder="https://facebook.com/your-shop"></label></div></section>

                        <section class="seller-panel profile-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">03 · Brand assets</span><h2>Make it recognisably yours</h2></div><span class="seller-panel-number">03</span></div><p class="seller-panel-copy">Use a logo and a banner image to make your storefront feel polished. Existing images stay until you upload a replacement.</p><div class="profile-upload-grid"><label class="profile-upload-card">Logo image <span>JPG, PNG, GIF or WebP · up to 2 MB</span>@if ($shop->logo_path)<span class="profile-current-image"><img src="{{ asset('storage/'.$shop->logo_path) }}" alt="Current shop logo">Current logo saved</span>@endif<input name="logo" type="file" accept="image/png,image/jpeg,image/gif,image/webp"><small>Square images work best.</small></label><label class="profile-upload-card">Banner image <span>JPG, PNG, GIF or WebP · up to 5 MB</span>@if ($shop->banner_path)<span class="profile-current-image profile-current-banner"><img src="{{ asset('storage/'.$shop->banner_path) }}" alt="Current shop banner">Current banner saved</span>@endif<input name="banner" type="file" accept="image/png,image/jpeg,image/gif,image/webp"><small>Use a wide product or showroom image.</small></label></div></section>

                        <section class="seller-panel profile-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">04 · Search preview</span><h2>Be easier to discover</h2></div><span class="seller-panel-number">04</span></div><p class="seller-panel-copy">These fields help search engines and social previews describe your public shop.</p><div class="profile-fields"><label class="profile-field-wide">SEO title <span class="profile-optional">Up to 60 characters</span><input name="seo_title" maxlength="60" value="{{ old('seo_title', $shop->seo_title) }}" placeholder="Divya Saree · Wholesale silk collection"></label><label class="profile-field-wide">SEO description <span class="profile-optional">Up to 160 characters</span><textarea name="seo_description" maxlength="160" rows="3" placeholder="Wholesale sarees from Divya Saree. Browse the latest collection and enquire directly.">{{ old('seo_description', $shop->seo_description) }}</textarea></label></div></section>

                        <div class="profile-save-bar"><p><strong>Ready to publish your updates?</strong><span>Your catalogue and products will not be changed.</span></p><button class="seller-primary-button" type="submit">Save profile <span aria-hidden="true">→</span></button></div>
                    </div>

                    <aside class="profile-side-column"><section class="seller-panel profile-preview-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">Live preview</span><h2>Your shop card</h2></div><span class="seller-panel-number">↗</span></div><div class="profile-shop-card"><span class="profile-shop-avatar">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span><span class="profile-shop-type">{{ Str::headline($shop->type) }}</span><h3>{{ $shop->name }}</h3><p>{{ $shop->market_category ?: 'Wholesale saree collection' }}</p><a href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">View storefront ↗</a></div><div class="profile-preview-note"><span aria-hidden="true">i</span><p>Your public URL changes automatically when you edit the shop name. You can also customise the slug.</p></div></section><section class="seller-panel profile-checklist-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">Profile checklist</span><h2>Build trust faster</h2></div></div><ul class="profile-checklist"><li class="{{ $shop->description ? 'is-complete' : '' }}"><span>{{ $shop->description ? '✓' : '1' }}</span><div><strong>Tell your story</strong><small>Add an about description</small></div></li><li class="{{ $shop->address ? 'is-complete' : '' }}"><span>{{ $shop->address ? '✓' : '2' }}</span><div><strong>Add your location</strong><small>Help buyers find your shop</small></div></li><li class="{{ $shop->phone || $shop->whatsapp || $shop->email ? 'is-complete' : '' }}"><span>{{ $shop->phone || $shop->whatsapp || $shop->email ? '✓' : '3' }}</span><div><strong>Share contact details</strong><small>Make enquiries easy</small></div></li><li class="{{ $shop->logo_path || $shop->banner_path ? 'is-complete' : '' }}"><span>{{ $shop->logo_path || $shop->banner_path ? '✓' : '4' }}</span><div><strong>Add brand imagery</strong><small>Show your collection well</small></div></li></ul></section></aside>
                </form>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const form = document.querySelector('[data-shop-slug-form]');
        if (form) {
            const name = form.querySelector('[data-shop-name]');
            const slug = form.querySelector('[data-shop-slug]');
            const preview = form.querySelector('[data-shop-url-preview]');
            let customSlug = slug.value !== slugify(name.value);
            function slugify(value) { return value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); }
            function updatePreview() { if (preview) { preview.textContent = `${preview.dataset.base}${slug.value} ↗`; preview.href = `${preview.dataset.base}${slug.value}`; } }
            if (preview) preview.dataset.base = preview.href.replace(/[^/]+$/, '');
            name.addEventListener('input', () => { if (!customSlug) slug.value = slugify(name.value); updatePreview(); });
            slug.addEventListener('input', () => { slug.value = slugify(slug.value); customSlug = slug.value !== slugify(name.value); updatePreview(); });
        }
    </script>
@endpush
