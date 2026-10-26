@extends('layouts.storefront')

@section('body_class', 'auth-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/auth.css') }}?v={{ filemtime(public_path('storefront/auth.css')) }}">
@endpush

@section('content')
    <main class="auth-shell">
        <aside class="auth-visual">
            <a class="auth-brand" href="{{ route('market.index') }}">
                <span class="auth-mark">W</span>
                <span><strong>WholeMarket</strong><small>Wholesale made simple</small></span>
            </a>
            <div class="auth-visual-copy">
                <p class="auth-overline">Join the seller network</p>
                <h1>Turn your collection into a destination.</h1>
                <p>Apply once and get a polished public shop, catalogue tools, and a direct line to serious retail buyers.</p>
                <ul class="auth-benefits">
                    <li>Shop URL generated from your business name</li>
                    <li>Organise products by category</li>
                    <li>Admin review keeps the marketplace trusted</li>
                </ul>
            </div>
            <img class="auth-visual-image" src="{{ asset('storefront/saree1.jpg') }}" alt="Saree collection preview">
        </aside>

        <section class="auth-card" aria-labelledby="signup-title">
            <div class="auth-card-header">
                <p class="auth-overline">Seller application</p>
                <h2 id="signup-title">Create your shop</h2>
                <p class="auth-card-intro">Share a few details and we’ll reserve your public shop address. An admin reviews every application before it goes live.</p>
            </div>

            <form class="auth-form" method="post" action="{{ route('seller.signup.store') }}" data-shop-slug-form>
                    @csrf

                    <label>
                        Your name
                        <input name="seller_name" type="text" value="{{ old('seller_name') }}" required autocomplete="name" autofocus>
                    </label>
                    @error('seller_name')<p class="auth-error">{{ $message }}</p>@enderror

                    <label>
                        Email address
                        <input name="seller_email" type="email" value="{{ old('seller_email') }}" required autocomplete="email">
                    </label>
                    @error('seller_email')<p class="auth-error">{{ $message }}</p>@enderror

                    <label>
                        Shop name
                        <input name="name" type="text" value="{{ old('name') }}" required data-shop-name>
                    </label>
                    @error('name')<p class="auth-error">{{ $message }}</p>@enderror

                    <label>
                        Shop URL
                        <span class="auth-slug-field"><span>{{ request()->getSchemeAndHttpHost() }}/wholesaler/</span><input name="slug" type="text" value="{{ old('slug') }}" pattern="[A-Za-z0-9-]+" required data-shop-slug></span>
                    </label>
                    <p class="auth-form-note">Generated from your shop name. You can edit it; use letters, numbers, and hyphens only.</p>
                    @error('slug')<p class="auth-error">{{ $message }}</p>@enderror

                    <label>
                        Password
                        <input name="seller_password" type="password" required autocomplete="new-password">
                    </label>
                    @error('seller_password')<p class="auth-error">{{ $message }}</p>@enderror

                    <label>
                        Confirm password
                        <input name="seller_password_confirmation" type="password" required autocomplete="new-password">
                    </label>

                    <button class="auth-primary" type="submit">Submit application <span aria-hidden="true">→</span></button>
                </form>

            <div class="auth-footer"><span>Already have an account?</span><a href="{{ route('login') }}">Sign in</a></div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-shop-slug-form]').forEach((form) => {
            const name = form.querySelector('[data-shop-name]');
            const slug = form.querySelector('[data-shop-slug]');
            let customSlug = slug.value.trim() !== '';
            const slugify = (value) => value.toLowerCase().normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');

            name.addEventListener('input', () => {
                if (!customSlug) slug.value = slugify(name.value);
            });

            slug.addEventListener('input', () => {
                slug.value = slugify(slug.value);
                customSlug = slug.value !== slugify(name.value);
            });
        });
    </script>
@endpush
