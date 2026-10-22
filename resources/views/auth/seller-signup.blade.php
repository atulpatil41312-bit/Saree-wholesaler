@extends('layouts.storefront')

@section('content')
    <main>
        <section class="contact">
            <div>
                <div class="eyebrow">JOIN WHOLEMARKET</div>
                <h2>Apply to sell</h2>
                <p>Submit your details to create a seller account and a pending wholesale shop. An admin will review and activate it before it is shown in the market.</p>

                <form method="post" action="{{ route('seller.signup.store') }}" data-shop-slug-form>
                    @csrf

                    <label>
                        Your name
                        <input name="seller_name" type="text" value="{{ old('seller_name') }}" required autocomplete="name">
                    </label>
                    @error('seller_name')<p>{{ $message }}</p>@enderror

                    <label>
                        Email address
                        <input name="seller_email" type="email" value="{{ old('seller_email') }}" required autocomplete="email">
                    </label>
                    @error('seller_email')<p>{{ $message }}</p>@enderror

                    <label>
                        Shop name
                        <input name="name" type="text" value="{{ old('name') }}" required data-shop-name>
                    </label>
                    @error('name')<p>{{ $message }}</p>@enderror

                    <label>
                        Shop URL
                        <span>{{ request()->getSchemeAndHttpHost() }}/wholesaler/</span><input name="slug" type="text" value="{{ old('slug') }}" pattern="[A-Za-z0-9-]+" required data-shop-slug>
                    </label>
                    <p>Generated from your shop name. You can edit it; use letters, numbers, and hyphens only.</p>
                    @error('slug')<p>{{ $message }}</p>@enderror

                    <label>
                        Password
                        <input name="seller_password" type="password" required autocomplete="new-password">
                    </label>
                    @error('seller_password')<p>{{ $message }}</p>@enderror

                    <label>
                        Confirm password
                        <input name="seller_password_confirmation" type="password" required autocomplete="new-password">
                    </label>

                    <button class="primary">Submit application</button>
                </form>

                <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
            </div>
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
