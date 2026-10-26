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
                <p class="auth-overline">A calmer way to sell</p>
                <h1>Your catalogue, beautifully presented.</h1>
                <p>Manage your shop, showcase every saree, and stay connected with buyers from one focused workspace.</p>
                <ul class="auth-benefits">
                    <li>One dashboard for your whole catalogue</li>
                    <li>Professional storefronts ready to share</li>
                    <li>Simple tools for enquiries and growth</li>
                </ul>
            </div>
            <img class="auth-visual-image" src="{{ asset('storefront/saree2.jpg') }}" alt="Saree catalogue preview">
        </aside>

        <section class="auth-card" aria-labelledby="login-title">
            <div class="auth-card-header">
                <p class="auth-overline">WholeMarket access</p>
                <h2 id="login-title">Welcome back</h2>
                <p class="auth-card-intro">Sign in to continue managing your shop and catalogue.</p>
                <div class="auth-role-list" aria-label="Available account types"><span class="auth-role">Seller</span><span class="auth-role">Retailer</span><span class="auth-role">Admin</span></div>
            </div>

            @if (session('status'))
                <div class="auth-alert" role="status">{{ session('status') }}</div>
            @endif

            <form class="auth-form" method="post" action="{{ route('login.store') }}">
                @csrf
                <label>Email address
                    <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </label>
                @error('email')<p class="auth-error">{{ $message }}</p>@enderror

                <label>Password
                    <input name="password" type="password" autocomplete="current-password" required>
                </label>
                @error('password')<p class="auth-error">{{ $message }}</p>@enderror

                <label class="auth-check"><input type="checkbox" name="remember"> Remember me on this device</label>
                <button class="auth-primary" type="submit">Sign in <span aria-hidden="true">→</span></button>
            </form>

            <div class="auth-footer">
                <a href="{{ route('password.request') }}">Forgot your password?</a>
                <span>New seller? <a href="{{ route('seller.signup') }}">Apply for an account</a></span>
            </div>
        </section>
    </main>
@endsection
