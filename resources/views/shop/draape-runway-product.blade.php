@extends('layouts.storefront')

@section('body_class', 'draape-runway')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('templates/draape-runway/style.css') }}">
@endpush

@section('content')
    @php
        $assetBase = asset('storefront');
        $shopUrl = $shop->publicUrl();
        $fallbackImages = ['saree.jpg', 'saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
        $productImages = $product->images->map(fn ($image): string => Storage::url($image->path))->values();
        $primaryImage = $productImages->first() ?: $assetBase.'/'.$fallbackImages[((int) $product->id - 1) % count($fallbackImages)];
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I am interested in '.$product->name.'. Product code: '.$product->code.'. Minimum order: '.$product->minimum_order_quantity.' pieces. Product link: '.$product->publicUrl())
            : null;
        $shopEmail = filter_var($shop->email, FILTER_VALIDATE_EMAIL) ? $shop->email : null;
    @endphp

    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = { theme: { extend: {
            colors: { ink: '#1a1a1a', paper: '#faf8f5', coral: '#ff5349', plum: '#9b1d5f', blush: '#fde2e4', 'soft-gray': '#efeeeb' },
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'], serif: ['Playfair Display', 'Georgia', 'serif'] },
            boxShadow: { 'plum-glow': '0 16px 36px -8px rgba(155, 29, 95, .16)', 'coral-glow': '0 12px 24px -4px rgba(255, 83, 73, .18)' },
        } } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="dr-shell min-h-screen bg-paper font-sans text-ink antialiased">
        @if (! empty($previewingTemplate))
            <div class="fixed inset-x-0 top-0 z-[80] bg-plum px-4 py-2 text-center text-xs font-semibold tracking-wide text-white">Previewing {{ $templateDetails['label'] }} · <a class="underline" href="{{ route('seller.design') }}">Return to design studio</a></div>
        @endif
        <header class="dr-header sticky top-0 z-50 bg-paper/95 shadow-[0_1px_8px_rgba(26,26,26,.06)] backdrop-blur-xl">
            <div class="bg-coral px-3 py-1.5 text-center text-[10px] font-bold uppercase tracking-[.18em] text-white">Runway capsule 04 <span class="mx-2 text-blush">•</span> Direct seller enquiries · {{ $shop->name }}</div>
            <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-4 py-4 sm:px-8 lg:px-12">
                <a class="flex min-w-0 items-center gap-2" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                    @if ($shop->logo_path)<img class="h-9 w-9 rounded-full border-2 border-coral object-cover" src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="36" height="36">@else<span class="flex h-9 w-9 items-center justify-center rounded-full bg-plum font-serif text-lg text-white">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>@endif
                    <span class="truncate font-serif text-xl font-semibold uppercase tracking-[.16em] text-plum sm:text-2xl">{{ $shop->name }}</span>
                </a>
                <nav class="hidden items-center gap-5 text-[10px] font-bold uppercase tracking-[.15em] text-plum sm:flex" aria-label="Product navigation"><a href="{{ $shopUrl }}#catalogue">Catalogue</a><a href="{{ $shopUrl }}#atelier">Blouse studio</a><a href="{{ $shopUrl }}#contact">Contact</a></nav>
                <a class="rounded-full bg-plum px-4 py-2 text-xs font-bold text-white hover:bg-coral" href="{{ $shopUrl }}#catalogue">Back to edit ↗</a>
            </div>
        </header>

        <main class="px-4 py-8 sm:px-8 lg:px-12 lg:py-14">
            <div class="mx-auto max-w-[1440px]">
                <nav class="mb-6 flex items-center gap-2 text-xs text-black/50" aria-label="Breadcrumb"><a class="hover:text-plum" href="{{ $shopUrl }}">Home</a><span>/</span><a class="hover:text-plum" href="{{ $shopUrl }}#catalogue">Runway archive</a><span>/</span><span class="truncate text-ink">{{ $product->name }}</span></nav>
                <section class="grid gap-8 lg:grid-cols-[1.05fr_.95fr] lg:gap-12">
                    <div>
                        <div class="group relative aspect-[4/5] overflow-hidden rounded-[2rem] bg-soft-gray shadow-plum-glow">
                            <img class="h-full w-full object-cover object-top transition duration-700 group-hover:scale-105" data-main-gallery src="{{ $primaryImage }}" alt="{{ $product->name }}" width="1200" height="1500">
                            <div class="absolute left-4 top-4 flex flex-wrap gap-2"><span class="rounded-full bg-coral px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Bestseller drop</span><span class="rounded-full bg-white/90 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-plum">Pre-pleated edit</span></div>
                            <button class="absolute right-4 top-4 flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-plum shadow-sm transition hover:bg-coral hover:text-white" type="button" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">♡</button>
                            <div class="absolute inset-x-4 bottom-4 flex items-center justify-between gap-2"><span class="rounded-full bg-ink/80 px-4 py-2 text-xs font-bold text-white">✦ Watch the seller edit</span><span class="rounded-full bg-white/90 px-3 py-2 text-[10px] font-bold text-plum">4.9/5 · Editorial lookbook</span></div>
                        </div>
                        @if ($productImages->count() > 1)
                            <div class="mt-3 grid grid-cols-4 gap-3" aria-label="Product images">
                                @foreach ($productImages as $index => $image)
                                    <button class="aspect-square overflow-hidden rounded-xl border-2 border-transparent transition hover:border-coral {{ $index === 0 ? 'border-coral' : '' }}" type="button" data-gallery-image="{{ $image }}" aria-label="View image {{ $index + 1 }}"><img class="h-full w-full object-cover" src="{{ $image }}" alt="{{ $product->name }} view {{ $index + 1 }}" width="180" height="180"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col justify-center">
                        <p class="text-[10px] font-bold uppercase tracking-[.3em] text-coral">{{ Str::upper($product->category?->name ?: 'CURATED EDIT') }} · {{ $product->code }}</p>
                        <h1 class="mt-3 font-serif text-4xl leading-tight text-ink sm:text-6xl">{{ $product->name }}</h1>
                        <p class="mt-4 max-w-xl text-base leading-7 text-black/60">{{ $product->description ?: 'A considered saree selected for colour, drape and finish. Ask '.$shop->name.' for current availability and styling guidance.' }}</p>
                        <div class="mt-7 rounded-2xl bg-soft-gray p-5"><div class="flex flex-wrap items-baseline gap-3"><span class="font-serif text-4xl text-plum">{{ $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 0) : 'Price on enquiry' }}</span><span class="rounded-full bg-coral px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Wholesale ready</span></div><p class="mt-2 text-sm text-black/55">Minimum order {{ $product->minimum_order_quantity }} pieces · Confirm dispatch timing directly with the seller.</p></div>
                        <div class="mt-7 grid grid-cols-2 gap-3 border-y border-black/10 py-5 text-sm"><div><span class="block text-[10px] font-bold uppercase tracking-wider text-black/45">Availability</span><strong class="mt-1 block text-plum">{{ $product->availability ?: 'Available' }}</strong></div><div><span class="block text-[10px] font-bold uppercase tracking-wider text-black/45">Fabric</span><strong class="mt-1 block text-plum">{{ $product->fabric?->name ?: 'Ask the seller' }}</strong></div><div><span class="block text-[10px] font-bold uppercase tracking-wider text-black/45">Colour</span><strong class="mt-1 block text-plum">{{ $product->colour ?: 'Ask the seller' }}</strong></div><div><span class="block text-[10px] font-bold uppercase tracking-wider text-black/45">Pack size</span><strong class="mt-1 block text-plum">{{ $product->pack_size ?: 'Flexible' }}</strong></div></div>
                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">@if ($whatsappUrl)<a class="inline-flex flex-1 items-center justify-center rounded-full bg-plum px-6 py-4 text-xs font-bold uppercase tracking-wider text-white shadow-coral-glow transition hover:bg-coral" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp enquiry ↗</a>@elseif ($shopEmail)<a class="inline-flex flex-1 items-center justify-center rounded-full bg-plum px-6 py-4 text-xs font-bold uppercase tracking-wider text-white" href="mailto:{{ $shopEmail }}">Email the seller ↗</a>@endif<a class="inline-flex items-center justify-center rounded-full border border-plum px-6 py-4 text-xs font-bold uppercase tracking-wider text-plum hover:bg-blush" href="{{ $shopUrl }}#catalogue">Keep browsing</a></div>
                        <div class="mt-8 grid gap-3 sm:grid-cols-3"><div class="rounded-2xl bg-blush p-4"><strong class="block font-serif text-lg text-plum">1-minute fit</strong><span class="mt-1 block text-xs text-black/55">Easy drape guidance</span></div><div class="rounded-2xl bg-blush p-4"><strong class="block font-serif text-lg text-plum">Deep pocket</strong><span class="mt-1 block text-xs text-black/55">Thoughtful details</span></div><div class="rounded-2xl bg-blush p-4"><strong class="block font-serif text-lg text-plum">48-hour dispatch</strong><span class="mt-1 block text-xs text-black/55">Seller-confirmed timing</span></div></div>
                    </div>
                </section>
                <section class="mt-14 rounded-[2rem] bg-plum p-7 text-white sm:p-10"><div class="grid gap-8 md:grid-cols-[1.1fr_.9fr] md:items-center"><div><p class="text-[10px] font-bold uppercase tracking-[.3em] text-blush">The WholeMarket edit</p><h2 class="mt-3 font-serif text-3xl sm:text-4xl">A saree that moves with your story.</h2><p class="mt-3 max-w-xl text-sm leading-7 text-white/75">Product names, images, prices, minimums and availability come from {{ $shop->name }}. Ask for a personal curation when you are sourcing for a collection, celebration or store launch.</p></div><div class="rounded-3xl bg-white/10 p-6"><h3 class="font-serif text-2xl">Need a private edit?</h3><p class="mt-2 text-sm text-white/70">Share quantities, colours and delivery notes directly with the seller.</p>@if ($whatsappUrl)<a class="mt-5 inline-flex rounded-full bg-white px-5 py-3 text-xs font-bold uppercase tracking-wider text-plum hover:bg-blush" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Start on WhatsApp ↗</a>@endif</div></div></section>
            </div>
        </main>

        @include('shop.partials.storefront-footer', ['shop' => $shop])
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const image = document.querySelector('[data-main-gallery]');
            document.querySelectorAll('[data-gallery-image]').forEach((button) => button.addEventListener('click', () => {
                if (image) image.src = button.dataset.galleryImage;
                document.querySelectorAll('[data-gallery-image]').forEach((item) => item.classList.remove('border-coral'));
                button.classList.add('border-coral');
            }));
            const key = {{ Illuminate\Support\Js::from('wholemarket:'.$shop->id.':'.$template.':favourites') }};
            const button = document.querySelector('[data-product-id]');
            let saved = [];
            try { saved = JSON.parse(localStorage.getItem(key) || '[]'); } catch { saved = []; }
            const id = button ? String(button.dataset.productId) : null;
            const refresh = () => { if (button && id) { const active = saved.map(String).includes(id); button.textContent = active ? '♥' : '♡'; button.classList.toggle('bg-coral', active); button.classList.toggle('text-white', active); } };
            if (button) button.addEventListener('click', () => { if (! id) return; saved = saved.map(String).includes(id) ? saved.filter((item) => String(item) !== id) : [...saved, id]; localStorage.setItem(key, JSON.stringify(saved)); refresh(); });
            refresh();
        })();
    </script>
@endpush
