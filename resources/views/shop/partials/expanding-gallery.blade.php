@php
    $galleryFallbackImages = is_array($productFallbackImages ?? null) && count($productFallbackImages) > 0
        ? array_values($productFallbackImages)
        : ['saree.jpg', 'saree0.jpg', 'saree1.jpg', 'saree2.jpg'];
    $galleryProducts = isset($products) && method_exists($products, 'getCollection')
        ? $products->getCollection()->take(4)
        : collect($products ?? [])->take(4);
    $galleryItems = [];

    foreach ($galleryProducts as $galleryIndex => $galleryProduct) {
        $galleryImage = $galleryProduct->images->first()
            ? Storage::url($galleryProduct->images->first()->path)
            : (isset($assetBase) && $assetBase
                ? $assetBase.'/'.$galleryFallbackImages[$galleryIndex % count($galleryFallbackImages)]
                : asset($galleryFallbackImages[$galleryIndex % count($galleryFallbackImages)]));

        $galleryItems[] = [
            'image' => $galleryImage,
            'name' => $galleryProduct->name,
            'label' => Str::upper($galleryProduct->category?->name ?: 'SAREE EDIT'),
            'url' => $galleryProduct->publicUrl(),
        ];
    }
@endphp

@if (count($galleryItems) > 0)
    <section class="wm-expand-gallery" aria-labelledby="wm-expand-gallery-title">
        <div class="wm-expand-gallery__intro">
            <span class="wm-expand-gallery__eyebrow">A CLOSER LOOK</span>
            <h2 id="wm-expand-gallery-title">Explore the collection.</h2>
            <p>Hover or focus a piece to bring it forward. Select any panel to view its full details.</p>
        </div>

        <div class="wm-expand-gallery__grid" role="list">
            @foreach ($galleryItems as $galleryIndex => $galleryItem)
                <a class="wm-expand-gallery__item" href="{{ $galleryItem['url'] }}" role="listitem">
                    <img src="{{ $galleryItem['image'] }}" alt="{{ $galleryItem['name'] }}" loading="lazy" width="720" height="900">
                    <span class="wm-expand-gallery__shade" aria-hidden="true"></span>
                    <span class="wm-expand-gallery__counter" aria-hidden="true">{{ str_pad((string) ($galleryIndex + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="wm-expand-gallery__copy">
                        <small>{{ $galleryItem['label'] }}</small>
                        <strong>{{ $galleryItem['name'] }}</strong>
                        <em>View details <span aria-hidden="true">↗</span></em>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
@endif
