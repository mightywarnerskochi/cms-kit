@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Career Common Section</li>
@endsection

@section('content')
@php
    $showLanguageUi = config('cms-kit.common.modules.languages', true);
    $sectionTranslations = $section->translations ?? [];
    $existingFilters = old('section_filters', collect(data_get($section->extra_fields, 'filters', []))
        ->map(fn ($filter) => [
            'key' => $filter['key'] ?? '',
            'label' => $filter['label'] ?? '',
            'options' => implode("\n", $filter['options'] ?? []),
        ])->values()->all());
    $filterEnabled = old('filter_enabled', data_get($section->extra_fields, 'filter_enabled', false) ? '1' : '0');
@endphp

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary">Career Common Section</h6>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('cms.careers.update-section') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="alert alert-light border-start border-primary border-4 py-2 mb-4 shadow-sm" style="font-size: 0.9rem;">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong>Note:</strong> This single record controls the page intro, banner, and frontend vacancy filters.
            </div>

            @if($showLanguageUi)
            <ul class="nav nav-pills mb-4 bg-light p-2 rounded-3" id="careerSectionTabs" role="tablist">
                @foreach($languages as $lang)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }} px-4 py-2 fw-medium" id="career-section-tab-{{ $lang->code }}" data-bs-toggle="tab" data-bs-target="#career-section-panel-{{ $lang->code }}" type="button" role="tab">
                        <i class="fas fa-language me-2 opacity-75"></i>{{ $lang->name }}
                    </button>
                </li>
                @endforeach
            </ul>
            @endif

            <div class="tab-content mb-4">
                @foreach($languages as $lang)
                    @php $translation = $sectionTranslations[$lang->code] ?? []; @endphp
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="career-section-panel-{{ $lang->code }}" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="translations[{{ $lang->code }}][title]" class="form-control @error("translations.{$lang->code}.title") is-invalid @enderror" value="{{ old("translations.{$lang->code}.title", $translation['title'] ?? '') }}" required>
                                @error("translations.{$lang->code}.title")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="translations[{{ $lang->code }}][description]" class="form-control @error("translations.{$lang->code}.description") is-invalid @enderror" rows="3">{{ old("translations.{$lang->code}.description", $translation['description'] ?? '') }}</textarea>
                                @error("translations.{$lang->code}.description")
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @include('cms-kit::partials.extra-fields-translatable', [
                                'configKey' => 'careers.section',
                                'lang' => $lang,
                                'existingTranslations' => $section->translations ?? [],
                            ])
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Banner</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Banner Image</label>
                            <input type="file" name="banner" class="form-control @error('banner') is-invalid @enderror" accept="image/*">
                            @error('banner')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($section->banner)
                            <div class="mt-3">
                                <img src="{{ asset('storage/' . $section->banner) }}" alt="{{ $section->banner_alt }}" class="img-fluid rounded border" style="max-height: 160px;">
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Banner Alt Text</label>
                            <input type="text" name="banner_alt" class="form-control @error('banner_alt') is-invalid @enderror" value="{{ old('banner_alt', $section->banner_alt ?? '') }}" placeholder="Describe the banner image">
                            @error('banner_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            @include('cms-kit::partials.extra-fields-global', [
                'configKey' => 'careers.section',
                'existingValues' => $section->extra_fields ?? [],
            ])

            <div class="card bg-light border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Filter Settings</h6>
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Enable Filters</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="filter_enabled" id="filterEnabledYes" value="1" {{ (string) $filterEnabled === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="filterEnabledYes">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="filter_enabled" id="filterEnabledNo" value="0" {{ (string) $filterEnabled !== '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="filterEnabledNo">No</label>
                        </div>
                    </div>

                    <div id="careerFiltersPanel" style="{{ (string) $filterEnabled === '1' ? '' : 'display:none;' }}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">Filter Items</h6>
                                <small class="text-muted">Suggested keys: `job_type`, `department`, `location`, `experience_level`.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addCareerFilter">
                                <i class="fas fa-plus me-1"></i>Add Filter
                            </button>
                        </div>

                        <div id="careerFiltersList">
                            @forelse($existingFilters as $index => $filter)
                                <div class="card border mb-3 career-filter-item">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold">Key</label>
                                                <input type="text" name="section_filters[{{ $index }}][key]" class="form-control" value="{{ $filter['key'] ?? '' }}" placeholder="job_type">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Label</label>
                                                <input type="text" name="section_filters[{{ $index }}][label]" class="form-control" value="{{ $filter['label'] ?? '' }}" placeholder="Job Type">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Options</label>
                                                <textarea name="section_filters[{{ $index }}][options]" class="form-control" rows="4" placeholder="One option per line">{{ $filter['options'] ?? '' }}</textarea>
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-career-filter w-100">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small" id="careerFiltersEmpty">No filters added yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-top pt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>Update Common Section
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const filtersPanel = document.getElementById('careerFiltersPanel');
        const filtersList = document.getElementById('careerFiltersList');
        const emptyStateHtml = '<div class="text-muted small" id="careerFiltersEmpty">No filters added yet.</div>';

        function toggleFiltersPanel() {
            const enabled = document.querySelector('input[name="filter_enabled"]:checked')?.value === '1';
            filtersPanel.style.display = enabled ? '' : 'none';
        }

        function refreshEmptyState() {
            const hasItems = filtersList.querySelector('.career-filter-item');
            const emptyState = document.getElementById('careerFiltersEmpty');

            if (!hasItems && !emptyState) {
                filtersList.insertAdjacentHTML('beforeend', emptyStateHtml);
            }

            if (hasItems && emptyState) {
                emptyState.remove();
            }
        }

        function filterIndex() {
            return filtersList.querySelectorAll('.career-filter-item').length;
        }

        document.querySelectorAll('input[name="filter_enabled"]').forEach((input) => {
            input.addEventListener('change', toggleFiltersPanel);
        });

        document.getElementById('addCareerFilter').addEventListener('click', function () {
            const index = filterIndex();
            const emptyState = document.getElementById('careerFiltersEmpty');
            if (emptyState) {
                emptyState.remove();
            }

            filtersList.insertAdjacentHTML('beforeend', `
                <div class="card border mb-3 career-filter-item">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Key</label>
                                <input type="text" name="section_filters[${index}][key]" class="form-control" placeholder="job_type">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Label</label>
                                <input type="text" name="section_filters[${index}][label]" class="form-control" placeholder="Job Type">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Options</label>
                                <textarea name="section_filters[${index}][options]" class="form-control" rows="4" placeholder="One option per line"></textarea>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-career-filter w-100">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });

        $(document).on('click', '.remove-career-filter', function() {
            $(this).closest('.career-filter-item').remove();
            refreshEmptyState();
        });

        document.addEventListener('invalid', function(e) {
            const invalidTabPane = e.target.closest('.tab-pane');
            if (invalidTabPane) {
                const tabId = invalidTabPane.id;
                const tabBtn = document.querySelector(`[data-bs-target="#${tabId}"]`);
                if (tabBtn && !tabBtn.classList.contains('active')) {
                    bootstrap.Tab.getOrCreateInstance(tabBtn).show();
                    setTimeout(() => { e.target.focus(); }, 150);
                }
            }
        }, true);

        toggleFiltersPanel();
        refreshEmptyState();
    });
</script>
@endpush
