@extends('cms-kit::layouts.cms')

@section('title', 'Static site texts')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('cms.languages.index') }}">Languages</a></li>
    <li class="breadcrumb-item active" aria-current="page">Static site texts</li>
@endsection

@section('content')
@php
    $vueStaticTpl = config('cms-kit.static_translations.vue_editor_url');
@endphp
<div class="row">
    <div class="col-12">
        <div class="card glass-card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-primary mb-2">Static site texts</h5>
                <p class="text-muted mb-4">
                    These JSON files hold copy that is not stored in the CMS database (for example labels used only on the public site).
                    <strong>{{ strtoupper($masterCode) }}.json</strong> is the master; new keys are added during front-end development. In the panel you edit <strong>values</strong> only. Adding a language creates a new JSON file by copying English keys so you can translate the values.
                </p>

                <div class="table-responsive">
                    <table class="table premium-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-0">Language</th>
                                <th>Code</th>
                                <th class="text-end pe-0">File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($languages as $lang)
                                @php
                                    $c = strtolower($lang->code);
                                    $editHref = is_string($vueStaticTpl) && trim($vueStaticTpl) !== ''
                                        ? str_replace(['{code}', '{CODE}'], [$c, strtoupper($c)], trim($vueStaticTpl))
                                        : route('cms.languages.static-texts.edit', $c);
                                @endphp
                                <tr>
                                    <td class="ps-0 align-middle">
                                        <a href="{{ $editHref }}" class="fw-semibold text-decoration-none">
                                            {{ $lang->name }}
                                            @if($c === strtolower($masterCode))
                                                <span class="badge bg-primary ms-1">Master</span>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="align-middle"><code class="text-primary">{{ $c }}</code></td>
                                    <td class="text-end pe-0 align-middle">
                                        <a href="{{ $editHref }}" class="btn btn-sm btn-light border" title="Edit values"><i class="fas fa-file-lines text-secondary"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="text-muted small mb-0 mt-3">
                    Files are stored under <code>{{ str_replace('\\', '/', $directory) }}</code>
                    (see <code>config/cms-kit.php</code> → <code>static_translations</code>).
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
