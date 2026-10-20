@extends('layouts.storefront')

@section('content')
    <main>
        <section class="contact">
            <div>
                <div class="eyebrow">JOIN WHOLEMARKET</div>
                <h2>Apply to sell</h2>
                <p>Submit your details to create a seller account and a pending wholesale shop. An admin will review and activate it before it is shown in the market.</p>

                <form method="post" action="{{ route('seller.signup.store') }}">
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
                        <input name="name" type="text" value="{{ old('name') }}" required>
                    </label>
                    @error('name')<p>{{ $message }}</p>@enderror

                    <label>
                        Shop URL
                        <input name="slug" type="text" value="{{ old('slug') }}" pattern="[A-Za-z0-9-]+" required>
                    </label>
                    <p>Use letters, numbers, and hyphens only.</p>
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
