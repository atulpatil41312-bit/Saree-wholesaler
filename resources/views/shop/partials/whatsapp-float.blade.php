@php
    $floatingWhatsappNumber = preg_replace('/\D/', '', (string) ($shop->whatsapp ?? ''));
    $floatingWhatsappUrl = preg_match('/^\d{8,15}$/', $floatingWhatsappNumber) === 1
        ? 'https://wa.me/'.$floatingWhatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shop->publicUrl())
        : null;
@endphp

@if ($floatingWhatsappUrl)
    <a class="wm-whatsapp-float" href="{{ $floatingWhatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Chat with {{ $shop->name }} on WhatsApp">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.6 4.1 1.6 5.8L.2 24l6.6-1.7a11.8 11.8 0 0 0 5.3 1.3h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.1-1.2-6.1-3.5-8.3Zm-8.4 18.1h-.1c-1.7 0-3.4-.5-4.8-1.4l-.3-.2-3.9 1 1-3.8-.2-.3a9.8 9.8 0 1 1 8.3 4.7Zm5.4-7.4c-.3-.2-1.7-.8-2-.9-.3-.1-.5-.2-.7.2-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1-1.6-.8-2.6-1.5-3.6-3.3-.3-.5.3-.5.8-1.6.1-.2.1-.4 0-.6l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.4 1.4 3.6c.2.2 2.3 3.6 5.6 5 .8.4 1.4.6 1.9.8.8.2 1.5.2 2 .1.6-.1 1.7-.7 1.9-1.4.2-.7.2-1.3.1-1.4-.1-.2-.3-.3-.6-.4Z"/></svg>
        <span>WhatsApp</span>
    </a>
@endif
