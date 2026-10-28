@php($editing = isset($product) && $product->exists)

<form method="post" action="{{ $editing ? route('seller.products.update', $product) : route('seller.products.store') }}" class="seller-product-editor-form" enctype="multipart/form-data">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="seller-editor-section">
        <div class="seller-editor-section-heading"><span class="seller-panel-kicker">01 · Product details</span><h2>Tell buyers what makes it special</h2><p>Use a clear name and design code so retailers can find the right saree quickly.</p></div>
        <div class="seller-editor-grid">
            <label class="seller-field-wide">Product name <span>Visible in your catalogue</span><input name="name" value="{{ old('name', $product->name ?? '') }}" placeholder="Ivory sequin party saree" required autofocus></label>
            <label>Category <span>Organise your collection</span><select name="category_id" required>@foreach ($shop->categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label>Design code <span>Example: RS-001</span><input name="code" value="{{ old('code', $product->code ?? '') }}" placeholder="RS-001" required></label>
            <label>Wholesale price (₹) <span>Optional · per piece</span><input name="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price ?? '') }}" inputmode="decimal" placeholder="2450"></label>
            <label>Minimum order quantity <span>Smallest buyer order</span><input name="minimum_order_quantity" type="number" min="1" value="{{ old('minimum_order_quantity', $product->minimum_order_quantity ?? 1) }}" required></label>
            <label>Pack size <span>Optional</span><input name="pack_size" type="number" min="1" value="{{ old('pack_size', $product->pack_size ?? '') }}" placeholder="6"></label>
            <label>Availability <span>Shown to buyers</span><select name="availability"><option value="available" @selected(old('availability', $product->availability ?? 'available') === 'available')>Available</option><option value="out_of_stock" @selected(old('availability', $product->availability ?? '') === 'out_of_stock')>Out of stock</option><option value="on_enquiry" @selected(old('availability', $product->availability ?? '') === 'on_enquiry')>On enquiry</option></select></label>
        </div>
    </div>

    <div class="seller-editor-section">
        <div class="seller-editor-section-heading"><span class="seller-panel-kicker">02 · Product photography</span><h2>Show the drape clearly</h2><p>Upload up to {{ $limits['product_image_limit'] }} photos. Images are resized and compressed automatically before they are stored.</p></div>
        <label class="seller-editor-upload" for="product-images"><span class="seller-upload-label"><span>Product photos</span><small>JPG, PNG, GIF or WebP · up to 5 MB each</small></span><span class="seller-upload-dropzone"><span class="seller-upload-icon" aria-hidden="true">↑</span><span><strong>Choose product images</strong><small>Use a full model view, detail crop or border close-up</small></span><span class="seller-upload-button">Browse</span></span><input id="product-images" name="images[]" type="file" accept="image/png,image/jpeg,image/gif,image/webp" multiple data-editor-image-picker data-max-images="{{ $limits['product_image_limit'] }}"><span class="seller-upload-preview" data-editor-image-preview aria-live="polite"></span></label>

        @if ($editing && $product->images->isNotEmpty())
            <div class="seller-existing-images"><div class="seller-existing-images-heading"><strong>Saved photos</strong><span>{{ $product->images->count() }} {{ Str::plural('photo', $product->images->count()) }}</span></div><div class="seller-existing-image-grid">@foreach ($product->images as $image)<div class="seller-existing-image"><img src="{{ Storage::url($image->path) }}" alt="{{ $image->alt_text ?: $product->name }}"><button type="submit" form="remove-product-image-{{ $image->id }}" aria-label="Remove image {{ $loop->iteration }}">×</button></div>@endforeach</div></div>
        @elseif ($editing)
            <p class="seller-editor-empty">No photos saved yet. Add your first product image above.</p>
        @endif
    </div>

    @if ($errors->any())
        <div class="seller-alert seller-alert-error" role="alert"><strong>Check the product details.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="seller-editor-actions"><a class="seller-secondary-button" href="{{ route('seller.products.index') }}">Cancel</a><button class="seller-primary-button" type="submit">{{ $editing ? 'Save product changes' : 'Add product to catalogue' }} <span aria-hidden="true">→</span></button></div>
</form>

@if ($editing)
    @foreach ($product->images as $image)
        <form id="remove-product-image-{{ $image->id }}" method="post" action="{{ route('seller.products.images.destroy', [$product, $image]) }}">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif

@push('scripts')
    <script>
        (() => {
            const picker = document.querySelector('[data-editor-image-picker]');
            const preview = document.querySelector('[data-editor-image-preview]');

            if (!picker || !preview) {
                return;
            }

            picker.addEventListener('change', () => {
                preview.replaceChildren();
                const files = Array.from(picker.files || []).slice(0, Number(picker.dataset.maxImages || 1));

                files.forEach((file) => {
                    if (!file.type.startsWith('image/')) {
                        return;
                    }

                    const item = document.createElement('span');
                    item.className = 'seller-upload-preview-item';
                    const image = document.createElement('img');
                    image.alt = file.name;
                    image.src = URL.createObjectURL(file);
                    image.addEventListener('load', () => URL.revokeObjectURL(image.src), { once: true });
                    item.append(image);
                    preview.append(item);
                });

                if (files.length > 0) {
                    const count = document.createElement('small');
                    count.className = 'seller-upload-count';
                    count.textContent = `${files.length} ${files.length === 1 ? 'photo' : 'photos'} selected`;
                    preview.append(count);
                }
            });
        })();
    </script>
@endpush
