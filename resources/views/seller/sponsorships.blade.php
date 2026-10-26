@extends('layouts.storefront')

@section('body_class', 'seller-dashboard-page seller-sponsorships-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/seller-dashboard.css') }}?v={{ filemtime(public_path('storefront/seller-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('storefront/seller-sponsorships.css') }}?v={{ filemtime(public_path('storefront/seller-sponsorships.css')) }}">
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
                <a class="seller-nav-link" href="{{ route('seller.design') }}"><span aria-hidden="true">◇</span>Design</a>
                <a class="seller-nav-link" href="{{ route('seller.enquiries.index') }}"><span aria-hidden="true">↗</span>Enquiries</a>
                @if ($shop->isWholesaler())
                    <a class="seller-nav-link is-active" href="{{ route('seller.sponsorships.index') }}" aria-current="page"><span aria-hidden="true">♡</span>Retailer sponsors</a>
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
                <div><p class="seller-overline">{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</p><p class="seller-breadcrumb">{{ $shop->name }} / Retailer sponsors</p></div>
                <a class="seller-store-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">Open storefront <span aria-hidden="true">↗</span></a>
            </header>

            <main class="seller-content sponsorships-content">
                @if (session('status'))
                    <div class="seller-alert seller-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="seller-alert seller-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="sponsorships-heading">
                    <div>
                        <p class="seller-overline">Pro wholesaler benefit</p>
                        <h1>Retailer sponsorships</h1>
                        <p class="seller-page-intro">Create retailer accounts and launch storefronts powered by your catalogue.</p>
                    </div>
                    <div class="sponsorships-heading-actions"><span class="sponsorships-slot-summary"><strong>{{ $activeCount }}</strong> / {{ $slotLimit }} active slots</span><a class="seller-secondary-button" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">View live shop <span aria-hidden="true">↗</span></a></div>
                </section>

                @php($pendingInviteCount = $invites->where('status', 'pending')->count())
                <section class="sponsorships-metrics" aria-label="Sponsorship summary">
                    <article class="sponsorship-metric sponsorship-metric-primary"><span class="sponsorship-metric-icon" aria-hidden="true">♡</span><div><span>Active sponsors</span><strong>{{ $activeCount }}</strong><small>Retailer relationships live</small></div></article>
                    <article class="sponsorship-metric"><span class="sponsorship-metric-icon" aria-hidden="true">＋</span><div><span>Available slots</span><strong>{{ max(0, $slotLimit - $activeCount) }}</strong><small>Included with your plan</small></div></article>
                    <article class="sponsorship-metric"><span class="sponsorship-metric-icon" aria-hidden="true">↗</span><div><span>Legacy invitations</span><strong>{{ $pendingInviteCount }}</strong><small>Older invitations awaiting setup</small></div></article>
                </section>

                @if (session('invitation_link'))
                    <section class="sponsorship-link-alert" role="status"><span class="sponsorship-link-icon" aria-hidden="true">✓</span><div><strong>Your invitation is ready to share.</strong><p>Local mail is logged for review. You can copy this link and send it directly to the retailer.</p><a href="{{ session('invitation_link') }}">{{ session('invitation_link') }}</a></div></section>
                @endif

                @if ($slotLimit > 0)
                    <section class="seller-panel sponsorship-invite-panel" aria-labelledby="invite-retailer-heading">
                        <div class="sponsorship-panel-heading"><div><p class="seller-panel-kicker">Grow your network</p><h2 id="invite-retailer-heading">Create a retailer account</h2><p>Create the retailer’s login and storefront directly. Their shop will be active under your sponsored catalogue immediately.</p></div><span class="sponsorship-panel-number">01</span></div>
                        <form method="post" action="{{ route('seller.sponsorships.retailers.store') }}" class="sponsorship-invite-form sponsorship-create-form" data-retailer-slug-form>
                            @csrf
                            <div class="sponsorship-create-grid">
                                <label>Retailer name <span>Account owner’s full name</span><input name="retailer_name" value="{{ old('retailer_name') }}" autocomplete="name" required></label>
                                <label>Phone / WhatsApp number <span>Used for retailer enquiries</span><input name="retailer_phone" value="{{ old('retailer_phone') }}" inputmode="tel" autocomplete="tel" placeholder="+91 98765 43210" required></label>
                                <label>Retailer email <span>Login email address</span><input name="retailer_email" type="email" value="{{ old('retailer_email') }}" autocomplete="email" placeholder="retailer@example.com" required></label>
                                <label>Shop name <span>Public retailer storefront name</span><input name="shop_name" value="{{ old('shop_name') }}" data-shop-name autocomplete="organization" required></label>
                                <label>Password <span>Share this securely with the retailer</span><input name="retailer_password" type="password" minlength="12" autocomplete="new-password" required></label>
                                <label>Confirm password <span>Repeat the retailer login password</span><input name="retailer_password_confirmation" type="password" minlength="12" autocomplete="new-password" required></label>
                                <label class="sponsorship-field-wide">Shop URL <span>Generated from the shop name</span><span class="sponsorship-slug-field"><span>{{ request()->getSchemeAndHttpHost() }}/retailer/</span><input name="slug" value="{{ old('slug') }}" data-shop-slug pattern="[A-Za-z0-9-]+" required></span></label>
                                <label>Sponsored term <span>How long access lasts</span><select name="term_days">@foreach ($terms as $term)<option value="{{ $term }}" @selected((int) old('term_days', $terms[0] ?? $term) === (int) $term)>{{ $term }} days</option>@endforeach</select></label>
                                <label>Initial design <span>Retailer can change it later</span><select name="template">@foreach(\App\Models\Shop::templatesForType('retailer') as $key => $name)<option value="{{ $key }}" @selected(old('template', 'classic') === $key)>{{ $name }}</option>@endforeach</select></label>
                            </div>
                            <button class="seller-primary-button" type="submit">Create retailer shop <span aria-hidden="true">→</span></button>
                        </form>
                    </section>
                @else
                    <section class="sponsorship-upgrade-panel"><div class="sponsorship-upgrade-icon" aria-hidden="true">✦</div><div><p class="seller-overline">Unlock retailer growth</p><h2>Upgrade to Pro to sponsor retailer shops.</h2><p>Retailer sponsorships require an active Pro wholesaler subscription. Ask an administrator to assign or renew Pro access for this shop.</p></div><span class="sponsorship-plan-pill">Pro benefit</span></section>
                @endif

                <section class="seller-panel sponsorship-list-panel" aria-labelledby="active-sponsors-heading">
                    <div class="sponsorship-list-heading"><div><p class="seller-panel-kicker">Your network</p><h2 id="active-sponsors-heading">Active retailer websites</h2><p>Retailers currently connected to your wholesale catalogue.</p></div><span class="sponsorship-list-count">{{ $sponsorships->count() }} active</span></div>
                    @forelse ($sponsorships as $sponsorship)
                        <article class="sponsorship-row"><span class="sponsorship-avatar" aria-hidden="true">{{ Str::upper(Str::substr($sponsorship->retailerShop?->name ?? 'R', 0, 1)) }}</span><div><div class="sponsorship-row-title"><h3>{{ $sponsorship->retailerShop?->name ?? 'Retailer shop' }}</h3><span class="sponsorship-status sponsorship-status-{{ $sponsorship->status }}">{{ Str::headline($sponsorship->status) }}</span></div><p>Access ends {{ $sponsorship->expires_at->format('d M Y') }} @if ($sponsorship->grace_until)<span>· Grace ends {{ $sponsorship->grace_until->format('d M Y') }}</span>@endif</p></div>@if ($sponsorship->retailerShop)<a class="sponsorship-view-link" href="{{ $sponsorship->retailerShop->publicUrl() }}" target="_blank" rel="noopener">View retailer shop <span aria-hidden="true">↗</span></a>@endif</article>
                    @empty
                        <div class="sponsorship-empty-state"><span class="sponsorship-empty-icon" aria-hidden="true">♡</span><div><strong>No retailer websites are currently sponsored.</strong><p>Create a retailer account above to extend your collection into a new storefront.</p></div></div>
                    @endforelse
                </section>

                <section class="seller-panel invitation-history-panel" aria-labelledby="invitation-history-heading">
                    <div class="sponsorship-list-heading"><div><p class="seller-panel-kicker">Legacy records</p><h2 id="invitation-history-heading">Invitation history</h2><p>Older invitation records remain available for reference. New retailer shops are created directly above.</p></div><span class="sponsorship-list-count">{{ $invites->count() }} total</span></div>
                    @forelse ($invites as $invite)
                        <article class="invitation-row"><div class="invitation-recipient"><span class="invitation-avatar" aria-hidden="true">{{ Str::upper(Str::substr($invite->email, 0, 1)) }}</span><div><h3>{{ $invite->email }}</h3><p>{{ $invite->term_days }} day sponsorship · expires {{ $invite->expires_at->format('d M Y') }}</p></div></div><div class="invitation-row-actions"><span class="invitation-status invitation-status-{{ $invite->status }}">{{ Str::headline($invite->status) }}</span>@if ($invite->status === 'pending')<form method="post" action="{{ route('seller.sponsorships.revoke', $invite) }}">@csrf @method('delete')<button class="invitation-revoke-button" type="submit">Revoke invitation</button></form>@endif</div></article>
                    @empty
                        <div class="invitation-empty-state"><span class="invitation-empty-icon" aria-hidden="true">↗</span><strong>No legacy invitations.</strong><p>New retailer shops are created directly from the form above.</p></div>
                    @endforelse
                </section>

                <section class="sponsorship-help-panel"><div><p class="seller-overline">How sponsorships work</p><h2>One catalogue, more places to sell.</h2></div><p>Retailers receive their own storefront and can showcase your products. You keep control of the wholesale catalogue and can follow every invitation from this workspace.</p><a class="sponsorship-text-link" href="{{ route('seller.profile.edit') }}">Review shop profile <span aria-hidden="true">→</span></a></section>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-retailer-slug-form]').forEach((form) => {
            const name = form.querySelector('[data-shop-name]');
            const slug = form.querySelector('[data-shop-slug]');

            if (!name || !slug) {
                return;
            }

            let customSlug = slug.value.trim() !== '';
            const slugify = (value) => value.toLowerCase()
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');

            name.addEventListener('input', () => {
                if (!customSlug) {
                    slug.value = slugify(name.value);
                }
            });

            slug.addEventListener('input', () => {
                slug.value = slugify(slug.value);
                customSlug = slug.value !== slugify(name.value);
            });
        });
    </script>
@endpush
