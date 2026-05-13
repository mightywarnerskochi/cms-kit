@extends('cms-kit::layouts.cms')

@section('title', 'Static texts — ' . $language->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('cms.languages.index') }}">Languages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Static texts — {{ $language->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card glass-card border-0 shadow-sm mb-3">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <h5 class="fw-bold text-primary mb-1">Static site texts — {{ $language->name }}</h5>
                        <p class="text-muted small mb-0">
                            @if($isMaster)
                                You are editing the <strong>master</strong> file ({{ strtoupper($masterCode) }}). Keys are defined in the codebase (English JSON); edit <strong>values</strong> here only. Adding a language copies English keys into a new file so you can translate values.
                            @else
                                Keys match English (read-only). Only <strong>values</strong> can be changed; they are saved to this language’s JSON file.
                            @endif
                        </p>
                    </div>
                    <code class="small text-break align-self-center" style="max-width: 100%;">{{ $jsonFilePath }}</code>
                </div>
            </div>
        </div>

        @can('languages.edit')
            <form method="post" action="{{ route('cms.languages.static-texts.update', strtolower($language->code)) }}">
                @csrf
                @method('PUT')
                <div class="card glass-card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 220px;">Key</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($flat as $k => $v)
                                        <tr>
                                            <td class="ps-4">
                                                <input type="hidden" name="entries[{{ $loop->index }}][key]" value="{{ $k }}">
                                                <span class="form-control-plaintext font-monospace small py-1">{{ $k }}</span>
                                            </td>
                                            <td class="pe-4">
                                                <textarea class="form-control form-control-sm" name="entries[{{ $loop->index }}][value]" rows="2">{{ $v }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                        <a href="{{ route('cms.languages.index') }}" class="btn btn-outline-secondary">Back to languages</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save to JSON file
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="card glass-card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Key</th>
                                    <th class="pe-4">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($flat as $k => $v)
                                    <tr>
                                        <td class="ps-4 font-monospace small">{{ $k }}</td>
                                        <td class="pe-4"><pre class="small mb-0 text-wrap">{{ $v }}</pre></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">No keys in this file yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <a href="{{ route('cms.languages.index') }}" class="btn btn-outline-secondary">Back to languages</a>
                </div>
            </div>
        @endcan
    </div>
</div>

@endsection
