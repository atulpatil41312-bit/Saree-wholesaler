@extends('layouts.storefront')

@section('body_class', 'seller-dashboard-page seller-design-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/seller-dashboard.css') }}?v={{ filemtime(public_path('storefront/seller-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('storefront/seller-design.css') }}?v={{ filemtime(public_path('storefront/seller-design.css')) }}">
@endpush

@section('content')
    <div class="seller-shell">
        <aside class="seller-sidebar">
            <a class="seller-brand" href="{{ route('seller.dashboard') }}" aria-label="{{ $shop->name }} dashboard">
                <span class="seller-brand-mark">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                <span><strong>{{ $shop->name }}</strong><small>{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</small></span>
            </a>
            <nav class="seller-nav" aria-label="Seller navigation">
                <span class="seller-nav-label">Workspace</span>
                <a class="seller-nav-link" href="{{ route('seller.dashboard') }}"><span aria-hidden="true">⌂</span>Overview</a>
                <a class="seller-nav-link" href="{{ route('seller.profile.edit') }}"><span aria-hidden="true">◉</span>Shop profile</a>
                <a class="seller-nav-link is-active" href="{{ route('seller.design') }}" aria-current="page"><span aria-hidden="true">◇</span>Design</a>
                <a class="seller-nav-link" href="{{ route('seller.enquiries.index') }}"><span aria-hidden="true">↗</span>Enquiries</a>
                @if ($shop->isWholesaler())
                    <a class="seller-nav-link" href="{{ route('seller.sponsorships.index') }}"><span aria-hidden="true">♡</span>Retailer sponsors</a>
                @endif
                <span class="seller-nav-label seller-nav-label-spaced">Shortcuts</span>
                <a class="seller-nav-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener"><span aria-hidden="true">↗</span>View storefront</a>
            </nav>
            <div class="seller-sidebar-footer">
                <div class="seller-user-chip"><span class="seller-user-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span></div>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="seller-signout" type="submit">Sign out <span aria-hidden="true">↗</span></button></form>
            </div>
        </aside>

        <div class="seller-workspace">
            <header class="seller-topbar">
                <div><p class="seller-overline">{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</p><p class="seller-breadcrumb">{{ $shop->name }} / Design studio</p></div>
                <a class="seller-store-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">Open storefront <span aria-hidden="true">↗</span></a>
            </header>

            <main class="seller-content design-content">
                @if (session('status'))
                    <div class="seller-alert seller-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="seller-alert seller-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="design-heading">
                    <div>
                        <p class="seller-overline">Storefront design</p>
                        <h1>Design studio</h1>
                        <p class="seller-page-intro">Choose a polished presentation for your catalogue. Preview each option before you apply it.</p>
                    </div>
                    <div class="design-heading-actions">
                        <span class="design-template-count"><strong>{{ count($templates) }}</strong> templates available</span>
                        <a class="seller-secondary-button" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">View live shop <span aria-hidden="true">↗</span></a>
                    </div>
                </section>

                <div class="design-info-callout" role="note">
                    <span class="design-info-icon" aria-hidden="true">i</span>
                    <div><strong>Your catalogue stays exactly the same.</strong><p>Changing the design only updates the public presentation. Your shop URL, categories, products, images and catalogue limits are safe.</p></div>
                </div>

                @if (! $designAccessActive)
                    <div class="design-access-callout" role="alert">
                        <span class="design-access-icon" aria-hidden="true">!</span>
                        <div><strong>Design access is paused</strong><p>Your current subscription has expired, is still pending, or this shop is not active. Renew or activate a plan to preview and apply storefront designs.</p></div>
                        <a class="seller-secondary-button" href="{{ route('seller.dashboard') }}">View plan status <span aria-hidden="true">→</span></a>
                    </div>
                @endif

                <section class="design-section" aria-labelledby="template-options-heading">
                    <div class="design-section-heading">
                        <div><p class="seller-overline">Choose a direction</p><h2 id="template-options-heading">Template options</h2></div>
                        <p>Pick the style that best fits your collection.</p>
                    </div>

                    <div class="design-template-grid">
                        @foreach ($templates as $template => $label)
                            @php($details = $templateDetails[$template])
                            @php($isActive = $template === $shop->storefrontTemplate())
                            @php($isAvailable = $templateAvailability[$template] ?? false)
                            <article class="design-template-card {{ $isActive ? 'is-active' : '' }} {{ $isAvailable ? '' : 'is-locked' }}">
                                <div class="design-template-media">
                                    <img src="{{ asset('templates/shared/ivory-party.webp') }}" alt="{{ $label }} storefront preview" loading="lazy">
                                    <span class="design-template-badge">{{ $details['badge'] }}</span>
                                    <span class="design-template-state {{ $isActive ? 'is-current' : '' }} {{ $isAvailable ? '' : 'is-locked' }}"><span aria-hidden="true">{{ $isActive ? '✓' : ($isAvailable ? '○' : '⚿') }}</span>{{ $isActive ? 'Active now' : ($isAvailable ? 'Available' : 'Renew to unlock') }}</span>
                                </div>
                                <div class="design-template-body">
                                    <div class="design-template-title-row"><h3>{{ $label }}</h3>@if ($isActive)<span class="design-check" aria-label="Currently selected">✓</span>@endif</div>
                                    <p>{{ $details['description'] }}</p>
                                    <div class="design-template-actions">
                                        @if ($isAvailable)
                                            <a class="design-preview-link" href="{{ route('seller.design.preview', $template) }}">Preview design <span aria-hidden="true">↗</span></a>
                                        @else
                                            <span class="design-locked-label"><span aria-hidden="true">⚿</span> Preview locked</span>
                                        @endif
                                        @if ($isActive)
                                            <span class="design-applied-label"><span aria-hidden="true">✓</span> Currently applied</span>
                                        @elseif ($isAvailable)
                                            <form method="post" action="{{ route('seller.design.update') }}">
                                                @csrf
                                                @method('put')
                                                <input type="hidden" name="template" value="{{ $template }}">
                                                <button class="design-apply-button" type="submit">Apply {{ $label }} <span aria-hidden="true">→</span></button>
                                            </form>
                                        @else
                                            <span class="design-locked-label">Renew plan to apply</span>
                                        @endif
                                    </div>
                                </div>
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
                    <section class="design-settings-panel" aria-labelledby="atelier-settings-heading">
                        <div class="design-settings-heading">
                            <div><p class="seller-overline">Wholesale Atelier</p><h2 id="atelier-settings-heading">Fine-tune your storefront</h2><p>Make the Atelier layout feel like your brand. These settings only affect the Wholesale Atelier template.</p></div>
                            <span class="design-settings-mark" aria-hidden="true">✦</span>
                        </div>
                        <form method="post" action="{{ route('seller.design.settings.update') }}" class="design-settings-form">
                            @csrf
                            @method('put')
                            <div class="design-settings-fields">
                                <label>Colour palette <span>Choose the mood for your storefront</span>
                                    <select name="colour_key">
                                        <option value="heritage" @selected(old('colour_key', $designSettings?->colour_key ?? 'heritage') === 'heritage')>Heritage clay</option>
                                        <option value="ruby" @selected(old('colour_key', $designSettings?->colour_key) === 'ruby')>Ruby rose</option>
                                        <option value="indigo" @selected(old('colour_key', $designSettings?->colour_key) === 'indigo')>Indigo loom</option>
                                    </select>
                                </label>
                                <label>Typography <span>Set the tone for your headlines</span>
                                    <select name="font_key">
                                        <option value="editorial" @selected(old('font_key', $designSettings?->font_key ?? 'editorial') === 'editorial')>Editorial serif</option>
                                        <option value="classic" @selected(old('font_key', $designSettings?->font_key) === 'classic')>Classic sans</option>
                                        <option value="modern" @selected(old('font_key', $designSettings?->font_key) === 'modern')>Modern contrast</option>
                                    </select>
                                </label>
                                <label>Hero title <span>Optional · defaults to your shop name</span><input name="hero_title" maxlength="100" value="{{ old('hero_title', $atelierSections['hero_title'] ?? '') }}" placeholder="Leave empty to use the shop name"></label>
                                <label>Hero button label <span>Optional · defaults to Explore catalogue</span><input name="hero_cta_label" maxlength="40" value="{{ old('hero_cta_label', $atelierSections['hero_cta_label'] ?? '') }}" placeholder="Explore catalogue"></label>
                                <label class="design-field-wide">Hero description <span>Optional · up to 280 characters</span><textarea name="hero_subtitle" rows="3" maxlength="280" placeholder="Leave empty to use the shop description">{{ old('hero_subtitle', $atelierSections['hero_subtitle'] ?? '') }}</textarea></label>
                                <label class="design-field-wide">Homepage section order <span>Choose which story the buyer sees first</span>
                                    <select name="section_order">
                                        <option value="featured-story-contact" @selected(old('section_order', $atelierOrder) === 'featured-story-contact')>Featured collections, story, contact</option>
                                        <option value="story-featured-contact" @selected(old('section_order', $atelierOrder) === 'story-featured-contact')>Story, featured collections, contact</option>
                                        <option value="featured-contact-story" @selected(old('section_order', $atelierOrder) === 'featured-contact-story')>Featured collections, contact, story</option>
                                    </select>
                                </label>
                            </div>
                            <div class="design-visibility-card">
                                <div><p class="design-visibility-kicker">Homepage visibility</p><h3>Keep the right sections in focus</h3><p>Toggle the content blocks shown on your Wholesale Atelier home page.</p></div>
                                <div class="design-switch-grid">
                                    <label class="design-switch"><input name="show_featured" type="checkbox" value="1" @checked(old('show_featured', $atelierVisible['featured'] ?? true))><span class="design-switch-track" aria-hidden="true"></span><span><strong>Featured collections</strong><small>Show your best categories</small></span></label>
                                    <label class="design-switch"><input name="show_story" type="checkbox" value="1" @checked(old('show_story', $atelierVisible['story'] ?? true))><span class="design-switch-track" aria-hidden="true"></span><span><strong>Business story</strong><small>Introduce your sourcing story</small></span></label>
                                    <label class="design-switch"><input name="show_contact" type="checkbox" value="1" @checked(old('show_contact', $atelierVisible['contact'] ?? true))><span class="design-switch-track" aria-hidden="true"></span><span><strong>Contact section</strong><small>Make enquiries easy</small></span></label>
                                    <label class="design-switch"><input name="show_prices" type="checkbox" value="1" @checked(old('show_prices', $designSettings?->show_prices ?? true))><span class="design-switch-track" aria-hidden="true"></span><span><strong>Wholesale prices</strong><small>Show prices when available</small></span></label>
                                </div>
                            </div>
                            <div class="design-settings-footer"><p>Your logo, banner, contact details and social links come from <a href="{{ route('seller.profile.edit') }}">Shop profile</a>.</p><button class="seller-primary-button" type="submit">Save Atelier settings <span aria-hidden="true">→</span></button></div>
                        </form>
                    </section>
                <?php } ?>
            </main>
        </div>
    </div>
@endsection
