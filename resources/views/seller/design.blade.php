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

        <?php if ($wholesaleAtelierAvailable) {
                $atelierSections = $designSettings?->sections ?? [];
                $atelierOrder = is_array($atelierSections['order'] ?? null)
                    ? implode('-', $atelierSections['order'])
                    : 'featured-story-contact';
                $atelierVisible = is_array($atelierSections['visible'] ?? null)
                    ? $atelierSections['visible']
                    : [];
        ?>

            <section class="collection">
                <div class="eyebrow">WHOLESALE ATELIER SETTINGS</div>
                <h2>Tailor the presentation</h2>
                <p>These allowlisted settings are used only by Wholesale Atelier. Your logo, banner, contact details and social links come from the shop profile.</p>

                <form method="post" action="{{ route('seller.design.settings.update') }}">
                    @csrf
                    @method('put')
                    <label>Colour palette
                        <select name="colour_key">
                            <option value="heritage" @selected(old('colour_key', $designSettings?->colour_key ?? 'heritage') === 'heritage')>Heritage clay</option>
                            <option value="ruby" @selected(old('colour_key', $designSettings?->colour_key) === 'ruby')>Ruby rose</option>
                            <option value="indigo" @selected(old('colour_key', $designSettings?->colour_key) === 'indigo')>Indigo loom</option>
                        </select>
                    </label>
                    <label>Typography
                        <select name="font_key">
                            <option value="editorial" @selected(old('font_key', $designSettings?->font_key ?? 'editorial') === 'editorial')>Editorial serif</option>
                            <option value="classic" @selected(old('font_key', $designSettings?->font_key) === 'classic')>Classic sans</option>
                            <option value="modern" @selected(old('font_key', $designSettings?->font_key) === 'modern')>Modern contrast</option>
                        </select>
                    </label>
                    <label>Hero title<input name="hero_title" maxlength="100" value="{{ old('hero_title', $atelierSections['hero_title'] ?? '') }}" placeholder="Leave empty to use the shop name"></label>
                    <label>Hero description<textarea name="hero_subtitle" rows="3" maxlength="280" placeholder="Leave empty to use the shop description">{{ old('hero_subtitle', $atelierSections['hero_subtitle'] ?? '') }}</textarea></label>
                    <label>Hero button label<input name="hero_cta_label" maxlength="40" value="{{ old('hero_cta_label', $atelierSections['hero_cta_label'] ?? '') }}" placeholder="Explore catalogue"></label>
                    <label>Homepage section order
                        <select name="section_order">
                            <option value="featured-story-contact" @selected(old('section_order', $atelierOrder) === 'featured-story-contact')>Featured collections, story, contact</option>
                            <option value="story-featured-contact" @selected(old('section_order', $atelierOrder) === 'story-featured-contact')>Story, featured collections, contact</option>
                            <option value="featured-contact-story" @selected(old('section_order', $atelierOrder) === 'featured-contact-story')>Featured collections, contact, story</option>
                        </select>
                    </label>
                    <label><input name="show_featured" type="checkbox" value="1" @checked(old('show_featured', $atelierVisible['featured'] ?? true))> Show featured collections</label>
                    <label><input name="show_story" type="checkbox" value="1" @checked(old('show_story', $atelierVisible['story'] ?? true))> Show business story</label>
                    <label><input name="show_contact" type="checkbox" value="1" @checked(old('show_contact', $atelierVisible['contact'] ?? true))> Show contact section</label>
                    <label><input name="show_prices" type="checkbox" value="1" @checked(old('show_prices', $designSettings?->show_prices ?? true))> Show wholesale prices when available</label>
                    <button class="primary">Save Wholesale Atelier settings</button>
                </form>
            </section>
        <?php } ?>
    </main>
@endsection
