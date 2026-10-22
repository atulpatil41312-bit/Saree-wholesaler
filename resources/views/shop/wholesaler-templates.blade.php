@extends('layouts.storefront')

@section('body_class', 'wholesaler-templates')

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/wholesaler-templates/style.css') }}">
@endpush

@section('content')
    @php
        $settings = $shop->settings;
        $sections = is_array($settings?->sections) ? $settings->sections : [];
        $availableThemes = ['heritage', 'ruby', 'indigo'];
        $availableFonts = ['editorial', 'classic', 'modern'];
        $colourKey = in_array($settings?->colour_key, $availableThemes, true) ? $settings->colour_key : 'heritage';
        $fontKey = in_array($settings?->font_key, $availableFonts, true) ? $settings->font_key : 'editorial';
        $defaultOrder = ['featured', 'story', 'contact'];
        $sectionOrder = $sections['order'] ?? $defaultOrder;
        if (! is_array($sectionOrder) || count($sectionOrder) !== count($defaultOrder) || count(array_unique($sectionOrder)) !== count($defaultOrder) || array_diff($sectionOrder, $defaultOrder) !== []) {
            $sectionOrder = $defaultOrder;
        }
        $visibility = is_array($sections['visible'] ?? null) ? $sections['visible'] : [];
        $showFeatured = $visibility['featured'] ?? true;
        $showStory = $visibility['story'] ?? true;
        $showContact = $visibility['contact'] ?? true;
        $showPrices = $settings?->show_prices ?? true;
        $assetBase = asset('templates/wholesaler-templates');
        $shopUrl = $shop->publicUrl();
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', $template) : $shopUrl;
        $heroImage = $shop->banner_path ? Storage::url($shop->banner_path) : $assetBase.'/placeholder-hero.svg';
        $heroTitle = ($sections['hero_title'] ?? null) ?: $shop->name;
        $heroSubtitle = ($sections['hero_subtitle'] ?? null) ?: ($shop->description ?: 'A focused wholesale saree catalogue for retailers who value clear details and direct sourcing.');
        $heroCtaLabel = ($sections['hero_cta_label'] ?? null) ?: 'Explore catalogue';
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $hasWhatsapp = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1;
        $whatsappUrl = $hasWhatsapp ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shopUrl) : null;
        $phoneNumber = preg_replace('/\D/', '', (string) $shop->phone);
        $phoneUrl = preg_match('/^\d{8,15}$/', $phoneNumber) === 1 ? 'tel:'.$phoneNumber : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
        $instagramUrl = filter_var($shop->instagram_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->instagram_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->instagram_url : null;
        $facebookUrl = filter_var($shop->facebook_url, FILTER_VALIDATE_URL) && in_array(parse_url($shop->facebook_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->facebook_url : null;
    @endphp

    @if (! empty($previewingTemplate))
        <div class="wta-preview-notice" role="status">
            Previewing Wholesale Atelier. <a href="{{ route('seller.design') }}">Return to template selection</a>
        </div>
    @endif

    <div class="wta-shell" data-theme="{{ $colourKey }}" data-font="{{ $fontKey }}">
        <a class="wta-skip-link" href="#catalogue">Skip to catalogue</a>

        <div class="wta-utility" aria-label="Store information">
            <span>WHOLESALE CATALOGUE</span>
            <span>DIRECT BUYER ENQUIRIES</span>
            @if ($shop->market_category)
                <span>{{ Str::upper($shop->market_category) }}</span>
            @endif
        </div>

        <header class="wta-header">
            <a class="wta-brand" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} homepage">
                @if ($shop->logo_path)
                    <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="96" height="96">
                @else
                    <span class="wta-monogram" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                @endif
                <span>
                    <strong>{{ $shop->name }}</strong>
                    <small>{{ $shop->isRetailer() ? 'RETAILER STORE' : 'WHOLESALE STUDIO' }}</small>
                </span>
            </a>

            <nav class="wta-nav" aria-label="Primary navigation">
                <a href="#catalogue">Catalogue</a>
                @if ($showStory)
                    <a href="#about">About</a>
                @endif
                @if ($showContact)
                    <a href="#contact">Contact</a>
                @endif
                <a href="{{ route('market.index') }}">WholeMarket</a>
            </nav>

            <details class="wta-mobile-menu">
                <summary><span class="wta-visually-hidden">Open navigation</span>Menu</summary>
                <nav aria-label="Mobile navigation">
                    <a href="#catalogue">Catalogue</a>
                    @if ($showStory)
                        <a href="#about">About</a>
                    @endif
                    @if ($showContact)
                        <a href="#contact">Contact</a>
                    @endif
                    <a href="{{ route('market.index') }}">WholeMarket</a>
                </nav>
            </details>
        </header>

        <main>
            <section class="wta-hero" aria-labelledby="wta-hero-title">
                <div class="wta-hero-copy">
                    <p class="wta-kicker">WHOLESALE ATELIER</p>
                    <h1 id="wta-hero-title">{{ $heroTitle }}</h1>
                    <p class="wta-lead">{{ $heroSubtitle }}</p>
                    <div class="wta-actions">
                        <a class="wta-button" href="#catalogue">{{ $heroCtaLabel }} <span aria-hidden="true">→</span></a>
                        @if ($whatsappUrl)
                            <a class="wta-text-link" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Talk on WhatsApp <span aria-hidden="true">↗</span></a>
                        @endif
                    </div>
                    <dl class="wta-hero-facts">
                        <div><dt>{{ $products->total() }}</dt><dd>catalogue pieces</dd></div>
                        <div><dt>{{ $categories->count() }}</dt><dd>collections</dd></div>
                        <div><dt>{{ $shop->isRetailer() ? 'Retail' : 'Wholesale' }}</dt><dd>store type</dd></div>
                    </dl>
                </div>
                <div class="wta-hero-art">
                    <img src="{{ $heroImage }}" alt="{{ $shop->name }} collection" width="1200" height="1440" fetchpriority="high">
                    <div class="wta-hero-stamp"><span>CURATED</span><strong>01</strong><span>SOURCING</span></div>
                </div>
            </section>

            @foreach ($sectionOrder as $section)
                @if ($section === 'featured' && $showFeatured)
                    <section class="wta-section wta-featured" aria-labelledby="featured-collections-title">
                        <div class="wta-section-heading">
                            <div>
                                <p class="wta-kicker">SHOP BY COLLECTION</p>
                                <h2 id="featured-collections-title">Made for your next edit.</h2>
                            </div>
                            <a class="wta-text-link" href="#catalogue">View all sarees <span aria-hidden="true">→</span></a>
                        </div>

                        <div class="wta-collection-grid">
                            @forelse ($categories->take(4) as $category)
                                @php
                                    $categoryUrl = ! empty($previewingTemplate)
                                        ? route('seller.design.preview', ['template' => $template, 'category' => $category->id]).'#catalogue'
                                        : $shop->publicUrl(['category' => $category->id]).'#catalogue';
                                @endphp
                                <a class="wta-collection-card" href="{{ $categoryUrl }}">
                                    <img src="{{ $assetBase.'/placeholder-product.svg' }}" alt="" width="640" height="760" loading="lazy">
                                    <span>{{ $category->name }}</span>
                                    <small>Explore collection <b aria-hidden="true">→</b></small>
                                </a>
                            @empty
                                <div class="wta-empty-card">
                                    <p class="wta-kicker">COLLECTIONS ARRIVING SOON</p>
                                    <p>This shop has not grouped its catalogue yet. Browse the latest sarees below.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @elseif ($section === 'story' && $showStory)
                    <section id="about" class="wta-section wta-story" aria-labelledby="about-title">
                        <div class="wta-story-panel">
                            <p class="wta-kicker">ABOUT THE BUSINESS</p>
                            <h2 id="about-title">A clear catalogue, a direct relationship.</h2>
                            <p>{{ $shop->description ?: 'Use this space to introduce your sourcing approach, speciality fabrics and the kinds of collections you make available to buyers.' }}</p>
                            @if ($shop->address)
                                <p class="wta-address">{{ $shop->shop_number ? $shop->shop_number.', ' : '' }}{{ $shop->address }}</p>
                            @endif
                        </div>
                        <div class="wta-story-notes" aria-label="Shop details">
                            <div><span>01</span><p>Independent shop profile</p></div>
                            <div><span>02</span><p>Current product information</p></div>
                            <div><span>03</span><p>Direct seller communication</p></div>
                        </div>
                    </section>
                @elseif ($section === 'contact' && $showContact)
                    <section id="contact" class="wta-section wta-contact" aria-labelledby="contact-title">
                        <div>
                            <p class="wta-kicker">START A CONVERSATION</p>
                            <h2 id="contact-title">Tell {{ $shop->name }} what you are sourcing.</h2>
                            <p>Ask about availability, colours, minimum quantities or a specific catalogue piece.</p>
                            <div class="wta-contact-actions">
                                @if ($whatsappUrl)
                                    <a class="wta-button" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp enquiry <span aria-hidden="true">↗</span></a>
                                @endif
                                @if ($shopEmail)
                                    <a class="wta-text-link" href="mailto:{{ $shopEmail }}">Email the shop <span aria-hidden="true">→</span></a>
                                @endif
                                @if ($phoneUrl)
                                    <a class="wta-text-link" href="{{ $phoneUrl }}">Call {{ $shop->phone }} <span aria-hidden="true">→</span></a>
                                @endif
                            </div>
                            @if ($instagramUrl || $facebookUrl)
                                <div class="wta-social-links" aria-label="Social links">
                                    @if ($instagramUrl)
                                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener"><span aria-hidden="true">◎</span> Instagram</a>
                                    @endif
                                    @if ($facebookUrl)
                                        <a href="{{ $facebookUrl }}" target="_blank" rel="noopener"><span aria-hidden="true">f</span> Facebook</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <form class="wta-enquiry-form" method="post" action="{{ route('enquiries.store', ['type' => $shop->type, 'shop' => $shop]) }}">
                            @csrf
                            @if (session('status'))
                                <p class="wta-form-status" role="status">{{ session('status') }}</p>
                            @endif
                            @if ($errors->any())
                                <p class="wta-form-status" role="alert">Please check the enquiry details and try again.</p>
                            @endif
                            <label>Name<input name="name" value="{{ old('name') }}" autocomplete="name" required></label>
                            <label>Email<input name="email" type="email" value="{{ old('email') }}" autocomplete="email"></label>
                            <label>Phone<input name="phone" value="{{ old('phone') }}" autocomplete="tel"></label>
                            <label>What are you looking for?<textarea name="message" rows="4" required>{{ old('message') }}</textarea></label>
                            <button class="wta-button" type="submit">Send enquiry <span aria-hidden="true">→</span></button>
                        </form>
                    </section>
                @endif
            @endforeach

            <section id="catalogue" class="wta-section wta-catalogue" aria-labelledby="catalogue-title">
                <div class="wta-section-heading">
                    <div>
                        <p class="wta-kicker">THE CATALOGUE</p>
                        <h2 id="catalogue-title">Browse at your own pace.</h2>
                    </div>
                    <p>{{ $products->total() }} {{ Str::plural('piece', $products->total()) }} listed</p>
                </div>

                <form class="wta-filters" method="get" action="{{ $collectionUrl }}">
                    <label><span>Search</span><input name="q" value="{{ request('q') }}" placeholder="Name or design code"></label>
                    <label><span>Collection</span><select name="category"><option value="">All collections</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                    <label><span>Fabric</span><select name="fabric"><option value="">All fabrics</option>@foreach ($fabrics as $fabric)<option value="{{ $fabric->id }}" @selected((int) request('fabric') === $fabric->id)>{{ $fabric->name }}</option>@endforeach</select></label>
                    <label><span>Colour</span><select name="colour"><option value="">All colours</option>@foreach ($colours as $colour)<option value="{{ $colour }}" @selected(request('colour') === $colour)>{{ $colour }}</option>@endforeach</select></label>
                    <label><span>Sort</span><select name="sort"><option value="">Newest first</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select></label>
                    <button type="submit">Apply filters</button>
                </form>

                <div class="wta-product-grid">
                    @forelse ($products as $product)
                        @php
                            $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/placeholder-product.svg';
                            $productUrl = $shop->productUrl($product);
                            $productWhatsappUrl = $hasWhatsapp
                                ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Product link: '.$productUrl.'. Shop link: '.$shopUrl)
                                : null;
                        @endphp
                        <article class="wta-product-card">
                            <a class="wta-product-image" href="{{ $productUrl }}">
                                <img src="{{ $productImage }}" alt="{{ $product->name }}" width="720" height="960" loading="lazy">
                                <span>{{ Str::headline($product->availability) }}</span>
                            </a>
                            <div class="wta-product-copy">
                                <p>{{ $product->code }}</p>
                                <h3><a href="{{ $productUrl }}">{{ $product->name }}</a></h3>
                                <dl>
                                    @if ($product->fabric)<div><dt>Fabric</dt><dd>{{ $product->fabric->name }}</dd></div>@endif
                                    @if ($product->colour)<div><dt>Colour</dt><dd>{{ $product->colour }}</dd></div>@endif
                                </dl>
                                <div class="wta-product-meta">
                                    @if ($showPrices && $product->priceInRupees() !== null)
                                        <strong>₹{{ number_format($product->priceInRupees(), 2) }}</strong>
                                    @else
                                        <strong>Price on request</strong>
                                    @endif
                                    <span>MOQ {{ $product->minimum_order_quantity }}</span>
                                </div>
                                <div class="wta-product-actions">
                                    <a href="{{ $productUrl }}">View details <span aria-hidden="true">→</span></a>
                                    @if ($productWhatsappUrl)
                                        <a href="{{ $productWhatsappUrl }}" target="_blank" rel="noopener">WhatsApp <span aria-hidden="true">↗</span></a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="wta-empty-state">
                            <p class="wta-kicker">NOTHING MATCHED</p>
                            <h3>There are no sarees in this filtered view yet.</h3>
                            <a class="wta-text-link" href="{{ $collectionUrl }}#catalogue">Clear filters <span aria-hidden="true">→</span></a>
                        </div>
                    @endforelse
                </div>

                @if ($products->hasPages())
                    <div class="wta-pagination">{{ $products->onEachSide(1)->links() }}</div>
                @endif
            </section>
        </main>

        <footer class="wta-footer">
            <a class="wta-brand" href="{{ $shopUrl }}">
                <span class="wta-monogram" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                <span><strong>{{ $shop->name }}</strong><small>WHOLESALE ATELIER</small></span>
            </a>
            <p>Independent catalogue on <a href="{{ route('market.index') }}">WholeMarket</a>.</p>
        </footer>

        @if ($whatsappUrl)
            <a class="wta-whatsapp-float" href="{{ $whatsappUrl }}" target="_blank" rel="noopener" aria-label="Enquire with {{ $shop->name }} on WhatsApp">
                <span aria-hidden="true">◔</span><span>WhatsApp</span>
            </a>
        @endif
    </div>
@endsection
