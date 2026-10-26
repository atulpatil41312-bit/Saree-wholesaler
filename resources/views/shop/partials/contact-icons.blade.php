@php
    $contactWhatsappUrl = isset($whatsappUrl) && $whatsappUrl
        ? $whatsappUrl
        : null;
    if (! $contactWhatsappUrl && isset($shop)) {
        $contactWhatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        if (preg_match('/^\d{8,15}$/', $contactWhatsappNumber) === 1) {
            $contactWhatsappUrl = 'https://wa.me/'.$contactWhatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shop->publicUrl());
        }
    }
    $contactInstagramUrl = isset($instagramUrl) && $instagramUrl ? $instagramUrl : null;
    $contactFacebookUrl = isset($facebookUrl) && $facebookUrl ? $facebookUrl : null;
    $contactEmail = isset($shopEmail) && $shopEmail ? $shopEmail : (isset($shop) && filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null);
@endphp

@if ($contactWhatsappUrl || $contactInstagramUrl || $contactFacebookUrl || $contactEmail)
    <nav class="wm-contact-icons" aria-label="Social links">
        @if ($contactWhatsappUrl)
            <a class="wm-contact-icon wm-contact-icon-whatsapp" href="{{ $contactWhatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Chat with {{ $shop->name }} on WhatsApp">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.6 4.1 1.6 5.8L.2 24l6.6-1.7a11.8 11.8 0 0 0 5.3 1.3h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.1-1.2-6.1-3.5-8.3Zm-8.4 18.1h-.1c-1.7 0-3.4-.5-4.8-1.4l-.3-.2-3.9 1 1-3.8-.2-.3a9.8 9.8 0 1 1 8.3 4.7Zm5.4-7.4c-.3-.2-1.7-.8-2-.9-.3-.1-.5-.2-.7.2-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1-1.6-.8-2.6-1.5-3.6-3.3-.3-.5.3-.5.8-1.6.1-.2.1-.4 0-.6l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.3 3.6 5.6 5 .8.4 1.4.6 1.9.8.8.2 1.5.2 2 .1.6-.1 1.7-.7 1.9-1.4.2-.7.2-1.3.1-1.4-.1-.2-.3-.3-.6-.4Z"/></svg>
                <span>WhatsApp</span>
            </a>
        @endif
        @if ($contactInstagramUrl)
            <a class="wm-contact-icon wm-contact-icon-instagram" href="{{ $contactInstagramUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Visit {{ $shop->name }} on Instagram">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.4" cy="6.6" r="1" class="wm-icon-fill"/></svg>
                <span>Instagram</span>
            </a>
        @endif
        @if ($contactFacebookUrl)
            <a class="wm-contact-icon wm-contact-icon-facebook" href="{{ $contactFacebookUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Visit {{ $shop->name }} on Facebook">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 8h3V4h-3c-3 0-5 2-5 5v3H6v4h3v4h4v-4h3l1-4h-4V9c0-.7.3-1 1-1Z"/></svg>
                <span>Facebook</span>
            </a>
        @endif
        @if ($contactEmail)
            <a class="wm-contact-icon wm-contact-icon-email" href="mailto:{{ $contactEmail }}" aria-label="Email {{ $shop->name }}">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 5h18a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm0 3v.4l9 5.7 9-5.7V8l-9 5.7L3 8Z"/></svg>
                <span>Email</span>
            </a>
        @endif
    </nav>
@endif
