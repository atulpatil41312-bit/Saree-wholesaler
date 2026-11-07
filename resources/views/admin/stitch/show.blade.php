@extends('layouts.storefront')

@section('body_class', 'admin-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('storefront/admin.css') }}?v={{ filemtime(public_path('storefront/admin.css')) }}">
    <link rel="stylesheet" href="{{ asset('storefront/admin-stitch.css') }}?v={{ filemtime(public_path('storefront/admin-stitch.css')) }}">
@endpush

@section('content')
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}" aria-label="WholeMarket admin home"><span class="admin-brand-mark">W</span><span><strong>WholeMarket</strong><small>Platform control</small></span></a>
            <nav class="admin-nav" aria-label="Admin navigation"><span class="admin-nav-label">Workspace</span><a class="admin-nav-link" href="{{ route('admin.dashboard') }}"><span aria-hidden="true">⌂</span>Overview</a><a class="admin-nav-link is-active" href="{{ route('admin.stitch.index') }}"><span aria-hidden="true">✦</span>Stitch studio</a><a class="admin-nav-link" href="{{ route('admin.subscriptions.index') }}"><span aria-hidden="true">◈</span>Subscriptions</a><a class="admin-nav-link" href="{{ route('admin.plans.index') }}"><span aria-hidden="true">◇</span>Plans & access</a></nav>
            <div class="admin-sidebar-footer"><div class="admin-user-chip"><span class="admin-avatar">{{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></span></div><form method="post" action="{{ route('logout') }}">@csrf<button class="admin-signout" type="submit">Sign out <span aria-hidden="true">↗</span></button></form></div>
        </aside>

        <div class="admin-workspace">
            <header class="admin-topbar"><div><p class="admin-overline">Platform operations</p><p class="admin-breadcrumb">Admin / Stitch studio / Preview</p></div><div class="admin-topbar-actions"><a class="admin-view-link" href="{{ route('admin.stitch.index') }}">Back to studio <span aria-hidden="true">↩</span></a></div></header>
            <main class="admin-content">
                <section class="admin-page-heading" aria-labelledby="draft-title"><div><p class="admin-overline">Private Stitch draft</p><h1 id="draft-title">{{ $draft->title }}</h1><p class="admin-page-intro">{{ Str::headline($draft->device_type) }} · {{ Str::headline($draft->status) }} · {{ $draft->created_at->format('d M Y, H:i') }}</p></div>@if($draft->html_path)<a class="admin-primary-button" href="{{ route('admin.stitch.html', $draft) }}">Download HTML <span aria-hidden="true">↓</span></a>@endif</section>

                @if ($draft->status === 'failed')<div class="admin-alert admin-alert-error" role="alert"><strong>This draft failed.</strong><p>{{ $draft->error_message ?: 'No additional error details were returned.' }}</p></div>@endif

                <section class="stitch-preview-grid">
                    <article class="admin-panel stitch-preview-panel"><div class="admin-panel-heading"><div><p class="admin-overline">Visual preview</p><h2>Generated screen</h2></div><span class="admin-count-pill">{{ $draft->screen_id ? 'Screen linked' : 'No screen' }}</span></div><div class="stitch-preview-canvas">@if($draft->image_path)<img src="{{ route('admin.stitch.image', $draft) }}" alt="Generated Stitch preview for {{ $draft->title }}">@else<div class="admin-empty-state"><span aria-hidden="true">▧</span><h3>No screenshot available</h3><p>Stitch returned source without a screenshot for this draft.</p></div>@endif</div></article>
                    <aside class="admin-panel stitch-details-panel"><div class="admin-panel-heading"><div><p class="admin-overline">Generation brief</p><h2>Prompt</h2></div></div><div class="stitch-prompt">{{ $draft->prompt }}</div><dl class="stitch-detail-list"><div><dt>Project</dt><dd>{{ $draft->stitch_project_id }}</dd></div><div><dt>Device</dt><dd>{{ $draft->device_type }}</dd></div><div><dt>Status</dt><dd>{{ Str::headline($draft->status) }}</dd></div></dl></aside>
                </section>

                @if($html)<section class="admin-panel stitch-source-panel"><div class="admin-panel-heading"><div><p class="admin-overline">Implementation handoff</p><h2>Generated HTML source</h2></div><a class="stitch-text-link" href="{{ route('admin.stitch.html', $draft) }}">Download source</a></div><pre class="stitch-source"><code>{{ $html }}</code></pre></section>@endif
            </main>
        </div>
    </div>
@endsection
