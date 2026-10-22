@extends('layouts.storefront')

@section('content')
    <main>
        <section class="contact">
            <div>
                <div class="eyebrow">WHOLEMARKET</div>
                <h2>This shop is currently unavailable.</h2>
                <p>It may be awaiting approval, temporarily paused, or no longer available. Please return to the marketplace to discover active catalogues.</p>
                <a class="primary" href="{{ route('market.index') }}">Return to WholeMarket</a>
            </div>
        </section>
    </main>
@endsection
