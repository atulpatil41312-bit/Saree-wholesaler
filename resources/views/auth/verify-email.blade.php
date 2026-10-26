@extends('layouts.storefront')

@section('body_class', 'auth-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/auth.css') }}?v={{ filemtime(public_path('storefront/auth.css')) }}">
@endpush

@section('content')
    <main class="auth-shell">
        <aside class="auth-visual">
            <a class="auth-brand" href="{{ route('market.index') }}"><span class="auth-mark">W</span><span><strong>WholeMarket</strong><small>Wholesale made simple</small></span></a>
            <div class="auth-visual-copy"><p class="auth-overline">One quick step</p><h1>Keep your account secure.</h1><p>Verified email helps us protect your shop, enquiries, and customer conversations.</p></div>
            <img class="auth-visual-image" src="{{ asset('storefront/saree1.jpg') }}" alt="Saree catalogue preview">
        </aside>
        <section class="auth-card" aria-labelledby="verify-title">
            <div class="auth-card-header"><p class="auth-overline">Email verification</p><h2 id="verify-title">Check your inbox</h2><p class="auth-card-intro">We sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Local development writes the message to the mail log.</p></div>
            @if(session('status'))<div class="auth-alert" role="status">{{ session('status') }}</div>@endif
            <form class="auth-form" method="post" action="{{ route('verification.send') }}">
                @csrf
                <button class="auth-primary" type="submit">Send another link <span aria-hidden="true">→</span></button>
            </form>
            <div class="auth-footer"><a href="{{ route('seller.dashboard') }}">Continue to dashboard →</a></div>
        </section>
    </main>
@endsection
