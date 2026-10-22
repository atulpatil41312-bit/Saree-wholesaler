@extends('layouts.storefront')

@section('content')
    <main><section class="contact"><div><div class="eyebrow">ACCOUNT RECOVERY</div><h2>Reset your password</h2><p>Enter the email address linked to your WholeMarket account. In local development, the reset link is written to the mail log.</p>@if(session('status'))<p>{{ session('status') }}</p>@endif<form method="post" action="{{ route('password.email') }}">@csrf<label>Email<input name="email" type="email" value="{{ old('email') }}" required></label>@error('email')<p>{{ $message }}</p>@enderror<button class="primary">Send reset link</button></form><p><a href="{{ route('login') }}">Return to sign in</a></p></div></section></main>
@endsection
