@extends('layouts.storefront')

@section('content')
    <header>
        <a class="brand" href="{{ route('seller.dashboard') }}"><span class="emblem">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span><span>{{ Str::upper($shop->name) }}<small>SHOP PROFILE</small></span></a>
        <nav><a href="{{ route('seller.dashboard') }}">Dashboard</a><a href="{{ route('seller.design') }}">Design</a></nav>
        <form method="post" action="{{ route('logout') }}">@csrf<button>Logout</button></form>
    </header>

    <main>
        <section class="collection">
            <div class="eyebrow">PUBLIC SHOP DETAILS</div>
            <h2>Shop profile & search details</h2>
            <p>Changing the shop URL automatically keeps a redirect from the previous typed shop address.</p>
            @if (session('status'))<p>{{ session('status') }}</p>@endif
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach

            <form method="post" action="{{ route('seller.profile.update') }}" data-shop-slug-form enctype="multipart/form-data">
                @csrf
                @method('put')
                <label>Shop name<input name="name" value="{{ old('name', $shop->name) }}" required data-shop-name></label>
                <label>Public shop URL<span>{{ request()->getSchemeAndHttpHost() }}/{{ $shop->isRetailer() ? 'retailer' : 'wholesaler' }}/</span><input name="slug" value="{{ old('slug', $shop->slug) }}" required data-shop-slug></label>
                <label>Directory category<input name="market_category" value="{{ old('market_category', $shop->market_category) }}"></label>
                <label>About the shop<textarea name="description" rows="5">{{ old('description', $shop->description) }}</textarea></label>
                <label>Address<textarea name="address" rows="3">{{ old('address', $shop->address) }}</textarea></label>
                <label>Shop / unit number<input name="shop_number" value="{{ old('shop_number', $shop->shop_number) }}"></label>
                <label>Opening hours<input name="opening_hours" value="{{ old('opening_hours', $shop->opening_hours) }}"></label>
                <label>Phone<input name="phone" value="{{ old('phone', $shop->phone) }}"></label>
                <label>WhatsApp<input name="whatsapp" value="{{ old('whatsapp', $shop->whatsapp) }}"></label>
                <label>Public email<input name="email" type="email" value="{{ old('email', $shop->email) }}"></label>
                <label>Instagram URL<input name="instagram_url" type="url" value="{{ old('instagram_url', $shop->instagram_url) }}"></label>
                <label>Facebook URL<input name="facebook_url" type="url" value="{{ old('facebook_url', $shop->facebook_url) }}"></label>
                <label>Logo image (JPG, PNG, GIF or WebP; up to 2 MB)<input name="logo" type="file" accept="image/png,image/jpeg,image/gif,image/webp"></label>
                <label>Banner image (JPG, PNG, GIF or WebP; up to 5 MB)<input name="banner" type="file" accept="image/png,image/jpeg,image/gif,image/webp"></label>
                <label>SEO title<input name="seo_title" maxlength="60" value="{{ old('seo_title', $shop->seo_title) }}"></label>
                <label>SEO description<textarea name="seo_description" maxlength="160" rows="3">{{ old('seo_description', $shop->seo_description) }}</textarea></label>
                <button class="primary">Save profile</button>
            </form>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        const form = document.querySelector('[data-shop-slug-form]');
        if (form) {
            const name = form.querySelector('[data-shop-name]');
            const slug = form.querySelector('[data-shop-slug]');
            let customSlug = slug.value !== slugify(name.value);
            function slugify(value) {
                return value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            }
            name.addEventListener('input', () => { if (!customSlug) slug.value = slugify(name.value); });
            slug.addEventListener('input', () => { slug.value = slugify(slug.value); customSlug = slug.value !== slugify(name.value); });
        }
    </script>
@endpush
