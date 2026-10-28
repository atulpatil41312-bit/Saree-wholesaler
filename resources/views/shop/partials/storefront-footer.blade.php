@php
    $footerWhatsappNumber = preg_replace('/\D/', '', (string) ($shop->whatsapp ?? ''));
    $footerWhatsappUrl = preg_match('/^\d{8,15}$/', $footerWhatsappNumber) === 1
        ? 'https://wa.me/'.$footerWhatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shop->publicUrl())
        : null;
    $footerPhoneNumber = preg_replace('/\D/', '', (string) ($shop->phone ?? ''));
    $footerPhoneUrl = preg_match('/^\d{8,15}$/', $footerPhoneNumber) === 1 ? 'tel:'.$footerPhoneNumber : null;
    $footerEmail = filter_var($shop->email ?? null, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    $footerInstagramUrl = filter_var($shop->instagram_url ?? null, FILTER_VALIDATE_URL) && in_array(parse_url($shop->instagram_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->instagram_url : null;
    $footerFacebookUrl = filter_var($shop->facebook_url ?? null, FILTER_VALIDATE_URL) && in_array(parse_url($shop->facebook_url, PHP_URL_SCHEME), ['http', 'https'], true) ? $shop->facebook_url : null;
    $footerAddress = collect([$shop->shop_number, $shop->address])->filter(fn ($value): bool => filled($value))->implode(', ');
@endphp

<footer class="wm-site-footer">
    <div class="wm-site-footer__inner">
        <div class="wm-site-footer__brand-column">
            <a class="wm-site-footer__brand" href="{{ $shop->publicUrl() }}" aria-label="{{ $shop->name }} home">
                @if ($shop->logo_path)
                    <img src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="56" height="56">
                @else
                    <span class="wm-site-footer__mark" aria-hidden="true">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                @endif
                <span>{{ $shop->name }}<small>{{ $templateDetails['label'] ?? 'Saree collection' }}</small></span>
            </a>
            <p class="wm-site-footer__tagline">Beautiful sarees, thoughtfully presented for your next collection.</p>
            <a class="wm-site-footer__market-link" href="{{ route('market.index') }}">Powered by WholeMarket <span aria-hidden="true">↗</span></a>
        </div>

        <div class="wm-site-footer__column">
            <h2>Shop now</h2>
            <a href="{{ $shop->publicUrl() }}#collection">View collection</a>
            <a href="{{ $shop->publicUrl() }}#categories">Browse categories</a>
            <a href="{{ $shop->publicUrl() }}#contact">Ask about availability</a>
            @if ($footerWhatsappUrl)
                <a href="{{ $footerWhatsappUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp enquiry</a>
            @endif
        </div>

        <div class="wm-site-footer__column">
            <h2>Useful links</h2>
            <a href="{{ $shop->publicUrl() }}#about">About the shop</a>
            <a href="{{ $shop->publicUrl() }}#collection">Saree catalogue</a>
            <a href="{{ route('market.index') }}">Explore WholeMarket</a>
            <a href="{{ $shop->publicUrl() }}#contact">Contact the seller</a>
        </div>

        <div class="wm-site-footer__column wm-site-footer__contact">
            <h2>Contact us</h2>
            @if ($footerWhatsappUrl || $footerInstagramUrl || $footerFacebookUrl || $footerEmail)
                <div class="wm-site-footer__socials">
                    @include('shop.partials.contact-icons', [
                        'shop' => $shop,
                        'whatsappUrl' => $footerWhatsappUrl,
                        'instagramUrl' => $footerInstagramUrl,
                        'facebookUrl' => $footerFacebookUrl,
                        'shopEmail' => $footerEmail,
                    ])
                </div>
            @endif
            @if ($footerAddress)
                <p>{{ $footerAddress }}</p>
            @endif
            @if ($shop->opening_hours)
                <p><strong>Hours</strong><br>{{ $shop->opening_hours }}</p>
            @endif
            @if ($footerPhoneUrl)
                <a href="{{ $footerPhoneUrl }}">{{ $shop->phone }}</a>
            @endif
            @if ($footerEmail)
                <a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a>
            @endif
        </div>
    </div>

    <div class="wm-site-footer__bottom">
        <span>© {{ now()->year }} {{ $shop->name }}. All rights reserved.</span>
        <span>{{ $templateDetails['label'] ?? 'Saree collection' }} · Direct seller enquiries</span>
    </div>
</footer>
