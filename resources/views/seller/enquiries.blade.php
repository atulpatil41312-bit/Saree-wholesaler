@extends('layouts.storefront')

@section('content')
    <header>
        <a class="brand" href="{{ route('seller.dashboard') }}"><span class="emblem">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span><span>{{ Str::upper($shop->name) }}<small>ENQUIRIES</small></span></a>
        <nav><a href="{{ route('seller.dashboard') }}">Dashboard</a><a href="{{ route('seller.profile.edit') }}">Profile</a></nav>
        <form method="post" action="{{ route('logout') }}">@csrf<button>Logout</button></form>
    </header>
    <main>
        <section class="collection">
            <div class="eyebrow">LEADS & FOLLOW-UP</div>
            <h2>Customer enquiries</h2>
            @if (session('status'))<p>{{ session('status') }}</p>@endif
            <div class="products">
                @forelse ($enquiries as $enquiry)
                    <article class="product">
                        <div class="product-code">{{ strtoupper($enquiry->status) }} · {{ $enquiry->created_at->format('d M Y') }}</div>
                        <h3>{{ $enquiry->name }}</h3>
                        <p>{{ $enquiry->product?->name ? 'Product: '.$enquiry->product->name : 'General shop enquiry' }}</p>
                        <p>{{ $enquiry->email ?: $enquiry->phone }}</p>
                        <p>{{ $enquiry->message }}</p>
                        <form method="post" action="{{ route('seller.enquiries.update', $enquiry) }}">
                            @csrf @method('patch')
                            <label>Follow-up status<select name="status"><option value="new" @selected($enquiry->status === 'new')>New</option><option value="contacted" @selected($enquiry->status === 'contacted')>Contacted</option><option value="closed" @selected($enquiry->status === 'closed')>Closed</option></select></label>
                            <button>Save status</button>
                        </form>
                    </article>
                @empty
                    <p>No enquiries yet.</p>
                @endforelse
            </div>
            {{ $enquiries->links() }}
        </section>
    </main>
@endsection
