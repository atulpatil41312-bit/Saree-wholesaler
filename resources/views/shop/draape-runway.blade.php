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
        $collectionUrl = ! empty($previewingTemplate) ? route('seller.design.preview', ['template' => $template]) : $shopUrl;
        $fallbackImages = ['saree.jpg', 'saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
        $heroImage = $shop->banner_path ? Storage::url($shop->banner_path) : $assetBase.'/saree.jpg';
        $showPrices = $shop->settings?->show_prices ?? true;
        $whatsappNumber = preg_replace('/\D/', '', (string) $shop->whatsapp);
        $whatsappUrl = preg_match('/^\d{8,15}$/', $whatsappNumber) === 1
            ? 'https://wa.me/'.$whatsappNumber.'?text='.urlencode('Hello, I would like to enquire about '.$shop->name.'. Shop link: '.$shopUrl)
            : null;
        $featureProduct = $products->getCollection()->first();
        $featureImage = $featureProduct?->images?->first()
            ? Storage::url($featureProduct->images->first()->path)
            : $heroImage;
        $categoryImage = static function ($category, int $index) use ($products, $assetBase, $fallbackImages): string {
            $product = $products->getCollection()->firstWhere('category_id', $category->id);

            return $product?->images?->first()
                ? Storage::url($product->images->first()->path)
                : $assetBase.'/'.$fallbackImages[$index % count($fallbackImages)];
        };
    @endphp

    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#1a1a1a',
                        paper: '#faf8f5',
                        coral: '#ff5349',
                        plum: '#9b1d5f',
                        blush: '#fde2e4',
                        'soft-gray': '#efeeeb',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                        serif: ['Playfair Display', 'Georgia', 'serif'],
                    },
                    boxShadow: {
                        'plum-glow': '0 16px 36px -8px rgba(155, 29, 95, .16)',
                        'coral-glow': '0 12px 24px -4px rgba(255, 83, 73, .18)',
                    },
                },
            },
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="dr-shell min-h-screen bg-paper font-sans text-ink antialiased">
        @if (! empty($previewingTemplate))
            <div class="fixed inset-x-0 top-0 z-[80] bg-plum px-4 py-2 text-center text-xs font-semibold tracking-wide text-white">
                Previewing {{ $templateDetails['label'] }} ·
                <a class="underline" href="{{ route('seller.design') }}">Return to design studio</a>
            </div>
        @endif

        <header class="dr-header fixed inset-x-0 top-0 z-50 bg-paper/95 shadow-[0_1px_8px_rgba(26,26,26,.06)] backdrop-blur-xl">
            <div class="bg-coral px-3 py-1.5 text-center text-[10px] font-bold uppercase tracking-[.18em] text-white">
                <span>FREE EXPRESS BLOUSE CUSTOMIZATION ON ORDERS ₹3,999+</span>
                <span class="mx-2 text-blush">•</span>
                <span>DIRECT SELLER ENQUIRIES · {{ $shop->name }}</span>
            </div>
            <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-4 py-3 sm:px-8 lg:px-12">
                <a class="flex min-w-0 items-center gap-2" href="{{ $shopUrl }}" aria-label="{{ $shop->name }} home">
                    @if ($shop->logo_path)
                        <img class="h-9 w-9 rounded-full border-2 border-coral object-cover" src="{{ Storage::url($shop->logo_path) }}" alt="{{ $shop->name }} logo" width="36" height="36">
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-plum font-serif text-lg text-white">{{ Str::upper(Str::substr($shop->name, 0, 1)) }}</span>
                    @endif
                    <span class="truncate font-serif text-xl font-semibold uppercase tracking-[.16em] text-plum sm:text-2xl">{{ $shop->name }}</span>
                </a>
                <form class="hidden max-w-xl flex-1 items-center rounded-full bg-soft-gray px-4 py-2 md:flex" action="{{ $collectionUrl }}" method="get">
                    <span class="mr-2 text-plum" aria-hidden="true">⌕</span>
                    <input class="min-w-0 flex-1 border-0 bg-transparent text-sm outline-none" name="q" value="{{ request('q') }}" placeholder="Search metallic organza, pre-stitched, silk..." aria-label="Search catalogue">
                    <span class="rounded-full bg-white px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-plum">Trending</span>
                </form>
                <div class="flex items-center gap-2 text-sm sm:gap-3">
                    <span class="hidden rounded-full bg-soft-gray px-3 py-2 text-xs font-semibold sm:inline">INR ₹</span>
                    <a class="relative rounded-full p-2 text-plum transition hover:bg-blush" href="#catalogue" aria-label="Saved favourites">♡<span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-plum px-1 text-[9px] font-bold text-white" data-favourite-count>0</span></a>
                    <a class="rounded-full bg-plum px-3 py-2 text-xs font-bold text-white transition hover:bg-coral sm:px-4" href="#catalogue">Shop the drop <span aria-hidden="true">↗</span></a>
                </div>
            </div>
            <nav class="mx-auto flex max-w-[1440px] gap-5 overflow-x-auto border-t border-black/5 px-4 py-2.5 text-[10px] font-bold uppercase tracking-[.15em] text-plum sm:justify-center sm:px-8" aria-label="Main navigation">
                <a class="whitespace-nowrap hover:text-coral" href="#catalogue">Cocktail sarees</a>
                <a class="whitespace-nowrap hover:text-coral" href="#catalogue">Ready-to-wear drapes</a>
                <a class="whitespace-nowrap hover:text-coral" href="#catalogue">Gen-Z metallics</a>
                <a class="whitespace-nowrap hover:text-coral" href="#catalogue">Organza & tulle</a>
                <a class="whitespace-nowrap hover:text-coral" href="#atelier">Blouse studio</a>
                <a class="whitespace-nowrap hover:text-coral" href="#contact">Editorial edit</a>
            </nav>
        </header>

        <main class="pt-[122px] sm:pt-[126px]">
            <section class="bg-blush px-4 py-5 sm:px-8 lg:px-12">
                <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-3 text-[10px] font-bold uppercase tracking-[.14em] text-plum">
                    <span>Runway capsule 04</span>
                    <span class="hidden sm:inline">Express 48-hour dispatch · custom blouse conversations</span>
                    <span>New season ↗</span>
                </div>
            </section>

            <section class="relative overflow-hidden px-4 py-8 sm:px-8 lg:px-12 lg:py-12">
                <div class="mx-auto grid max-w-[1440px] overflow-hidden rounded-[2rem] bg-white shadow-plum-glow lg:grid-cols-[1.08fr_.92fr]">
                    <div class="flex flex-col justify-center p-6 sm:p-10 lg:p-16">
                        <p class="mb-4 text-[10px] font-bold uppercase tracking-[.3em] text-plum">The re-engineered saree · 25</p>
                        <h1 class="max-w-xl font-serif text-5xl font-medium leading-[.96] tracking-[-.04em] text-ink sm:text-6xl lg:text-8xl">
                            {{ $templateDetails['hero_heading'] }}<br>
                            <em class="text-coral">{{ $templateDetails['hero_emphasis'] }}</em>
                        </h1>
                        <p class="mt-5 max-w-lg text-sm leading-7 text-black/60 sm:text-base">{{ $shop->description ?: 'Pre-stitched, featherlight drapes and considered sarees created for collections, celebrations and confident everyday styling.' }}</p>
                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <a class="inline-flex items-center justify-center rounded-full bg-plum px-6 py-3 text-xs font-bold uppercase tracking-wider text-white shadow-coral-glow transition hover:bg-coral" href="#catalogue">Explore ready-to-wear drapes <span class="ml-2">↗</span></a>
                            @if ($whatsappUrl)
                                <a class="inline-flex items-center justify-center rounded-full border border-plum px-6 py-3 text-xs font-bold uppercase tracking-wider text-plum transition hover:bg-blush" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Watch / chat with seller <span class="ml-2">↗</span></a>
                            @endif
                        </div>
                        <div class="mt-7 flex flex-wrap gap-2 text-[10px] font-bold uppercase tracking-wider text-black/60">
                            <span class="rounded-full bg-soft-gray px-3 py-2">1-minute zipped drapes</span>
                            <span class="rounded-full bg-soft-gray px-3 py-2">Pocket sarees available</span>
                            <span class="rounded-full bg-soft-gray px-3 py-2">Dance-proof pleats</span>
                        </div>
                        <div class="mt-8 grid max-w-md grid-cols-3 gap-3 border-t border-black/10 pt-5">
                            <div><strong class="block font-serif text-2xl text-coral">60s</strong><span class="text-[9px] font-bold uppercase tracking-wider text-black/50">wear time</span></div>
                            <div><strong class="block font-serif text-2xl text-coral">0</strong><span class="text-[9px] font-bold uppercase tracking-wider text-black/50">safety pins</span></div>
                            <div><strong class="block font-serif text-2xl text-coral">48k+</strong><span class="text-[9px] font-bold uppercase tracking-wider text-black/50">collections loved</span></div>
                        </div>
                    </div>
                    <div class="relative min-h-[420px] overflow-hidden bg-plum sm:min-h-[560px]">
                        <img class="absolute inset-0 h-full w-full object-cover object-top transition duration-700 hover:scale-105" src="{{ $featureImage }}" alt="{{ $featureProduct?->name ?: $shop->name.' saree collection' }}" width="1200" height="1500">
                        <div class="absolute inset-0 bg-gradient-to-t from-plum/80 via-transparent to-transparent"></div>
                        <div class="absolute bottom-5 left-5 right-5 flex items-end justify-between gap-3 text-white">
                            <div><p class="text-[10px] font-bold uppercase tracking-[.2em] text-blush">Featured drop</p><strong class="mt-1 block max-w-xs font-serif text-2xl">{{ $featureProduct?->name ?: 'A considered saree for your next drop.' }}</strong></div>
                            @if ($featureProduct)<span class="rounded-full bg-white/90 px-3 py-2 text-xs font-bold text-plum">{{ $showPrices && $featureProduct->priceInRupees() !== null ? '₹'.number_format($featureProduct->priceInRupees(), 0) : 'Enquire' }}</span>@endif
                        </div>
                    </div>
                </div>
            </section>

            @if ($categories->isNotEmpty())
                <section class="border-y border-black/5 bg-white px-4 py-6 sm:px-8 lg:px-12">
                    <div class="mx-auto max-w-[1440px]">
                        <div class="mb-4 flex items-center justify-between gap-3"><h2 class="font-serif text-2xl text-ink">Shop by vibe & occasion</h2><span class="hidden text-[10px] font-bold uppercase tracking-wider text-black/50 sm:inline">Tap to filter drops</span></div>
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            @foreach ($categories->take(8) as $category)
                                <a class="whitespace-nowrap rounded-full border border-black/10 bg-paper px-4 py-2 text-xs font-semibold text-ink transition hover:border-coral hover:bg-blush" href="{{ $collectionUrl.'?category='.$category->id }}#catalogue">{{ $category->name }} <span class="text-coral">✦</span></a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            <section id="catalogue" class="px-4 py-12 sm:px-8 lg:px-12 lg:py-20">
                <div class="mx-auto max-w-[1440px]">
                    <div class="mb-7 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                        <div><p class="text-[10px] font-bold uppercase tracking-[.3em] text-coral">Drop 042 · live catalogue</p><h2 class="mt-2 font-serif text-4xl text-ink sm:text-5xl">Runway bestsellers</h2><p class="mt-2 text-sm text-black/55">Instantly shop {{ $products->total() }} curated pieces with wholesale-ready details.</p></div>
                        <form class="flex flex-wrap gap-2" action="{{ $collectionUrl }}" method="get">
                            <input class="min-h-10 rounded-full border border-black/10 bg-white px-4 text-xs outline-none focus:border-coral" type="search" name="q" value="{{ request('q') }}" placeholder="Search the edit" aria-label="Search catalogue">
                            <select class="min-h-10 rounded-full border border-black/10 bg-white px-4 text-xs outline-none focus:border-coral" name="sort"><option value="">Latest arrivals</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option></select>
                            <button class="min-h-10 rounded-full bg-ink px-5 text-xs font-bold uppercase tracking-wider text-white" type="submit">Filter</button>
                        </form>
                    </div>
                    @if ($products->isNotEmpty())
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 lg:gap-5">
                            @foreach ($products as $product)
                                @php
                                    $productImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$fallbackImages[$loop->index % count($fallbackImages)];
                                    $price = $showPrices && $product->priceInRupees() !== null ? '₹'.number_format($product->priceInRupees(), 0) : 'Price on enquiry';
                                @endphp
                                <article class="group overflow-hidden rounded-2xl bg-white shadow-[0_1px_0_rgba(26,26,26,.06)] transition duration-300 hover:-translate-y-1 hover:shadow-plum-glow">
                                    <div class="relative aspect-[4/5] overflow-hidden bg-soft-gray">
                                        <a href="{{ $product->publicUrl() }}"><img class="h-full w-full object-cover transition duration-700 group-hover:scale-105" src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy" width="720" height="900"></a>
                                        <span class="absolute left-2 top-2 rounded-full bg-plum px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-white">{{ Str::upper($product->fabric?->name ?: 'SAREE') }}</span>
                                        <button class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-plum shadow-sm transition hover:bg-coral hover:text-white" type="button" data-product-id="{{ $product->id }}" aria-label="Save {{ $product->name }}" aria-pressed="false">♡</button>
                                        <a class="absolute inset-x-2 bottom-2 rounded-full bg-white/95 px-3 py-2 text-center text-[10px] font-bold uppercase tracking-wider text-plum opacity-0 transition group-hover:opacity-100" href="{{ $product->publicUrl() }}">Quick view ↗</a>
                                    </div>
                                    <div class="p-3 sm:p-4">
                                        <p class="text-[9px] font-bold uppercase tracking-[.14em] text-coral">{{ $product->category?->name ?: 'Curated edit' }}</p>
                                        <h3 class="mt-1 line-clamp-2 min-h-10 font-serif text-base leading-tight text-ink sm:text-lg"><a href="{{ $product->publicUrl() }}">{{ $product->name }}</a></h3>
                                        <div class="mt-3 flex items-center justify-between gap-2"><span class="text-sm font-bold text-plum">{{ $price }}</span><span class="text-[9px] text-black/50">Min {{ $product->minimum_order_quantity }}</span></div>
                                        @include('shop.partials.product-actions', ['shop' => $shop, 'product' => $product])
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        @if ($products->hasPages())
                            <nav class="mt-8 flex justify-center gap-4 text-xs font-semibold text-plum" aria-label="Catalogue pages">
                                @if ($products->previousPageUrl())<a href="{{ $products->previousPageUrl() }}#catalogue">← Previous</a>@endif
                                <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                                @if ($products->nextPageUrl())<a href="{{ $products->nextPageUrl() }}#catalogue">Next →</a>@endif
                            </nav>
                        @endif
                    @else
                        <div class="rounded-2xl border border-dashed border-black/15 bg-white p-12 text-center text-sm text-black/55">The next drop is being prepared. Contact {{ $shop->name }} for a private edit.</div>
                    @endif
                </div>
            </section>

            <section id="atelier" class="bg-soft-gray px-4 py-14 sm:px-8 lg:px-12 lg:py-20">
                <div class="mx-auto max-w-[1200px]">
                    <div class="text-center"><p class="text-[10px] font-bold uppercase tracking-[.3em] text-coral">Blouse architecture lab</p><h2 class="mt-2 font-serif text-4xl text-ink sm:text-5xl">Design your fit in 3 steps</h2><p class="mx-auto mt-3 max-w-xl text-sm text-black/55">No tailors, no frustrating fittings. Pick your cut, submit three basic body dimensions and receive a precision blouse edit.</p></div>
                    <div class="mt-8 grid gap-4 md:grid-cols-3">
                        @foreach (['Select neck & back cut' => 'Choose from signature architectural silhouettes engineered with couture-level construction.', 'Smart fit sizing' => 'Share three measurements and let the seller guide you to the right fit.', '48-hour precision craft' => 'Laser-cut finishing and dispatch updates keep the process beautifully simple.'] as $titleText => $descriptionText)
                            <article class="rounded-3xl bg-white p-6 shadow-sm"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-blush text-xs font-bold text-coral">{{ $loop->iteration < 10 ? '0'.$loop->iteration : $loop->iteration }}</span><h3 class="mt-5 font-serif text-2xl text-ink">{{ $titleText }}</h3><p class="mt-2 text-sm leading-6 text-black/55">{{ $descriptionText }}</p><div class="mt-5 flex gap-2"><span class="rounded-full bg-soft-gray px-3 py-1 text-[10px] font-bold text-plum">Editorial</span><span class="rounded-full bg-soft-gray px-3 py-1 text-[10px] font-bold text-plum">Custom</span></div></article>
                        @endforeach
                    </div>
                    @if ($whatsappUrl)
                        <a class="mt-6 flex items-center justify-between rounded-full bg-plum px-5 py-4 text-sm font-bold text-white shadow-coral-glow transition hover:bg-coral" href="{{ $whatsappUrl }}" target="_blank" rel="noopener"><span>Have an existing drape? <small class="ml-2 hidden font-normal text-white/75 sm:inline">Share your measurements and ask about custom blouse coordination.</small></span><span class="rounded-full bg-white px-4 py-2 text-xs text-plum">Start a fit conversation ↗</span></a>
                    @endif
                </div>
            </section>

            <section class="px-4 py-14 sm:px-8 lg:px-12 lg:py-20">
                <div class="mx-auto max-w-[1440px]">
                    <div class="mb-7 flex items-end justify-between gap-4"><div><p class="text-[10px] font-bold uppercase tracking-[.3em] text-coral">Community in motion</p><h2 class="mt-2 font-serif text-4xl text-ink sm:text-5xl">Tagged on the gram <span class="italic text-plum">#{{ Str::slug($shop->name, '') ?: 'DraapeSquad' }}</span></h2></div><span class="hidden text-[10px] font-bold uppercase tracking-wider text-plum sm:inline">Follow the edit ↗</span></div>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        @foreach ($products->getCollection()->take(4)->values() as $product)
                            @php $socialImage = $product->images->first() ? Storage::url($product->images->first()->path) : $assetBase.'/'.$fallbackImages[$loop->index % count($fallbackImages)]; @endphp
                            <a class="group relative aspect-[4/5] overflow-hidden rounded-2xl bg-plum" href="{{ $product->publicUrl() }}"><img class="h-full w-full object-cover transition duration-700 group-hover:scale-110" src="{{ $socialImage }}" alt="{{ $product->name }}" loading="lazy"><div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/90 to-transparent p-4 pt-16 text-white"><span class="text-[10px] font-bold uppercase tracking-wider">Shop the look ↗</span><strong class="mt-1 block truncate font-serif text-lg">{{ $product->name }}</strong></div></a>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-plum px-4 py-14 text-white sm:px-8 lg:px-12 lg:py-20">
                <div class="mx-auto max-w-[1200px]">
                    <p class="text-center text-[10px] font-bold uppercase tracking-[.3em] text-blush">As praised by top editors & cultural voices</p>
                    <div class="mt-8 grid gap-4 md:grid-cols-3">
                        <blockquote class="rounded-3xl bg-white p-6 text-ink"><strong class="font-serif text-xl">VOGUE</strong><p class="mt-4 text-sm italic leading-6">“A considered edit for the new Indian wardrobe — fluid, confident and ready to move.”</p><cite class="mt-5 block text-[10px] font-bold uppercase tracking-wider text-plum">WholeMarket style desk</cite></blockquote>
                        <blockquote class="rounded-3xl bg-coral p-6 text-white shadow-coral-glow"><strong class="font-serif text-xl">GRAZIA</strong><p class="mt-4 text-sm italic leading-6">“Drape-forward design that turns everyday movement into an occasion.”</p><cite class="mt-5 block text-[10px] font-bold uppercase tracking-wider text-white/80">{{ $shop->name }} editorial</cite></blockquote>
                        <blockquote class="rounded-3xl bg-white p-6 text-ink"><strong class="font-serif text-xl">COSMOPOLITAN</strong><p class="mt-4 text-sm italic leading-6">“The easiest way to find a saree that feels like you — and ship it with confidence.”</p><cite class="mt-5 block text-[10px] font-bold uppercase tracking-wider text-plum">WholeMarket buyer edit</cite></blockquote>
                    </div>
                </div>
            </section>

            <section id="contact" class="px-4 py-14 sm:px-8 lg:px-12 lg:py-20">
                <div class="mx-auto max-w-5xl rounded-[2rem] bg-soft-gray p-7 text-center sm:p-12">
                    <p class="text-[10px] font-bold uppercase tracking-[.3em] text-coral">The {{ Str::upper($shop->name) }} circle</p>
                    <h2 class="mt-3 font-serif text-4xl text-ink sm:text-5xl">Be the first to wear the future of Indian couture.</h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm text-black/55">Ask about drops, VIP pre-stitched drape fittings and collection availability directly from the seller.</p>
                    <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                        @if ($whatsappUrl)<a class="rounded-full bg-plum px-6 py-3 text-xs font-bold uppercase tracking-wider text-white hover:bg-coral" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">WhatsApp {{ $shop->name }} ↗</a>@endif
                        @if (filter_var($shop->email, FILTER_VALIDATE_EMAIL))<a class="rounded-full border border-plum px-6 py-3 text-xs font-bold uppercase tracking-wider text-plum hover:bg-blush" href="mailto:{{ $shop->email }}">Email the studio ↗</a>@endif
                    </div>
                    @if ($shop->instagram_url || $shop->facebook_url)<div class="mt-5">@include('shop.partials.contact-icons', ['shop' => $shop, 'whatsappUrl' => $whatsappUrl, 'instagramUrl' => $shop->instagram_url, 'facebookUrl' => $shop->facebook_url, 'shopEmail' => $shop->email])</div>@endif
                </div>
            </section>
        </main>

        @include('shop.partials.storefront-footer', ['shop' => $shop])
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const key = {{ Illuminate\Support\Js::from('wholemarket:'.$shop->id.':'.$template.':favourites') }};
            let ids = [];
            try { ids = JSON.parse(localStorage.getItem(key) || '[]'); } catch { ids = []; }
            const saved = new Set(Array.isArray(ids) ? ids.map(String) : []);
            const refresh = () => {
                document.querySelectorAll('[data-product-id]').forEach((button) => {
                    const active = saved.has(String(button.dataset.productId));
                    button.textContent = active ? '♥' : '♡';
                    button.setAttribute('aria-pressed', String(active));
                    button.classList.toggle('bg-coral', active);
                    button.classList.toggle('text-white', active);
                });
                document.querySelectorAll('[data-favourite-count]').forEach((count) => { count.textContent = String(saved.size); });
            };
            document.querySelectorAll('[data-product-id]').forEach((button) => button.addEventListener('click', () => {
                const id = String(button.dataset.productId);
                saved.has(id) ? saved.delete(id) : saved.add(id);
                localStorage.setItem(key, JSON.stringify([...saved]));
                refresh();
            }));
            refresh();
        })();
    </script>
@endpush
