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
                <p class="auth-overline">Access protected</p>
                <h1>Your shop stays safe and in control.</h1>
                <p>Some storefront actions are available only while your shop has an active plan and the right permissions.</p>
                <ul class="auth-benefits">
                    <li>Catalogue data remains unchanged</li>
                    <li>Subscriptions keep access predictable</li>
                    <li>Every shop stays isolated and secure</li>
                </ul>
            </div>
            <img class="auth-visual-image" src="{{ asset('storefront/saree2.jpg') }}" alt="Saree catalogue preview">
        </aside>

        <section class="auth-card" aria-labelledby="forbidden-title">
            <div class="auth-card-header">
                <p class="auth-overline">403 · Permission needed</p>
                <h2 id="forbidden-title">This design is locked</h2>
                <p class="auth-card-intro">Your current plan or shop status does not allow this preview yet. An administrator can activate or renew access without changing your catalogue.</p>
            </div>
            <div class="auth-alert auth-alert-error" role="alert">Preview and apply actions are protected by your shop’s subscription and permissions.</div>
            <div class="auth-form">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a class="auth-primary" href="{{ route('admin.subscriptions.index') }}">Manage subscriptions <span aria-hidden="true">→</span></a>
                    @else
                        <a class="auth-primary" href="{{ route('seller.design') }}">Return to design studio <span aria-hidden="true">→</span></a>
                    @endif
                @else
                    <a class="auth-primary" href="{{ route('login') }}">Sign in to continue <span aria-hidden="true">→</span></a>
                @endauth
                <a class="auth-footer" href="{{ route('market.index') }}">Return to marketplace</a>
            </div>
        </section>
    </main>
@endsection
