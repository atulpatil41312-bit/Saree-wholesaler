@extends('layouts.storefront')

@section('content')
    <header>
        <a class="brand" href="{{ route('seller.dashboard') }}">
            <span class="emblem">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
            <span>{{ Str::upper($shop->name) }}<small>{{ $shop->isRetailer() ? 'RETAILER DASHBOARD' : 'WHOLESALER DASHBOARD' }}</small></span>
        </a>
        <nav>
            <a href="{{ route('seller.profile.edit') }}">Shop profile</a>
            <a href="{{ route('seller.design') }}">Design</a>
            <a href="{{ route('seller.enquiries.index') }}">Enquiries</a>
            @if ($shop->isWholesaler())
                <a href="{{ route('seller.sponsorships.index') }}">Retailer sponsors</a>
            @endif
        </nav>
        <form method="post" action="{{ route('logout') }}">@csrf<button>Logout</button></form>
    </header>

    <main>
        <section class="collection">
            <div class="eyebrow">{{ $shop->isRetailer() ? 'RETAILER WEBSITE' : 'WHOLESALE CATALOGUE' }}</div>
            <h2>Your catalogue</h2>
            <p>Shop URL: <a href="{{ $shop->publicUrl() }}">{{ $shop->publicUrl() }}</a></p>
            <p>
                Plan: <strong>{{ $subscription?->plan?->name ?? 'Legacy access' }}</strong>
                · Status: {{ $shop->suspended_at ? 'Suspended' : $shop->status }}
            </p>
            <p>
                Usage: {{ $shop->categories()->count() }}/{{ $limits['category_limit'] }} categories
                · {{ $shop->products()->count() }}/{{ $limits['product_limit'] }} products
                · {{ $limits['product_image_limit'] }} images per product
            </p>

            @if (session('status'))<p>{{ session('status') }}</p>@endif
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach

            <div class="products">
                <article class="product">
                    <div class="product-code">CATALOGUE SETUP</div>
                    <h3>Add a category</h3>
                    <form method="post" action="{{ route('seller.categories.store') }}">
                        @csrf
                        <label>Category name<input name="name" placeholder="Wedding sarees" required></label>
                        <button>Create category</button>
                    </form>
                </article>

                <article class="product">
                    <div class="product-code">NEW PRODUCT</div>
                    <h3>Add a saree</h3>
                    @if ($shop->categories->isEmpty())
                        <p>Create a category first.</p>
                    @else
                        <form method="post" action="{{ route('seller.products.store') }}">
                            @csrf
                            <label>Category<select name="category_id" required>@foreach($shop->categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label>
                            <label>Product name<input name="name" placeholder="Royal silk saree" required></label>
                            <label>Design code<input name="code" placeholder="RS-001" required></label>
                            <label>Wholesale price (₹)<input name="wholesale_price" inputmode="decimal" placeholder="1250"></label>
                            <label>Minimum order quantity<input name="minimum_order_quantity" type="number" min="1" value="1" required></label>
                            <label>Pack size (optional)<input name="pack_size" type="number" min="1" placeholder="6"></label>
                            <label>Availability<select name="availability"><option value="available">Available</option><option value="out_of_stock">Out of stock</option><option value="on_enquiry">On enquiry</option></select></label>
                            <button class="primary">Add product</button>
                        </form>
                    @endif
                </article>

                <article class="product">
                    <div class="product-code">SHOP TOOLS</div>
                    <h3>Present your business</h3>
                    <p>Update your contact details, public address, social links and search description.</p>
                    <a class="details-btn" href="{{ route('seller.profile.edit') }}">Edit shop profile <span>↗</span></a>
                    <a class="details-btn" href="{{ route('seller.design') }}">Choose design <span>↗</span></a>
                    <a class="details-btn" href="{{ $shop->publicUrl() }}">Preview storefront <span>↗</span></a>
                </article>

                @if ($shop->isWholesaler())
                    <article class="product">
                        <div class="product-code">RETAILER SPONSORS</div>
                        <h3>{{ $sponsorships->count() }} retailer sponsorships</h3>
                        <p>Retailer sponsor slots are available on the Pro plan.</p>
                        <a class="details-btn" href="{{ route('seller.sponsorships.index') }}">Manage retailer sponsorships <span>↗</span></a>
                    </article>
                @endif
            </div>
        </section>
    </main>
@endsection
