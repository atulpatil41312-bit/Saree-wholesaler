@php
    $productUrl = $product->publicUrl();
    $whatsappNumber = preg_replace('/\D/', '', (string) ($shop->whatsapp ?? ''));
    $hasWhatsapp = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1;
    $whatsappUrl = $hasWhatsapp
        ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$productUrl.'. Shop link: '.$shop->publicUrl())
        : null;
    $shopEmail = filter_var($shop->email ?? null, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
@endphp

<div class="wm-product-actions" aria-label="Product actions">
    <a class="wm-product-action-primary" href="{{ $productUrl }}">View details <span aria-hidden="true">→</span></a>
    @if ($whatsappUrl)
        <a class="wm-product-action-secondary" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp enquiry <span aria-hidden="true">↗</span></a>
    @elseif ($shopEmail)
        <a class="wm-product-action-secondary" href="mailto:{{ $shopEmail }}?subject={{ urlencode('Enquiry: '.$product->code) }}">Email enquiry <span aria-hidden="true">↗</span></a>
    @endif
</div>
