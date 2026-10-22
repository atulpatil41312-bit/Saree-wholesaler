@extends('layouts.storefront')

@section('content')
    <main><section class="contact"><div><div class="eyebrow">ACCOUNT RECOVERY</div><h2>Choose a new password</h2><form method="post" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><label>Email<input name="email" type="email" value="{{ old('email') }}" required></label><label>New password<input name="password" type="password" minlength="12" required></label><label>Confirm new password<input name="password_confirmation" type="password" minlength="12" required></label>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach<button class="primary">Reset password</button></form></div></section></main>
@endsection
