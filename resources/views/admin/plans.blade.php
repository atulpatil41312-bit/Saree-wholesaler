@extends('layouts.storefront')

@section('content')
    <header>
        <a class="brand" href="{{ route('admin.dashboard') }}"><span class="emblem">W</span><span>WHOLEMARKET<small>PLAN MANAGEMENT</small></span></a>
        <nav><a href="{{ route('admin.dashboard') }}">Dashboard</a><a href="{{ route('admin.subscriptions.index') }}">Subscriptions</a></nav>
        <form method="post" action="{{ route('logout') }}">@csrf<button>Logout</button></form>
    </header>
    <main>
        <section class="collection">
            <div class="eyebrow">PLANS & ENTITLEMENTS</div>
            <h2>Plan catalogue</h2>
            @if (session('status'))<p>{{ session('status') }}</p>@endif
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            <h3>Add plan</h3>
            <form method="post" action="{{ route('admin.plans.store') }}">
                @csrf
                <label>Key<input name="key" placeholder="seasonal-pro" required></label>
                <label>Name<input name="name" required></label>
                <label>Shop type<select name="shop_type"><option value="wholesaler">Wholesaler</option><option value="retailer">Retailer</option></select></label>
                <label>Sort order<input name="sort_order" type="number" min="0" value="0"></label>
                <label><input name="signup_enabled" type="checkbox" value="1"> Available at signup</label>
                <button class="primary">Add plan</button>
            </form>

            <div class="products">
                @foreach ($plans as $plan)
                    <article class="product">
                        <div class="product-code">{{ strtoupper($plan->shop_type) }} · {{ $plan->is_active ? 'ACTIVE' : 'INACTIVE' }}</div>
                        <h3>{{ $plan->name }}</h3>
                        <p>Key: {{ $plan->key }}</p>
                        <form method="post" action="{{ route('admin.plans.update', $plan) }}">
                            @csrf @method('patch')
                            <label>Name<input name="name" value="{{ $plan->name }}" required></label>
                            <label>Sort order<input name="sort_order" type="number" min="0" value="{{ $plan->sort_order }}"></label>
                            <label><input name="signup_enabled" type="checkbox" value="1" @checked($plan->signup_enabled)> Available at signup</label>
                            <label><input name="is_active" type="checkbox" value="1" @checked($plan->is_active)> Active</label>
                            <button>Save plan</button>
                        </form>
                        <h4>Versions</h4>
                        @foreach($plan->versions as $version)
                            <p>v{{ $version->version }} · {{ $version->billing_interval }} · {{ $version->price_paise === null ? 'Price not configured' : '₹'.number_format($version->price_paise / 100, 2) }}<br>Limits: {{ json_encode($version->limits) }}<br>Templates: {{ is_array($version->template_keys) ? implode(', ', $version->template_keys) : $version->template_keys }}</p>
                        @endforeach
                        <form method="post" action="{{ route('admin.plans.versions.store', $plan) }}">
                            @csrf
                            <label>Price in paise (leave blank for manual)<input name="price_paise" type="number" min="0"></label>
                            <label>Billing interval<select name="billing_interval"><option>monthly</option><option>quarterly</option><option>yearly</option><option>manual</option></select></label>
                            <label>Limits JSON<textarea name="limits" rows="4" required>{"product_limit":30,"category_limit":5,"product_image_limit":2,"staff_limit":0,"sponsored_retailer_limit":0}</textarea></label>
                            <label>Template keys<input name="template_keys" value="classic" required></label>
                            <button>Add version</button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
    </main>
@endsection
