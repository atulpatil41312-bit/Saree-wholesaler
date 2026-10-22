@extends('layouts.storefront')

@section('content')
    <main>
        <section class="contact">
            <div>
                <div class="eyebrow">WHOLEMARKET RETAILER INVITATION</div>
                <h2>Launch your retailer website</h2>
                <p>{{ $invitation->sponsorShop->name }} is sponsoring a {{ $invitation->term_days }}-day retailer website. This invitation expires {{ $invitation->expires_at->format('d M Y H:i') }}.</p>
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                <form method="post" action="{{ route('retailer.invitations.accept', $token) }}" data-shop-slug-form>
                    @csrf
                    <label>Your name<input name="name" value="{{ old('name', auth()->user()?->name) }}" required></label>
                    <label>Invitation email<input name="email" type="email" value="{{ old('email', auth()->user()?->email ?? $invitation->email) }}" required></label>
                    @guest
                        <label>Create password<input name="password" type="password" minlength="12" required></label>
                        <label>Confirm password<input name="password_confirmation" type="password" minlength="12" required></label>
                    @endguest
                    <label>Retailer shop name<input name="shop_name" value="{{ old('shop_name') }}" required data-shop-name></label>
                    <label>Retailer URL<span>{{ request()->getSchemeAndHttpHost() }}/retailer/</span><input name="slug" value="{{ old('slug') }}" required data-shop-slug></label>
                    <label>Initial design<select name="template">@foreach(\App\Models\Shop::templates() as $key => $name)<option value="{{ $key }}" @selected(old('template', 'classic') === $key)>{{ $name }}</option>@endforeach</select></label>
                    <button class="primary">Create sponsored retailer site</button>
                </form>
                @auth
                    <p>Signed in as {{ auth()->user()->email }}. The invitation email must match this account.</p>
                @endauth
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        const form = document.querySelector('[data-shop-slug-form]');
        if (form) {
            const name = form.querySelector('[data-shop-name]');
            const slug = form.querySelector('[data-shop-slug]');
            const slugify = (value) => value.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            name.addEventListener('input', () => { if (slug.value === '' || slug.value === slugify(name.dataset.previous || '')) slug.value = slugify(name.value); name.dataset.previous = name.value; });
        }
    </script>
@endpush
