@extends('layouts.storefront')

@section('content')
    <header>
        <a class="brand" href="{{ route('seller.dashboard') }}">
            <span class="emblem">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
            <span>{{ Str::upper($shop->name) }}<small>SELLER DASHBOARD</small></span>
        </a>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button>Logout</button>
        </form>
    </header>

    <main>
        <section class="collection">
            <div class="eyebrow">STOREFRONT DESIGN</div>
            <h2>Choose your shop template</h2>
            <p>Changing a template only changes the public presentation. Your shop details, categories, products and catalogue limits stay exactly as they are.</p>

            @if (session('status'))
                <p>{{ session('status') }}</p>
            @endif

            <div class="products">
                @foreach ($templates as $template => $label)
                    @php($details = $templateDetails[$template])
                    <article class="product">
                        <div class="product-image" aria-hidden="true">
                            <img src="{{ asset($details['asset_path'].'/'.$details['hero_image']) }}" alt="">
                            <div class="tag">{{ $details['badge'] }}</div>
                        </div>
                        <div class="product-code">{{ $template === $shop->storefrontTemplate() ? 'ACTIVE TEMPLATE' : 'AVAILABLE TEMPLATE' }}</div>
                        <h3>{{ $label }}</h3>
                        <p>{{ $details['description'] }}</p>
                        <a class="details-btn" href="{{ route('seller.design.preview', $template) }}">Preview design <span>↗</span></a>
                        @if ($template === $shop->storefrontTemplate())
                            <p><strong>Currently applied</strong></p>
                        @else
                            <form method="post" action="{{ route('seller.design.update') }}">
                                @csrf
                                @method('put')
                                <input type="hidden" name="template" value="{{ $template }}">
                                <button class="primary">Apply {{ $label }}</button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    </main>
@endsection
