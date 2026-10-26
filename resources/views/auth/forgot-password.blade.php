@extends('layouts.storefront')

@section('body_class', 'auth-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/auth.css') }}?v={{ filemtime(public_path('storefront/auth.css')) }}">
@endpush

@section('content')
    <main class="auth-shell">
        <aside class="auth-visual">
            <a class="auth-brand" href="{{ route('market.index') }}"><span class="auth-mark">W</span><span><strong>WholeMarket</strong><small>Wholesale made simple</small></span></a>
            <div class="auth-visual-copy"><p class="auth-overline">Account recovery</p><h1>Let’s get you back to your catalogue.</h1><p>We’ll send a secure link to the email on your account. Your shop and products stay exactly as they are.</p></div>
            <img class="auth-visual-image" src="{{ asset('storefront/saree0.jpg') }}" alt="Saree catalogue preview">
        </aside>
        <section class="auth-card" aria-labelledby="recovery-title">
            <div class="auth-card-header"><p class="auth-overline">Account recovery</p><h2 id="recovery-title">Reset your password</h2><p class="auth-card-intro">Enter the email address linked to your WholeMarket account. In local development, the reset link is written to the mail log.</p></div>
            @if(session('status'))<div class="auth-alert" role="status">{{ session('status') }}</div>@endif
            <form class="auth-form" method="post" action="{{ route('password.email') }}">
                @csrf
                <label>Email address<input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></label>
                @error('email')<p class="auth-error">{{ $message }}</p>@enderror
                <button class="auth-primary" type="submit">Send reset link <span aria-hidden="true">→</span></button>
            </form>
            <div class="auth-footer"><a href="{{ route('login') }}">← Return to sign in</a></div>
        </section>
    </main>
@endsection
