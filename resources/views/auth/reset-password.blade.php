@extends('layouts.storefront')

@section('body_class', 'auth-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/auth.css') }}?v={{ filemtime(public_path('storefront/auth.css')) }}">
@endpush

@section('content')
    <main class="auth-shell">
        <aside class="auth-visual">
            <a class="auth-brand" href="{{ route('market.index') }}"><span class="auth-mark">W</span><span><strong>WholeMarket</strong><small>Wholesale made simple</small></span></a>
            <div class="auth-visual-copy"><p class="auth-overline">Account recovery</p><h1>A fresh key for your next chapter.</h1><p>Choose a strong password and return to the workspace that keeps your catalogue moving.</p></div>
            <img class="auth-visual-image" src="{{ asset('storefront/saree2.jpg') }}" alt="Saree catalogue preview">
        </aside>
        <section class="auth-card" aria-labelledby="reset-title">
            <div class="auth-card-header"><p class="auth-overline">Account recovery</p><h2 id="reset-title">Choose a new password</h2><p class="auth-card-intro">Use at least 12 characters. You’ll be signed in after the reset is complete.</p></div>
            @if ($errors->any())<div class="auth-alert auth-alert-error" role="alert"><strong>Please check the details below.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form class="auth-form" method="post" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <label>Email address<input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></label>
                <label>New password<input name="password" type="password" minlength="12" autocomplete="new-password" required></label>
                <label>Confirm new password<input name="password_confirmation" type="password" minlength="12" autocomplete="new-password" required></label>
                <button class="auth-primary" type="submit">Reset password <span aria-hidden="true">→</span></button>
            </form>
        </section>
    </main>
@endsection
