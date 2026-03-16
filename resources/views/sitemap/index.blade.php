@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Sitemap / SEO</li>
@stop

@section('content')
<div class="sitemap-card glass-card p-5 text-center mx-auto" style="max-width: 700px;">
    <h2 class="fw-bold mb-3">Sitemap Management</h2>
    
    <div class="mb-4">
        @if($exists)
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                <i class="fas fa-check-circle me-2"></i> sitemap.xml exists
            </span>
        @else
            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                <i class="fas fa-times-circle me-2"></i> sitemap.xml is missing
            </span>
        @endif
    </div>

    <p class="text-muted mb-5">
        Automatically generate and update your sitemap to improve SEO. 
        The system monitors content changes, but you can manually trigger a full crawl here.
    </p>

    <div class="row g-3 justify-content-center">
        <div class="col-md-6">
            <form action="{{ route('cms.sitemap.generate') }}" method="GET">
                <button type="submit" class="btn btn-primary btn-premium w-100 py-3">
                    <i class="fas fa-sync-alt me-2"></i> {{ $exists ? 'Regenerate Sitemap' : 'Generate Sitemap' }}
                </button>
            </form>
        </div>
        
        @if($exists)
        <div class="col-md-6">
            <a href="{{ route('cms.sitemap.edit') }}" class="btn btn-warning btn-premium w-100 py-3 text-white">
                <i class="fas fa-edit me-2"></i> Edit Manually
            </a>
        </div>
        <div class="col-12 mt-3">
            <a href="{{ url('sitemap.xml') }}" target="_blank" class="btn btn-light border w-100 py-2">
                <i class="fas fa-external-link-alt me-2"></i> View Sitemap File
            </a>
        </div>
        @endif
    </div>
</div>

@stop
