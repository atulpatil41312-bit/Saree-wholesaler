@extends('layouts.storefront')

@section('content')
    <main><section class="contact"><div><div class="eyebrow">EMAIL VERIFICATION</div><h2>Check your inbox</h2><p>We sent a verification link to {{ auth()->user()->email }}. Local development writes the message to the mail log.</p>@if(session('status'))<p>{{ session('status') }}</p>@endif<form method="post" action="{{ route('verification.send') }}">@csrf<button class="primary">Send another link</button></form><p><a href="{{ route('seller.dashboard') }}">Continue to dashboard</a></p></div></section></main>
@endsection
