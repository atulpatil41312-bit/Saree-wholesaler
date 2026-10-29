@extends('layouts.storefront')

@section('body_class', 'seller-dashboard-page seller-profile-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/seller-dashboard.css') }}?v={{ filemtime(public_path('storefront/seller-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('storefront/seller-profile.css') }}?v={{ filemtime(public_path('storefront/seller-profile.css')) }}">
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
                <a class="seller-nav-link is-active" href="{{ route('seller.profile.edit') }}"><span aria-hidden="true">◉</span>Shop profile</a>
                <a class="seller-nav-link" href="{{ route('seller.design') }}"><span aria-hidden="true">◇</span>Design</a>
                <a class="seller-nav-link" href="{{ route('seller.enquiries.index') }}"><span aria-hidden="true">↗</span>Enquiries</a>
                @if ($shop->isWholesaler())<a class="seller-nav-link" href="{{ route('seller.sponsorships.index') }}"><span aria-hidden="true">♡</span>Retailer sponsors</a>@endif
                <span class="seller-nav-label seller-nav-label-spaced">Shortcuts</span>
                <a class="seller-nav-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener"><span aria-hidden="true">↗</span>View storefront</a>
            </nav>
            <div class="seller-sidebar-footer"><div class="seller-user-chip"><span class="seller-user-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span></div><form method="post" action="{{ route('logout') }}">@csrf<button class="seller-signout" type="submit">Sign out <span aria-hidden="true">↗</span></button></form></div>
        </aside>

        <div class="seller-workspace">
            <header class="seller-topbar"><div><p class="seller-overline">{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</p><p class="seller-breadcrumb">{{ $shop->name }} / Shop profile</p></div><a class="seller-store-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">Open storefront <span aria-hidden="true">↗</span></a></header>
            <main class="seller-content profile-content">
                @if (session('status'))<div class="seller-alert seller-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>@endif
                @if ($errors->any())<div class="seller-alert seller-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

                <section class="seller-page-heading profile-page-heading"><div><p class="seller-overline">Public shop details</p><h1>Shop profile</h1><p class="seller-page-intro">Keep your public information clear and up to date so buyers know who they are contacting.</p></div><span class="seller-status seller-status-{{ $shop->suspended_at ? 'suspended' : $shop->status }}">{{ $shop->suspended_at ? 'Suspended' : Str::headline($shop->status) }}</span></section>

                <form method="post" action="{{ route('seller.profile.update') }}" data-shop-slug-form enctype="multipart/form-data" class="profile-layout">
                    @csrf
                    @method('put')
                    <div class="profile-form-column">
                        <section class="seller-panel profile-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">01 · Public presence</span><h2>How buyers find you</h2></div><span class="seller-panel-number">01</span></div><p class="seller-panel-copy">Your shop name and address appear in the marketplace directory and on your storefront.</p><div class="profile-fields"><label>Shop name <span class="profile-required">Required</span><input name="name" value="{{ old('name', $shop->name) }}" required data-shop-name autocomplete="organization"></label><label>Directory category <span class="profile-optional">Optional</span><input name="market_category" value="{{ old('market_category', $shop->market_category) }}" placeholder="Silk sarees, wedding wear"></label><label class="profile-field-wide">Public shop URL <span class="profile-optional">Share this link with buyers</span><div class="profile-url-field"><span>{{ request()->getSchemeAndHttpHost() }}/{{ $shop->isRetailer() ? 'retailer' : 'wholesaler' }}/</span><input name="slug" value="{{ old('slug', $shop->slug) }}" required data-shop-slug></div><small class="profile-url-preview">Live preview: <a data-shop-url-preview href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">{{ $shop->publicUrl() }} ↗</a></small></label><label class="profile-field-wide">About your shop <span class="profile-optional">A short introduction</span><textarea name="description" rows="5" maxlength="5000" placeholder="Tell buyers what makes your collection special...">{{ old('description', $shop->description) }}</textarea></label></div></section>

                        <section class="seller-panel profile-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">02 · Contact details</span><h2>Help buyers reach you</h2></div><span class="seller-panel-number">02</span></div><p class="seller-panel-copy">Add accurate contact and location details for enquiries and wholesale visits.</p><div class="profile-fields"><label class="profile-field-wide">Address <span class="profile-optional">Showroom or warehouse address</span><textarea name="address" rows="3" placeholder="Building, street, city, state">{{ old('address', $shop->address) }}</textarea></label><label>Shop / unit number <span class="profile-optional">Optional</span><input name="shop_number" value="{{ old('shop_number', $shop->shop_number) }}" placeholder="Unit 12"></label><label>Opening hours <span class="profile-optional">Optional</span><input name="opening_hours" value="{{ old('opening_hours', $shop->opening_hours) }}" placeholder="Mon–Sat, 10am–7pm"></label><label>Phone <span class="profile-optional">Optional</span><input name="phone" value="{{ old('phone', $shop->phone) }}" inputmode="tel" placeholder="+91 98765 43210"></label><label>WhatsApp <span class="profile-optional">Shows the floating enquiry icon</span><input name="whatsapp" value="{{ old('whatsapp', $shop->whatsapp) }}" inputmode="tel" placeholder="+91 98765 43210"></label><label class="profile-field-wide">Public email <span class="profile-optional">For buyer enquiries</span><input name="email" type="email" value="{{ old('email', $shop->email) }}" placeholder="sales@example.com"></label><label>Instagram URL <span class="profile-optional">Optional social link</span><input name="instagram_url" type="url" value="{{ old('instagram_url', $shop->instagram_url) }}" placeholder="https://instagram.com/your-shop"></label><label>Facebook URL <span class="profile-optional">Optional social link</span><input name="facebook_url" type="url" value="{{ old('facebook_url', $shop->facebook_url) }}" placeholder="https://facebook.com/your-shop"></label></div></section>

                        <section class="seller-panel profile-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">03 · Brand assets</span><h2>Make it recognisably yours</h2></div><span class="seller-panel-number">03</span></div><p class="seller-panel-copy">Upload your own images or create a polished logo and banner from ready-to-edit layouts. Existing images stay until you save a replacement.</p><div class="profile-upload-grid"><div class="profile-upload-card" data-brand-card="logo"><div class="profile-upload-heading"><strong>Logo image</strong><span>PNG, JPG, GIF or WebP · up to 2 MB</span></div>@if ($shop->logo_path)<span class="profile-current-image"><img src="{{ asset('storage/'.$shop->logo_path) }}" alt="Current shop logo">Current logo saved</span>@endif<input name="logo" type="file" accept="image/png,image/jpeg,image/gif,image/webp" data-brand-file="logo"><div class="profile-upload-actions"><button class="profile-create-button" type="button" data-open-brand-editor="logo">Create logo <span aria-hidden="true">✦</span></button><small>Square images work best.</small></div><span class="profile-file-status" data-brand-status="logo" aria-live="polite"></span></div><div class="profile-upload-card" data-brand-card="banner"><div class="profile-upload-heading"><strong>Banner image</strong><span>PNG, JPG, GIF or WebP · up to 5 MB</span></div>@if ($shop->banner_path)<span class="profile-current-image profile-current-banner"><img src="{{ asset('storage/'.$shop->banner_path) }}" alt="Current shop banner">Current banner saved</span>@endif<input name="banner" type="file" accept="image/png,image/jpeg,image/gif,image/webp" data-brand-file="banner"><div class="profile-upload-actions"><button class="profile-create-button" type="button" data-open-brand-editor="banner">Create banner <span aria-hidden="true">✦</span></button><small>Wide images work best.</small></div><span class="profile-file-status" data-brand-status="banner" aria-live="polite"></span></div></div><p class="profile-upload-note"><span aria-hidden="true">i</span> Created images are placed into these upload fields. Review the preview, then use the normal <strong>Save profile</strong> action to publish them.</p></section>

                        <section class="seller-panel profile-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">04 · Search preview</span><h2>Be easier to discover</h2></div><span class="seller-panel-number">04</span></div><p class="seller-panel-copy">These fields help search engines and social previews describe your public shop.</p><div class="profile-fields"><label class="profile-field-wide">SEO title <span class="profile-optional">Up to 60 characters</span><input name="seo_title" maxlength="60" value="{{ old('seo_title', $shop->seo_title) }}" placeholder="Divya Saree · Wholesale silk collection"></label><label class="profile-field-wide">SEO description <span class="profile-optional">Up to 160 characters</span><textarea name="seo_description" maxlength="160" rows="3" placeholder="Wholesale sarees from Divya Saree. Browse the latest collection and enquire directly.">{{ old('seo_description', $shop->seo_description) }}</textarea></label></div></section>

                        <div class="profile-save-bar"><p><strong>Ready to publish your updates?</strong><span>Your catalogue and products will not be changed.</span></p><button class="seller-primary-button" type="submit">Save profile <span aria-hidden="true">→</span></button></div>
                    </div>

                    <aside class="profile-side-column"><section class="seller-panel profile-preview-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">Live preview</span><h2>Your shop card</h2></div><span class="seller-panel-number">↗</span></div><div class="profile-shop-card"><div class="profile-shop-card-banner" data-shop-card-banner @if ($shop->banner_path) style="background-image:url('{{ asset('storage/'.$shop->banner_path) }}')" @endif><span class="profile-shop-avatar" data-shop-card-logo>{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span></div><span class="profile-shop-type">{{ Str::headline($shop->type) }}</span><h3>{{ $shop->name }}</h3><p>{{ $shop->market_category ?: 'Wholesale saree collection' }}</p><a href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">View storefront ↗</a></div><div class="profile-preview-note"><span aria-hidden="true">i</span><p>Your public URL changes automatically when you edit the shop name. You can also customise the slug.</p></div></section><section class="seller-panel profile-checklist-panel"><div class="seller-panel-heading"><div><span class="seller-panel-kicker">Profile checklist</span><h2>Build trust faster</h2></div></div><ul class="profile-checklist"><li class="{{ $shop->description ? 'is-complete' : '' }}"><span>{{ $shop->description ? '✓' : '1' }}</span><div><strong>Tell your story</strong><small>Add an about description</small></div></li><li class="{{ $shop->address ? 'is-complete' : '' }}"><span>{{ $shop->address ? '✓' : '2' }}</span><div><strong>Add your location</strong><small>Help buyers find your shop</small></div></li><li class="{{ $shop->phone || $shop->whatsapp || $shop->email ? 'is-complete' : '' }}"><span>{{ $shop->phone || $shop->whatsapp || $shop->email ? '✓' : '3' }}</span><div><strong>Share contact details</strong><small>Make enquiries easy</small></div></li><li class="{{ $shop->logo_path || $shop->banner_path ? 'is-complete' : '' }}"><span>{{ $shop->logo_path || $shop->banner_path ? '✓' : '4' }}</span><div><strong>Add brand imagery</strong><small>Save a logo or banner</small></div></li></ul></section></aside>
                </form>

                <div class="brand-editor" data-brand-editor hidden aria-hidden="true">
                    <div class="brand-editor-backdrop" data-brand-editor-close></div>
                    <section class="brand-editor-dialog" role="dialog" aria-modal="true" aria-labelledby="brand-editor-title" tabindex="-1">
                        <header class="brand-editor-header">
                            <div><p class="seller-overline">Brand studio</p><h2 id="brand-editor-title">Create your brand asset</h2><p data-brand-editor-subtitle>Choose a layout, tune the details, then place the finished image into your profile form.</p></div>
                            <button class="brand-editor-close" type="button" data-brand-editor-close aria-label="Close brand studio">×</button>
                        </header>
                        <div class="brand-editor-body">
                            <aside class="brand-editor-sidebar">
                                <div class="brand-editor-sidebar-heading"><strong>Choose a layout</strong><span data-brand-layout-count>6 options</span></div>
                                <div class="brand-editor-templates" data-brand-templates role="listbox" aria-label="Brand layouts"></div>
                                <div class="brand-editor-help"><span aria-hidden="true">✦</span><p>Every layout is editable. Your saved shop details and catalogue stay unchanged.</p></div>
                            </aside>
                            <div class="brand-editor-main">
                                <div class="brand-editor-preview-panel">
                                    <div class="brand-editor-preview-heading"><div><span class="profile-upload-eyebrow">Live preview</span><strong data-brand-preview-label>Desktop preview</strong></div><span data-brand-editor-kind-label>Logo</span></div>
                                    <div class="brand-editor-preview-stage" data-brand-preview-stage><canvas data-brand-canvas width="1200" height="1200" aria-label="Logo preview"></canvas><div class="brand-editor-loading" data-brand-loading hidden>Preparing preview…</div></div>
                                    <div class="brand-editor-crop-previews" data-banner-crop-previews hidden><div><span>Desktop crop</span><canvas data-brand-desktop-crop width="720" height="360"></canvas></div><div><span>Mobile crop</span><canvas data-brand-mobile-crop width="260" height="360"></canvas></div></div>
                                    <p class="brand-editor-feedback" data-brand-feedback role="status" aria-live="polite"></p>
                                </div>
                                <div class="brand-editor-controls">
                                    <div class="brand-editor-control-grid">
                                        <label>Business name<input type="text" data-brand-control="businessName" maxlength="120"></label>
                                        <label>Initials<input type="text" data-brand-control="initials" maxlength="4"></label>
                                        <label class="brand-editor-wide">Tagline <span>Optional</span><input type="text" data-brand-control="tagline" maxlength="120" placeholder="Wholesale sarees, thoughtfully sourced"></label>
                                        <label>Primary colour<input type="color" data-brand-control="primary"></label>
                                        <label>Accent colour<input type="color" data-brand-control="accent"></label>
                                        <label>Background<input type="color" data-brand-control="background"></label>
                                        <label>Text colour<input type="color" data-brand-control="text"></label>
                                        <label>Font<select data-brand-control="font"><option value="editorial">Editorial serif</option><option value="classic">Classic serif</option><option value="modern">Modern sans</option></select></label>
                                        <label>Text size <output data-brand-output="textSize">100%</output><input type="range" min="75" max="135" step="1" data-brand-control="textSize"></label>
                                        <label>Alignment<select data-brand-control="align"><option value="left">Left</option><option value="center">Centre</option><option value="right">Right</option></select></label>
                                        <label class="brand-editor-wide brand-editor-file-field">Photos <span data-brand-photo-hint>Optional image</span><input type="file" accept="image/png,image/jpeg,image/webp" multiple data-brand-control="photos"></label>
                                        <div class="brand-editor-wide brand-editor-photo-tools" data-brand-photo-tools hidden><label>Zoom <output data-brand-output="zoom">100%</output><input type="range" min="100" max="180" step="1" data-brand-control="zoom"></label><label>Horizontal position <output data-brand-output="offsetX">0</output><input type="range" min="-100" max="100" step="1" data-brand-control="offsetX"></label><label>Vertical position <output data-brand-output="offsetY">0</output><input type="range" min="-100" max="100" step="1" data-brand-control="offsetY"></label></div>
                                        <label class="brand-editor-wide brand-editor-check"><input type="checkbox" data-brand-control="transparent"><span>Transparent logo background</span></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <footer class="brand-editor-footer"><p>Exports are resized and checked against the 2 MB logo / 5 MB banner limits when you save.</p><div><button class="seller-secondary-button" type="button" data-brand-reset>Reset</button><button class="seller-secondary-button" type="button" data-brand-download>Download</button><button class="seller-secondary-button" type="button" data-brand-editor-cancel>Cancel</button><button class="seller-primary-button" type="button" data-brand-use>Use as logo</button></div></footer>
                    </section>
                </div>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const form = document.querySelector('[data-shop-slug-form]');
        if (form) {
            const name = form.querySelector('[data-shop-name]');
            const slug = form.querySelector('[data-shop-slug]');
            const preview = form.querySelector('[data-shop-url-preview]');
            let customSlug = slug.value !== slugify(name.value);
            function slugify(value) { return value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); }
            function updatePreview() { if (preview) { preview.textContent = `${preview.dataset.base}${slug.value} ↗`; preview.href = `${preview.dataset.base}${slug.value}`; } }
            if (preview) preview.dataset.base = preview.href.replace(/[^/]+$/, '');
            name.addEventListener('input', () => { if (!customSlug) slug.value = slugify(name.value); updatePreview(); });
            slug.addEventListener('input', () => { slug.value = slugify(slug.value); customSlug = slug.value !== slugify(name.value); updatePreview(); });
        }
    </script>
    <script>
        (() => {
            const editor = document.querySelector('[data-brand-editor]');
            const profileForm = document.querySelector('[data-shop-slug-form]');
            if (!editor || !profileForm) {
                return;
            }

            const layouts = {
                logo: [
                    { id: 'initial-circle', name: 'Initial circle', description: 'A confident monogram mark.' },
                    { id: 'wordmark', name: 'Elegant wordmark', description: 'A refined name-led lockup.' },
                    { id: 'boutique-badge', name: 'Boutique badge', description: 'A compact collection seal.' },
                    { id: 'geometric-emblem', name: 'Geometric emblem', description: 'Structured lines and a name.' },
                    { id: 'textile-motif', name: 'Textile motif', description: 'Woven rhythm with initials.' },
                    { id: 'framed-name', name: 'Framed name', description: 'A quiet name and tagline.' },
                ],
                banner: [
                    { id: 'text-photo', name: 'Text left · photo right', description: 'Editorial introduction with a hero image.' },
                    { id: 'photo-text', name: 'Photo left · text right', description: 'Image-led showroom statement.' },
                    { id: 'three-photo', name: 'Three-photo collage', description: 'A balanced edit of three views.' },
                    { id: 'centered-name', name: 'Centred name', description: 'Decorative background with a clear mark.' },
                    { id: 'collection-announcement', name: 'Collection announcement', description: 'A prominent photograph and headline.' },
                    { id: 'showroom-intro', name: 'Showroom introduction', description: 'A warm image, name and tagline.' },
                ],
            };

            const fontFamilies = {
                editorial: 'Georgia, Times New Roman, serif',
                classic: 'Palatino Linotype, Book Antiqua, Georgia, serif',
                modern: 'Arial, Helvetica, sans-serif',
            };
            const shopName = @json(old('name', $shop->name));
            const initialLogo = @json($shop->logo_path ? asset('storage/'.$shop->logo_path) : null);
            const initialBanner = @json($shop->banner_path ? asset('storage/'.$shop->banner_path) : null);
            const formInputs = {
                logo: profileForm.querySelector('[data-brand-file="logo"]'),
                banner: profileForm.querySelector('[data-brand-file="banner"]'),
            };
            const controls = {};
            editor.querySelectorAll('[data-brand-control]').forEach((control) => {
                controls[control.dataset.brandControl] = control;
            });
            const outputs = {};
            editor.querySelectorAll('[data-brand-output]').forEach((output) => {
                outputs[output.dataset.brandOutput] = output;
            });
            const canvas = editor.querySelector('[data-brand-canvas]');
            const desktopCrop = editor.querySelector('[data-brand-desktop-crop]');
            const mobileCrop = editor.querySelector('[data-brand-mobile-crop]');
            const feedback = editor.querySelector('[data-brand-feedback]');
            const templateList = editor.querySelector('[data-brand-templates]');
            const previewStage = editor.querySelector('[data-brand-preview-stage]');
            const cropPreviews = editor.querySelector('[data-banner-crop-previews]');
            const photoTools = editor.querySelector('[data-brand-photo-tools]');
            const photoHint = editor.querySelector('[data-brand-photo-hint]');
            const previewLabel = editor.querySelector('[data-brand-preview-label]');
            const kindLabel = editor.querySelector('[data-brand-editor-kind-label]');
            const subtitle = editor.querySelector('[data-brand-editor-subtitle]');
            const loading = editor.querySelector('[data-brand-loading]');
            const useButton = editor.querySelector('[data-brand-use]');
            const lastFocus = { element: null };
            let currentKind = 'logo';
            let currentLayout = 'initial-circle';
            let state = null;
            let previewSnapshot = null;
            let objectUrls = [];

            function slugify(value) {
                return String(value || 'brand').toLowerCase().normalize('NFKD').replace(/[\\u0300-\\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '') || 'brand';
            }

            function initialsFromName(value) {
                return String(value || '').trim().split(/\\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]).join('').toUpperCase() || 'WM';
            }

            function defaults(kind) {
                const isLogo = kind === 'logo';
                return {
                    businessName: shopName || 'Your shop',
                    initials: initialsFromName(shopName),
                    tagline: '',
                    primary: isLogo ? '#632e41' : '#632e41',
                    accent: isLogo ? '#c99a62' : '#d4a16b',
                    background: isLogo ? '#fffaf5' : '#f4ebe0',
                    text: '#302629',
                    font: 'editorial',
                    textSize: 100,
                    align: 'left',
                    zoom: 100,
                    offsetX: 0,
                    offsetY: 0,
                    transparent: false,
                    images: [],
                    imageSrcs: kind === 'logo' ? (initialLogo ? [initialLogo] : []) : (initialBanner ? [initialBanner] : []),
                };
            }

            function setFeedback(message = '', isError = false) {
                feedback.textContent = message;
                feedback.classList.toggle('is-error', isError);
            }

            function setLoading(value) {
                loading.hidden = !value;
                previewStage.classList.toggle('is-loading', value);
            }

            function setCanvasSize(kind) {
                const isLogo = kind === 'logo';
                canvas.width = isLogo ? 1200 : 2400;
                canvas.height = isLogo ? 1200 : 1200;
                canvas.setAttribute('aria-label', `${isLogo ? 'Logo' : 'Banner'} preview`);
                cropPreviews.hidden = isLogo;
                photoTools.hidden = false;
                photoHint.textContent = isLogo ? 'Optional uploaded image' : (currentLayout === 'centered-name' ? 'Optional image texture' : 'Add one or more photographs');
                kindLabel.textContent = isLogo ? 'Logo' : 'Banner';
                previewLabel.textContent = isLogo ? 'Square preview' : 'Desktop preview';
                useButton.textContent = `Use as ${isLogo ? 'logo' : 'banner'}`;
                subtitle.textContent = isLogo ? 'Choose a logo layout, tune the details, then place the finished image into your profile form.' : 'Choose a banner layout, add photographs, and keep your message inside safe desktop and mobile crop areas.';
            }

            function renderTemplateList() {
                templateList.replaceChildren();
                layouts[currentKind].forEach((layout, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'brand-editor-template';
                    button.dataset.brandLayout = layout.id;
                    button.setAttribute('role', 'option');
                    button.setAttribute('aria-selected', layout.id === currentLayout ? 'true' : 'false');
                    button.innerHTML = `<span class="brand-editor-template-preview"><span>${currentKind === 'logo' ? 'A' : String(index + 1).padStart(2, '0')}</span></span><span class="brand-editor-template-copy"><strong>${layout.name}</strong><small>${layout.description}</small></span>`;
                    button.addEventListener('click', () => {
                        currentLayout = layout.id;
                        templateList.querySelectorAll('.brand-editor-template').forEach((item) => {
                            const active = item.dataset.brandLayout === currentLayout;
                            item.classList.toggle('is-active', active);
                            item.setAttribute('aria-selected', active ? 'true' : 'false');
                        });
                        photoHint.textContent = currentKind === 'logo' ? 'Optional uploaded image' : (currentLayout === 'centered-name' ? 'Optional image texture' : 'Add one or more photographs');
                        setFeedback('');
                        render();
                    });
                    button.addEventListener('keydown', (event) => {
                        if (!['ArrowDown', 'ArrowUp'].includes(event.key)) {
                            return;
                        }
                        event.preventDefault();
                        const buttons = [...templateList.querySelectorAll('.brand-editor-template')];
                        const nextIndex = (buttons.indexOf(button) + (event.key === 'ArrowDown' ? 1 : -1) + buttons.length) % buttons.length;
                        buttons[nextIndex].focus();
                        buttons[nextIndex].click();
                    });
                    templateList.append(button);
                });
            }

            function syncControls() {
                Object.entries(controls).forEach(([key, control]) => {
                    if (key === 'photos') {
                        return;
                    }
                    if (control.type === 'checkbox') {
                        control.checked = Boolean(state[key]);
                    } else {
                        control.value = state[key];
                    }
                });
                updateOutputs();
            }

            function readControls() {
                Object.entries(controls).forEach(([key, control]) => {
                    if (key === 'photos') {
                        return;
                    }
                    state[key] = control.type === 'checkbox' ? control.checked : control.value;
                });
                state.textSize = Number(state.textSize);
                state.zoom = Number(state.zoom);
                state.offsetX = Number(state.offsetX);
                state.offsetY = Number(state.offsetY);
            }

            function updateOutputs() {
                if (outputs.textSize) outputs.textContent = `${state.textSize}%`;
                if (outputs.zoom) outputs.zoom.textContent = `${state.zoom}%`;
                if (outputs.offsetX) outputs.offsetX.textContent = state.offsetX;
                if (outputs.offsetY) outputs.offsetY.textContent = state.offsetY;
            }

            function font(size, weight = '400') {
                return `${weight} ${size}px ${fontFamilies[state.font] || fontFamilies.editorial}`;
            }

            function roundedRect(ctx, x, y, width, height, radius) {
                const r = Math.min(radius, width / 2, height / 2);
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.arcTo(x + width, y, x + width, y + height, r);
                ctx.arcTo(x + width, y + height, x, y + height, r);
                ctx.arcTo(x, y + height, x, y, r);
                ctx.arcTo(x, y, x + width, y, r);
                ctx.closePath();
            }

            function wrapText(ctx, value, maxWidth, lineHeight, maxLines = 3) {
                const words = String(value || '').trim().split(/\\s+/).filter(Boolean);
                const lines = [];
                let line = '';
                words.forEach((word) => {
                    const candidate = line ? `${line} ${word}` : word;
                    if (ctx.measureText(candidate).width > maxWidth && line) {
                        lines.push(line);
                        line = word;
                    } else {
                        line = candidate;
                    }
                });
                if (line) lines.push(line);
                if (lines.length > maxLines) {
                    lines.length = maxLines;
                    lines[maxLines - 1] = `${lines[maxLines - 1].replace(/[.]{3}$/, '')}…`;
                }
                return { lines, lineHeight };
            }

            function drawTextBlock(ctx, value, x, y, maxWidth, size, colour, align = 'left', maxLines = 3, weight = '400') {
                ctx.fillStyle = colour;
                ctx.font = font(size, weight);
                ctx.textAlign = align;
                const block = wrapText(ctx, value, maxWidth, Math.round(size * 1.1), maxLines);
                block.lines.forEach((line, index) => ctx.fillText(line, x, y + index * block.lineHeight));
                return block.lines.length * block.lineHeight;
            }

            function drawPhoto(ctx, image, x, y, width, height, crop = state) {
                if (!image) {
                    const gradient = ctx.createLinearGradient(x, y, x + width, y + height);
                    gradient.addColorStop(0, crop.accent);
                    gradient.addColorStop(1, crop.primary);
                    ctx.fillStyle = gradient;
                    ctx.fillRect(x, y, width, height);
                    return;
                }
                const scale = Math.max(width / image.width, height / image.height) * ((Number(crop.zoom) || 100) / 100);
                const drawWidth = image.width * scale;
                const drawHeight = image.height * scale;
                const left = x + (width - drawWidth) / 2 + (Number(crop.offsetX) || 0) / 100 * width;
                const top = y + (height - drawHeight) / 2 + (Number(crop.offsetY) || 0) / 100 * height;
                ctx.save();
                ctx.beginPath();
                ctx.rect(x, y, width, height);
                ctx.clip();
                ctx.drawImage(image, left, top, drawWidth, drawHeight);
                ctx.restore();
            }

            function drawLogo(ctx) {
                const width = canvas.width;
                const height = canvas.height;
                const primary = state.primary;
                const accent = state.accent;
                const background = state.transparent ? 'transparent' : state.background;
                if (background !== 'transparent') {
                    ctx.fillStyle = background;
                    ctx.fillRect(0, 0, width, height);
                } else {
                    ctx.clearRect(0, 0, width, height);
                }
                const centreX = width / 2;
                const centreY = height / 2;
                const scale = state.textSize / 100;
                const name = state.businessName || 'Your shop';
                const initials = state.initials || initialsFromName(name);
                const image = state.images[0];
                ctx.save();
                if (image) {
                    ctx.globalAlpha = .2;
                    ctx.beginPath();
                    ctx.arc(centreX, centreY - 90, 280, 0, Math.PI * 2);
                    ctx.clip();
                    drawPhoto(ctx, image, centreX - 280, centreY - 370, 560, 560);
                    ctx.restore();
                    ctx.save();
                }
                ctx.lineWidth = 16;
                ctx.strokeStyle = accent;
                ctx.fillStyle = primary;
                if (currentLayout === 'initial-circle') {
                    ctx.beginPath(); ctx.arc(centreX, 390, 250, 0, Math.PI * 2); ctx.fill();
                    ctx.lineWidth = 12; ctx.strokeStyle = accent; ctx.stroke();
                    ctx.fillStyle = '#fffaf5'; ctx.font = font(170 * scale); ctx.textAlign = 'center'; ctx.fillText(initials, centreX, 445);
                    drawTextBlock(ctx, name, centreX, 790, 850, 65 * scale, primary, 'center', 2, '600');
                } else if (currentLayout === 'wordmark') {
                    ctx.strokeStyle = accent; ctx.lineWidth = 6; ctx.beginPath(); ctx.moveTo(180, 350); ctx.lineTo(width - 180, 350); ctx.stroke();
                    drawTextBlock(ctx, name, centreX, 550, 850, 90 * scale, primary, 'center', 2, '600');
                    ctx.fillStyle = accent; ctx.fillRect(centreX - 90, 665, 180, 7);
                    drawTextBlock(ctx, state.tagline || 'SAREE COLLECTION', centreX, 760, 850, 28 * scale, primary, 'center', 2, '400');
                } else if (currentLayout === 'boutique-badge') {
                    roundedRect(ctx, 180, 200, width - 360, height - 400, 45); ctx.strokeStyle = accent; ctx.stroke();
                    ctx.beginPath(); ctx.arc(centreX, 480, 150, 0, Math.PI * 2); ctx.fill();
                    ctx.fillStyle = '#fffaf5'; ctx.font = font(100 * scale); ctx.textAlign = 'center'; ctx.fillText(initials, centreX, 515);
                    drawTextBlock(ctx, name, centreX, 760, 850, 52 * scale, primary, 'center', 2, '600');
                    drawTextBlock(ctx, 'BOUTIQUE EDIT', centreX, 870, 850, 24 * scale, accent, 'center', 1, '600');
                } else if (currentLayout === 'geometric-emblem') {
                    ctx.translate(centreX, 430); ctx.rotate(Math.PI / 4); ctx.fillStyle = primary; ctx.fillRect(-185, -185, 370, 370); ctx.strokeStyle = accent; ctx.lineWidth = 14; ctx.strokeRect(-145, -145, 290, 290); ctx.rotate(-Math.PI / 4);
                    ctx.fillStyle = '#fffaf5'; ctx.font = font(128 * scale); ctx.textAlign = 'center'; ctx.fillText(initials, 0, 45); ctx.restore(); ctx.save();
                    drawTextBlock(ctx, name, centreX, 790, 850, 52 * scale, primary, 'center', 2, '600');
                    drawTextBlock(ctx, state.tagline || 'WHOLESALE STUDIO', centreX, 900, 850, 24 * scale, accent, 'center', 1, '600');
                } else if (currentLayout === 'textile-motif') {
                    ctx.strokeStyle = accent; ctx.lineWidth = 4;
                    for (let i = -height; i < width; i += 55) { ctx.beginPath(); ctx.moveTo(i, 0); ctx.lineTo(i + height, height); ctx.stroke(); ctx.beginPath(); ctx.moveTo(i + height, 0); ctx.lineTo(i, height); ctx.stroke(); }
                    ctx.fillStyle = background === 'transparent' ? '#fffaf5' : background; ctx.globalAlpha = .94; ctx.fillRect(155, 245, width - 310, 700); ctx.globalAlpha = 1;
                    ctx.fillStyle = primary; ctx.font = font(165 * scale); ctx.textAlign = 'center'; ctx.fillText(initials, centreX, 500);
                    drawTextBlock(ctx, name, centreX, 700, 820, 48 * scale, primary, 'center', 2, '600');
                    drawTextBlock(ctx, state.tagline || 'TEXTILES · COLOUR · CRAFT', centreX, 850, 850, 22 * scale, accent, 'center', 1, '600');
                } else {
                    ctx.strokeStyle = accent; ctx.lineWidth = 12; ctx.strokeRect(130, 130, width - 260, height - 260);
                    ctx.lineWidth = 3; ctx.strokeRect(160, 160, width - 320, height - 320);
                    drawTextBlock(ctx, name, centreX, 560, 820, 70 * scale, primary, 'center', 2, '600');
                    drawTextBlock(ctx, state.tagline || 'WHOLESALE COLLECTION', centreX, 735, 820, 28 * scale, accent, 'center', 2, '400');
                    ctx.fillStyle = accent; ctx.fillRect(centreX - 55, 820, 110, 6);
                }
                ctx.restore();
            }

            function drawBanner(ctx) {
                const width = canvas.width;
                const height = canvas.height;
                const primary = state.primary;
                const accent = state.accent;
                const background = state.background;
                const image = state.images[0];
                const imageTwo = state.images[1] || image;
                const imageThree = state.images[2] || imageTwo;
                ctx.fillStyle = background;
                ctx.fillRect(0, 0, width, height);
                const textSize = 108 * (state.textSize / 100);
                if (currentLayout === 'text-photo') {
                    ctx.fillStyle = primary; ctx.fillRect(0, 0, width * .48, height);
                    drawPhoto(ctx, image, width * .48, 0, width * .52, height);
                    drawTextBlock(ctx, state.businessName, 170, 420, 850, textSize, '#fffaf5', 'left', 3, '600');
                    drawTextBlock(ctx, state.tagline || 'A considered saree collection.', 170, 760, 780, 38, '#f3d6b0', 'left', 3, '400');
                    drawTextBlock(ctx, 'THE COLLECTION', 170, 160, 780, 25, accent, 'left', 1, '600');
                } else if (currentLayout === 'photo-text') {
                    drawPhoto(ctx, image, 0, 0, width * .52, height);
                    ctx.fillStyle = background; ctx.fillRect(width * .52, 0, width * .48, height);
                    drawTextBlock(ctx, 'THE EDIT', width * .58, 180, width * .32, 25, accent, 'left', 1, '600');
                    drawTextBlock(ctx, state.businessName, width * .58, 455, width * .34, textSize, primary, 'left', 3, '600');
                    drawTextBlock(ctx, state.tagline || 'Beautifully presented for your next collection.', width * .58, 795, width * .31, 34, primary, 'left', 3, '400');
                } else if (currentLayout === 'three-photo') {
                    drawPhoto(ctx, image, 0, 0, width / 2, height);
                    drawPhoto(ctx, imageTwo, width / 2, 0, width / 2, height / 2);
                    drawPhoto(ctx, imageThree, width / 2, height / 2, width / 2, height / 2);
                    ctx.fillStyle = 'rgba(45, 20, 33, .78)'; ctx.fillRect(70, 70, 900, 340);
                    drawTextBlock(ctx, state.businessName, 135, 220, 780, 72 * (state.textSize / 100), '#fffaf5', 'left', 2, '600');
                    drawTextBlock(ctx, state.tagline || 'Three views of the collection.', 135, 335, 700, 26, '#f3d6b0', 'left', 2, '400');
                } else if (currentLayout === 'centered-name') {
                    const gradient = ctx.createLinearGradient(0, 0, width, height); gradient.addColorStop(0, background); gradient.addColorStop(1, accent); ctx.fillStyle = gradient; ctx.fillRect(0, 0, width, height);
                    ctx.strokeStyle = primary; ctx.lineWidth = 4; ctx.strokeRect(100, 100, width - 200, height - 200);
                    if (image) { ctx.globalAlpha = .16; drawPhoto(ctx, image, 0, 0, width, height); ctx.globalAlpha = 1; }
                    drawTextBlock(ctx, 'SAREE COLLECTION', width / 2, 370, 1700, 26, primary, 'center', 1, '600');
                    drawTextBlock(ctx, state.businessName, width / 2, 620, 1700, textSize, primary, 'center', 3, '600');
                    drawTextBlock(ctx, state.tagline || 'Direct catalogue enquiries.', width / 2, 800, 1500, 36, primary, 'center', 2, '400');
                } else if (currentLayout === 'collection-announcement') {
                    drawPhoto(ctx, image, 0, 0, width, height);
                    ctx.fillStyle = 'rgba(31, 18, 29, .65)'; ctx.fillRect(0, 0, width, height);
                    drawTextBlock(ctx, 'THE WHOLESALE EDIT', 150, 220, 1200, 26, accent, 'left', 1, '600');
                    drawTextBlock(ctx, state.businessName, 150, 570, 1300, textSize, '#fffaf5', 'left', 3, '600');
                    drawTextBlock(ctx, state.tagline || 'A new point of view on beautiful sarees.', 150, 820, 1150, 36, '#f5dfc1', 'left', 3, '400');
                } else {
                    drawPhoto(ctx, image, 0, 0, width * .58, height);
                    ctx.fillStyle = primary; ctx.fillRect(width * .58, 0, width * .42, height);
                    drawTextBlock(ctx, 'WELCOME TO', width * .65, 250, width * .29, 25, accent, 'left', 1, '600');
                    drawTextBlock(ctx, state.businessName, width * .65, 560, width * .3, textSize * .84, '#fffaf5', 'left', 3, '600');
                    drawTextBlock(ctx, state.tagline || 'A thoughtful place to source your next collection.', width * .65, 850, width * .29, 30, '#f5dfc1', 'left', 3, '400');
                }
            }

            function renderCropPreviews() {
                if (currentKind !== 'banner') {
                    return;
                }
                const source = state.images[0];
                [desktopCrop, mobileCrop].forEach((target, index) => {
                    const targetWidth = target.width;
                    const targetHeight = target.height;
                    target.getContext('2d').clearRect(0, 0, targetWidth, targetHeight);
                    target.getContext('2d').fillStyle = state.background;
                    target.getContext('2d').fillRect(0, 0, targetWidth, targetHeight);
                    if (source) {
                        drawPhoto(target.getContext('2d'), source, 0, 0, targetWidth, targetHeight, state);
                    }
                    target.getContext('2d').fillStyle = 'rgba(99, 46, 65, .82)';
                    target.getContext('2d').font = font(index === 0 ? 18 : 14, '600');
                    target.getContext('2d').textAlign = 'center';
                    target.getContext('2d').fillText(state.businessName || 'Your shop', targetWidth / 2, targetHeight - 22);
                });
            }

            function render() {
                readControls();
                const context = canvas.getContext('2d');
                context.clearRect(0, 0, canvas.width, canvas.height);
                if (currentKind === 'logo') drawLogo(context); else drawBanner(context);
                updateOutputs();
                renderCropPreviews();
            }

            function loadImage(source) {
                return new Promise((resolve, reject) => {
                    const image = new Image();
                    image.onload = () => resolve(image);
                    image.onerror = () => reject(new Error('The selected photograph could not be loaded.'));
                    image.src = source;
                });
            }

            async function loadInitialImages() {
                state.images = [];
                for (const source of state.imageSrcs) {
                    try {
                        state.images.push(await loadImage(source));
                    } catch (error) {
                        setFeedback(error.message, true);
                    }
                }
            }

            async function readPhotos(event) {
                objectUrls.forEach((url) => URL.revokeObjectURL(url));
                objectUrls = [];
                state.images = [];
                const files = [...(event.target.files || [])].slice(0, 3);
                setLoading(true);
                try {
                    for (const file of files) {
                        const url = URL.createObjectURL(file);
                        objectUrls.push(url);
                        state.images.push(await loadImage(url));
                    }
                    setFeedback(files.length ? `${files.length} photograph${files.length === 1 ? '' : 's'} ready for the preview.` : '');
                } catch (error) {
                    state.images = [];
                    setFeedback(error.message, true);
                } finally {
                    setLoading(false);
                    render();
                }
            }

            function capturePreviewSnapshot() {
                const logo = profileForm.querySelector('[data-shop-card-logo]');
                const banner = profileForm.querySelector('[data-shop-card-banner]');
                previewSnapshot = {
                    logoText: logo?.textContent || '',
                    logoBackground: logo?.style.backgroundImage || '',
                    bannerBackground: banner?.style.backgroundImage || '',
                };
            }

            function restorePreviewSnapshot() {
                const logo = profileForm.querySelector('[data-shop-card-logo]');
                const banner = profileForm.querySelector('[data-shop-card-banner]');
                if (logo && previewSnapshot) { logo.textContent = previewSnapshot.logoText; logo.style.backgroundImage = previewSnapshot.logoBackground; }
                if (banner && previewSnapshot) banner.style.backgroundImage = previewSnapshot.bannerBackground;
            }

            function updateShopCardPreview(kind, blob) {
                const url = URL.createObjectURL(blob);
                const logo = profileForm.querySelector('[data-shop-card-logo]');
                const banner = profileForm.querySelector('[data-shop-card-banner]');
                if (kind === 'logo' && logo) { logo.textContent = ''; logo.style.background = `url(${url}) center / cover no-repeat`; }
                if (kind === 'banner' && banner) banner.style.backgroundImage = `url(${url})`;
            }

            function setFileInput(kind, blob) {
                const input = formInputs[kind];
                if (!input || typeof DataTransfer === 'undefined') {
                    setFeedback('This browser cannot place the generated image into the upload form. Please download it and choose it manually.', true);
                    return false;
                }
                const transfer = new DataTransfer();
                transfer.items.add(new File([blob], `${slugify(state.businessName)}-${kind}.webp`, { type: blob.type || 'image/webp' }));
                input.files = transfer.files;
                const status = profileForm.querySelector(`[data-brand-status="${kind}"]`);
                if (status) status.textContent = `Created ${kind} ready — save your profile to publish it.`;
                updateShopCardPreview(kind, blob);
                return true;
            }

            function canvasBlob(type, quality) {
                return new Promise((resolve) => canvas.toBlob(resolve, type, quality));
            }

            async function exportBlob() {
                if (currentKind === 'banner' && ['text-photo', 'photo-text', 'three-photo', 'collection-announcement', 'showroom-intro'].includes(currentLayout) && !state.images.length) {
                    setFeedback('Add at least one photograph for this layout before exporting.', true);
                    return null;
                }
                setLoading(true);
                try {
                    let quality = .92;
                    const maxBytes = currentKind === 'logo' ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
                    let blob = await canvasBlob('image/webp', quality);
                    while (blob && blob.size > maxBytes && quality > .48) {
                        quality -= .08;
                        blob = await canvasBlob('image/webp', quality);
                    }
                    if (!blob || blob.size > maxBytes) {
                        setFeedback(`The exported image is larger than the ${currentKind === 'logo' ? '2 MB logo' : '5 MB banner'} limit. Reduce the photo size and try again.`, true);
                        return null;
                    }
                    return blob;
                } catch (error) {
                    setFeedback('The image could not be exported. Try another photo or layout.', true);
                    return null;
                } finally {
                    setLoading(false);
                }
            }

            async function useAsset() {
                const blob = await exportBlob();
                if (!blob || !setFileInput(currentKind, blob)) {
                    return;
                }
                closeEditor(true);
            }

            async function downloadAsset() {
                const blob = await exportBlob();
                if (!blob) return;
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `${slugify(state.businessName)}-${currentKind}.webp`;
                link.click();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                setFeedback('Download ready. Your saved profile image has not changed.');
            }

            function closeEditor(keepAsset = false) {
                if (!keepAsset) restorePreviewSnapshot();
                editor.hidden = true;
                editor.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('brand-editor-open');
                objectUrls.forEach((url) => URL.revokeObjectURL(url));
                objectUrls = [];
                if (lastFocus.element) lastFocus.element.focus();
            }

            async function openEditor(kind, trigger) {
                currentKind = kind;
                currentLayout = layouts[kind][0].id;
                state = defaults(kind);
                lastFocus.element = trigger;
                capturePreviewSnapshot();
                setCanvasSize(kind);
                renderTemplateList();
                syncControls();
                editor.hidden = false;
                editor.setAttribute('aria-hidden', 'false');
                document.body.classList.add('brand-editor-open');
                setFeedback('');
                setLoading(true);
                await loadInitialImages();
                setLoading(false);
                render();
                editor.querySelector('.brand-editor-close').focus();
            }

            editor.querySelectorAll('[data-brand-editor-close]').forEach((button) => button.addEventListener('click', () => closeEditor(false)));
            editor.querySelector('[data-brand-editor-cancel]').addEventListener('click', () => closeEditor(false));
            editor.querySelector('[data-brand-reset]').addEventListener('click', async () => {
                state = defaults(currentKind);
                syncControls();
                setFeedback('Preview reset. Your saved image has not changed.');
                setLoading(true);
                await loadInitialImages();
                setLoading(false);
                render();
            });
            editor.querySelector('[data-brand-download]').addEventListener('click', downloadAsset);
            useButton.addEventListener('click', useAsset);
            controls.photos.addEventListener('change', readPhotos);
            Object.entries(controls).forEach(([key, control]) => {
                if (key !== 'photos') control.addEventListener('input', render);
                if (key === 'transparent') control.addEventListener('change', render);
            });
            profileForm.querySelectorAll('[data-open-brand-editor]').forEach((button) => button.addEventListener('click', () => openEditor(button.dataset.openBrandEditor, button)));
            Object.entries(formInputs).forEach(([kind, input]) => input?.addEventListener('change', () => {
                const file = input.files?.[0];
                const status = profileForm.querySelector(`[data-brand-status="${kind}"]`);
                if (file && status) status.textContent = `${file.name} ready — save your profile to publish it.`;
                if (file && kind === 'banner') {
                    const reader = new FileReader();
                    reader.onload = () => { const banner = profileForm.querySelector('[data-shop-card-banner]'); if (banner) banner.style.backgroundImage = `url(${reader.result})`; };
                    reader.readAsDataURL(file);
                }
            }));
            document.addEventListener('keydown', (event) => {
                if (editor.hidden) return;
                if (event.key === 'Escape') {
                    closeEditor(false);
                    return;
                }
                if (event.key !== 'Tab') return;
                const focusables = [...editor.querySelectorAll('button, input, select, [tabindex]:not([tabindex="-1"])')]
                    .filter((element) => !element.disabled && element.offsetParent !== null);
                if (!focusables.length) return;
                const first = focusables[0];
                const last = focusables[focusables.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            });
        })();
    </script>
@endpush
