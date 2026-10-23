@extends('layouts.storefront')

@section('body_class', 'seller-dashboard-page seller-enquiries-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/seller-dashboard.css') }}?v={{ filemtime(public_path('storefront/seller-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('storefront/seller-enquiries.css') }}?v={{ filemtime(public_path('storefront/seller-enquiries.css')) }}">
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
                <a class="seller-nav-link is-active" href="{{ route('seller.enquiries.index') }}" aria-current="page"><span aria-hidden="true">↗</span>Enquiries</a>
                @if ($shop->isWholesaler())
                    <a class="seller-nav-link" href="{{ route('seller.sponsorships.index') }}"><span aria-hidden="true">♡</span>Retailer sponsors</a>
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
                <div><p class="seller-overline">{{ $shop->isRetailer() ? 'Retailer workspace' : 'Wholesaler workspace' }}</p><p class="seller-breadcrumb">{{ $shop->name }} / Enquiries</p></div>
                <a class="seller-store-link" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">Open storefront <span aria-hidden="true">↗</span></a>
            </header>

            <main class="seller-content enquiries-content">
                @if (session('status'))
                    <div class="seller-alert seller-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="seller-alert seller-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="enquiries-heading">
                    <div>
                        <p class="seller-overline">Leads & follow-up</p>
                        <h1>Customer enquiries</h1>
                        <p class="seller-page-intro">Keep buyer conversations moving and turn interest into your next wholesale order.</p>
                    </div>
                    <div class="enquiries-heading-actions">
                        <span class="enquiries-total"><strong>{{ $enquiryStats['total'] }}</strong> total {{ Str::plural('enquiry', $enquiryStats['total']) }}</span>
                        <a class="seller-secondary-button" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">View live shop <span aria-hidden="true">↗</span></a>
                    </div>
                </section>

                <section class="enquiry-metrics" aria-label="Enquiry summary">
                    <article class="enquiry-metric enquiry-metric-primary"><span class="enquiry-metric-icon" aria-hidden="true">✦</span><div><span>New</span><strong>{{ $enquiryStats['new'] }}</strong><small>Ready for a reply</small></div></article>
                    <article class="enquiry-metric"><span class="enquiry-metric-icon" aria-hidden="true">↗</span><div><span>Contacted</span><strong>{{ $enquiryStats['contacted'] }}</strong><small>Conversations in progress</small></div></article>
                    <article class="enquiry-metric"><span class="enquiry-metric-icon" aria-hidden="true">✓</span><div><span>Closed</span><strong>{{ $enquiryStats['closed'] }}</strong><small>Completed follow-ups</small></div></article>
                </section>

                @if ($enquiries->isEmpty())
                    <section class="seller-panel enquiry-empty-panel">
                        <div class="enquiry-empty-icon" aria-hidden="true">↗</div>
                        <p class="seller-overline">Your inbox is ready</p>
                        <h2>No enquiries yet.</h2>
                        <p>When a buyer contacts you from your storefront, their message and contact details will appear here. Share your shop to start the first conversation.</p>
                        <div class="enquiry-empty-actions"><a class="seller-primary-button" href="{{ $shop->publicUrl() }}" target="_blank" rel="noopener">Open storefront <span aria-hidden="true">↗</span></a><a class="enquiry-text-link" href="{{ route('seller.profile.edit') }}">Complete shop profile <span aria-hidden="true">→</span></a></div>
                    </section>
                @else
                    <section class="seller-panel enquiry-list-panel" aria-labelledby="latest-enquiries-heading">
                        <div class="enquiry-list-heading"><div><p class="seller-panel-kicker">Inbox</p><h2 id="latest-enquiries-heading">Latest conversations</h2></div><span class="enquiry-list-count">{{ $enquiries->total() }} total</span></div>
                        <div class="enquiry-list">
                            @foreach ($enquiries as $enquiry)
                                <article class="enquiry-row">
                                    <div class="enquiry-avatar" aria-hidden="true">{{ Str::upper(Str::substr($enquiry->name, 0, 1)) }}</div>
                                    <div class="enquiry-row-main">
                                        <div class="enquiry-row-topline"><div><h3>{{ $enquiry->name }}</h3><p>{{ $enquiry->product?->name ? 'Product enquiry · '.$enquiry->product->name : 'General shop enquiry' }}</p></div><span class="enquiry-status enquiry-status-{{ $enquiry->status }}">{{ Str::headline($enquiry->status) }}</span></div>
                                        <div class="enquiry-contact"><span>{{ $enquiry->email ?: 'No email provided' }}</span>@if ($enquiry->phone)<span>{{ $enquiry->phone }}</span>@endif<span>{{ $enquiry->created_at->format('d M Y · h:i A') }}</span></div>
                                        <p class="enquiry-message">{{ $enquiry->message }}</p>
                                        <form method="post" action="{{ route('seller.enquiries.update', $enquiry) }}" class="enquiry-status-form">
                                            @csrf
                                            @method('patch')
                                            <label>Follow-up status<select name="status"><option value="new" @selected($enquiry->status === 'new')>New</option><option value="contacted" @selected($enquiry->status === 'contacted')>Contacted</option><option value="closed" @selected($enquiry->status === 'closed')>Closed</option></select></label>
                                            <button class="enquiry-save-button" type="submit">Save status <span aria-hidden="true">→</span></button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        @if ($enquiries->hasPages())
                            <div class="enquiry-pagination">{{ $enquiries->links() }}</div>
                        @endif
                    </section>
                @endif

                <section class="enquiry-help-panel"><div><p class="seller-overline">A simple follow-up rhythm</p><h2>Reply quickly, then keep your pipeline tidy.</h2></div><p>Mark a lead as Contacted once you have replied. Close it when the buyer has what they need, so your New queue always shows who needs attention.</p><a class="enquiry-text-link" href="{{ route('seller.profile.edit') }}">Update contact details <span aria-hidden="true">→</span></a></section>
            </main>
        </div>
    </div>
@endsection
