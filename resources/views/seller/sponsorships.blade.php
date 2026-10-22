@extends('layouts.storefront')

@section('content')
    <header>
        <a class="brand" href="{{ route('seller.dashboard') }}"><span class="emblem">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span><span>{{ Str::upper($shop->name) }}<small>RETAILER SPONSORS</small></span></a>
        <nav><a href="{{ route('seller.dashboard') }}">Dashboard</a><a href="{{ route('seller.profile.edit') }}">Profile</a></nav>
        <form method="post" action="{{ route('logout') }}">@csrf<button>Logout</button></form>
    </header>
    <main>
        <section class="collection">
            <div class="eyebrow">PRO WHOLESALER BENEFIT</div>
            <h2>Retailer sponsorships</h2>
            <p>{{ $activeCount }}/{{ $slotLimit }} active sponsor slots used.</p>
            @if (session('status'))<p>{{ session('status') }}</p>@endif
            @if (session('invitation_link'))<p>Local invitation link: <a href="{{ session('invitation_link') }}">{{ session('invitation_link') }}</a></p>@endif
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach

            @if ($slotLimit > 0)
                <form method="post" action="{{ route('seller.sponsorships.store') }}">
                    @csrf
                    <label>Retailer email<input name="email" type="email" required></label>
                    <label>Sponsored term<select name="term_days">@foreach ($terms as $term)<option value="{{ $term }}">{{ $term }} days</option>@endforeach</select></label>
                    <button class="primary">Prepare invitation</button>
                </form>
            @else
                <p>Retailer sponsorships require an active Pro wholesaler subscription. Ask an administrator to assign or renew Pro.</p>
            @endif

            <div class="products">
                @forelse ($sponsorships as $sponsorship)
                    <article class="product">
                        <div class="product-code">{{ strtoupper($sponsorship->status) }}</div>
                        <h3>{{ $sponsorship->retailerShop?->name ?? 'Retailer shop' }}</h3>
                        <p>Ends {{ $sponsorship->expires_at->format('d M Y') }}</p>
                        @if($sponsorship->grace_until)<p>Grace ends {{ $sponsorship->grace_until->format('d M Y') }}</p>@endif
                    </article>
                @empty
                    <p>No retailer websites are currently sponsored.</p>
                @endforelse
            </div>

            <h3>Invitation history</h3>
            <div class="products">
                @forelse ($invites as $invite)
                    <article class="product">
                        <div class="product-code">{{ strtoupper($invite->status) }}</div>
                        <h3>{{ $invite->email }}</h3>
                        <p>{{ $invite->term_days }} days · expires {{ $invite->expires_at->format('d M Y') }}</p>
                        @if ($invite->status === 'pending')
                            <form method="post" action="{{ route('seller.sponsorships.revoke', $invite) }}">@csrf @method('delete')<button>Revoke invitation</button></form>
                        @endif
                    </article>
                @empty
                    <p>No invitations sent.</p>
                @endforelse
            </div>
        </section>
    </main>
@endsection
