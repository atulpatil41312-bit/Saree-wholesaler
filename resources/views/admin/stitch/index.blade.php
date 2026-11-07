@extends('layouts.storefront')

@section('body_class', 'admin-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/admin.css') }}?v={{ filemtime(public_path('storefront/admin.css')) }}">
    <link rel="stylesheet" href="{{ asset('storefront/admin-stitch.css') }}?v={{ filemtime(public_path('storefront/admin-stitch.css')) }}">
@endpush

@section('content')
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}" aria-label="WholeMarket admin home">
                <span class="admin-brand-mark">W</span>
                <span><strong>WholeMarket</strong><small>Platform control</small></span>
            </a>

            <nav class="admin-nav" aria-label="Admin navigation">
                <span class="admin-nav-label">Workspace</span>
                <a class="admin-nav-link" href="{{ route('admin.dashboard') }}"><span aria-hidden="true">⌂</span>Overview</a>
                <a class="admin-nav-link is-active" href="{{ route('admin.stitch.index') }}"><span aria-hidden="true">✦</span>Stitch studio</a>
                <a class="admin-nav-link" href="{{ route('admin.subscriptions.index') }}"><span aria-hidden="true">◈</span>Subscriptions</a>
                <a class="admin-nav-link" href="{{ route('admin.plans.index') }}"><span aria-hidden="true">◇</span>Plans & access</a>
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-user-chip"><span class="admin-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></span></div>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="admin-signout" type="submit">Sign out <span aria-hidden="true">↗</span></button>
                </form>
            </div>
        </aside>

        <div class="admin-workspace">
            <header class="admin-topbar"><div><p class="admin-overline">Platform operations</p><p class="admin-breadcrumb">Admin / Stitch studio</p></div><div class="admin-topbar-actions"><a class="admin-view-link" href="{{ route('market.index') }}" target="_blank" rel="noopener">View marketplace <span aria-hidden="true">↗</span></a></div></header>

            <main class="admin-content">
                @if (session('status'))
                    <div class="admin-alert admin-alert-success" role="status"><span aria-hidden="true">✓</span>{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="admin-alert admin-alert-error" role="alert"><strong>There’s something to review.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="admin-page-heading" aria-labelledby="stitch-title">
                    <div><p class="admin-overline">Admin-only design workspace</p><h1 id="stitch-title">Stitch template studio</h1><p class="admin-page-intro">Generate private UI drafts with Google Stitch before your team adapts them into a WholeMarket template.</p></div>
                    <span class="stitch-connection-pill {{ $stitchConfigured ? 'is-ready' : 'is-missing' }}"><span aria-hidden="true">{{ $stitchConfigured ? '●' : '!' }}</span>{{ $stitchConfigured ? 'Connected' : 'Needs setup' }}</span>
                </section>

                <section class="admin-metrics" aria-label="Stitch studio metrics">
                    <article class="admin-metric-card admin-metric-primary"><span class="admin-metric-icon" aria-hidden="true">✦</span><div><span class="admin-metric-label">Connection</span><strong>{{ $stitchConfigured ? 'Ready' : 'Setup' }}</strong><small>Server-side only</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">▤</span><div><span class="admin-metric-label">Drafts</span><strong>{{ $drafts->total() }}</strong><small>Private workspace designs</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">✓</span><div><span class="admin-metric-label">Ready previews</span><strong>{{ $drafts->getCollection()->where('status', 'ready')->count() }}</strong><small>HTML and screenshots saved</small></div></article>
                    <article class="admin-metric-card"><span class="admin-metric-icon" aria-hidden="true">◎</span><div><span class="admin-metric-label">Project</span><strong>{{ $stitchConfigured ? 'Linked' : '—' }}</strong><small>Configured in Render</small></div></article>
                </section>

                <section class="admin-layout-grid stitch-layout">
                    <div class="admin-panel stitch-draft-panel">
                        <div class="admin-panel-heading"><div><p class="admin-overline">Private drafts</p><h2>Generated designs</h2></div><span class="admin-count-pill">{{ $drafts->total() }} total</span></div>
                        <div class="stitch-draft-list">
                            @forelse ($drafts as $draft)
                                <article class="stitch-draft-card">
                                    <div class="stitch-draft-card-head"><div><span class="stitch-draft-device">{{ $draft->device_type }}</span><h3>{{ $draft->title }}</h3><p>{{ Str::limit($draft->prompt, 170) }}</p></div><span class="admin-status admin-status-{{ $draft->status === 'ready' ? 'active' : ($draft->status === 'failed' ? 'suspended' : 'pending') }}">{{ Str::headline($draft->status) }}</span></div>
                                    <div class="stitch-draft-meta"><span>Created {{ $draft->created_at->format('d M Y, H:i') }}</span>@if($draft->screen_id)<span>Screen {{ Str::limit($draft->screen_id, 18) }}</span>@endif</div>
                                    @if ($draft->status === 'failed' && $draft->error_message)<p class="stitch-error">{{ $draft->error_message }}</p>@endif
                                    <div class="stitch-draft-actions"><a class="admin-secondary-button" href="{{ route('admin.stitch.show', $draft) }}">Open preview <span aria-hidden="true">→</span></a>@if($draft->status === 'ready' && $draft->html_path)<a class="stitch-text-link" href="{{ route('admin.stitch.html', $draft) }}">Download HTML</a>@endif</div>
                                </article>
                            @empty
                                <div class="admin-empty-state"><span aria-hidden="true">✦</span><h3>No design drafts yet</h3><p>Describe a storefront, dashboard, or logo direction and generate your first private draft.</p></div>
                            @endforelse
                        </div>
                        @if ($drafts->hasPages())<div class="admin-pagination">{{ $drafts->links() }}</div>@endif
                    </div>

                    <aside class="admin-side-column">
                        <section class="admin-panel admin-create-panel" id="new-draft">
                            <div class="admin-panel-heading"><div><p class="admin-overline">Create a draft</p><h2>Describe the direction</h2></div><span class="admin-panel-number">01</span></div>
                            <p class="admin-panel-copy">Stitch creates a screenshot and HTML draft. It never changes a live shop or publishes a template.</p>
                            <form method="post" action="{{ route('admin.stitch.store') }}" class="admin-form">
                                @csrf
                                <label>Draft name<input name="title" value="{{ old('title') }}" maxlength="120" placeholder="Royal wedding collection" required></label>
                                <label>Prompt<textarea name="prompt" rows="8" minlength="20" maxlength="5000" placeholder="Create a premium saree storefront hero with..." required>{{ old('prompt') }}</textarea><small class="stitch-field-hint">Mention layout, tone, colours, responsive behaviour, and the screen you want.</small></label>
                                <label>Preview device<select name="device_type"><option value="DESKTOP" @selected(old('device_type', 'DESKTOP') === 'DESKTOP')>Desktop</option><option value="MOBILE" @selected(old('device_type') === 'MOBILE')>Mobile</option><option value="TABLET" @selected(old('device_type') === 'TABLET')>Tablet</option><option value="AGNOSTIC" @selected(old('device_type') === 'AGNOSTIC')>Responsive / agnostic</option></select></label>
                                <button class="admin-primary-button admin-full-button" type="submit" @disabled(! $stitchConfigured)>Generate private draft <span aria-hidden="true">→</span></button>
                                @unless($stitchConfigured)<p class="stitch-field-hint">Add <code>STITCH_API_KEY</code> and <code>STITCH_PROJECT_ID</code> to Render, then redeploy.</p>@endunless
                            </form>
                        </section>
                        <section class="admin-help-card stitch-safety-card"><span class="admin-help-icon" aria-hidden="true">✓</span><div><h3>Safe by default</h3><p>Generated HTML is stored privately and shown as source. It is not executed inside the admin dashboard, and no storefront data is sent to Stitch automatically.</p></div></section>
                    </aside>
                </section>
            </main>
        </div>
    </div>
@endsection
