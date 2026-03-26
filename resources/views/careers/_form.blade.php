@php
    $careerConfig = config('cms-kit.database.careers.items', []);
    $careerRequired = $careerConfig['required'] ?? [];
    $showLanguageUi = config('cms-kit.common.modules.languages', true);
    $item = $career ?? null;
    $isEdit = (bool) $item;
    $jobTypeOptions = array_values(array_unique(array_filter(array_merge(
        $filterOptions['job_type']['options'] ?? [],
        [old('job_type', $item->job_type ?? '')]
    ))));
    $departmentOptions = array_values(array_unique(array_filter(array_merge(
        $departmentOptions ?? [],
        $filterOptions['department']['options'] ?? [],
        [old('department', $item->department ?? '')]
    ))));
@endphp

<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">{{ $isEdit ? 'Edit Vacancy' : 'Add Vacancy' }}</h5>
    </div>
    <div class="card-body p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $formAction }}" method="POST">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Slug</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $item->slug ?? '') }}" placeholder="Auto-generated from title if left empty">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Published Date {!! in_array('published_date', $careerRequired, true) ? '<span class="text-danger">*</span>' : '' !!}</label>
                    <input type="date" name="published_date" class="form-control @error('published_date') is-invalid @enderror" value="{{ old('published_date', optional($item?->published_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                    @error('published_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Classification</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Job Type <span class="text-danger">*</span></label>
                            <select name="job_type" class="form-select @error('job_type') is-invalid @enderror" required>
                                <option value="">Select job type</option>
                                @foreach($jobTypeOptions as $option)
                                    <option value="{{ $option }}" {{ old('job_type', $item->job_type ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('job_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(empty($jobTypeOptions))
                                <small class="text-muted d-block mt-1">Add a `job_type` filter in the common section to populate this dropdown.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
                            <select name="department" class="form-select @error('department') is-invalid @enderror" required>
                                <option value="">Select department</option>
                                @foreach($departmentOptions as $option)
                                    <option value="{{ $option }}" {{ old('department', $item->department ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(empty($departmentOptions))
                                <small class="text-muted d-block mt-1">Add departments first, or configure a `department` filter in the common section.</small>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $item->location ?? '') }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Country</label>
                            <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $item->country ?? '') }}">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Base</label>
                            <input type="text" name="base" class="form-control @error('base') is-invalid @enderror" value="{{ old('base', $item->base ?? '') }}" placeholder="Optional">
                            @error('base')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-light border-start border-primary border-4 py-2 mb-4 shadow-sm" style="font-size: 0.9rem;">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong>Note:</strong> Fill the translated title, summary, and content{{ $showLanguageUi ? ' across all language tabs' : '' }}.
            </div>

            @if($showLanguageUi)
            <ul class="nav nav-pills mb-4 bg-light p-2 rounded-3" id="careerLanguageTabs" role="tablist">
                @foreach($languages as $lang)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }} px-4 py-2 fw-medium" id="career-tab-{{ $lang->code }}" data-bs-toggle="tab" data-bs-target="#career-panel-{{ $lang->code }}" type="button" role="tab">
                        <i class="fas fa-language me-2 opacity-75"></i>{{ $lang->name }}
                    </button>
                </li>
                @endforeach
            </ul>
            @endif

            <div class="tab-content mb-4">
                @foreach($languages as $lang)
                    @php $translation = data_get($item, "translations.{$lang->code}", []); @endphp
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="career-panel-{{ $lang->code }}" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="translations[{{ $lang->code }}][title]" class="form-control @error("translations.{$lang->code}.title") is-invalid @enderror" value="{{ old("translations.{$lang->code}.title", $translation['title'] ?? '') }}" required>
                                @error("translations.{$lang->code}.title")
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Short Description</label>
                                <textarea name="translations[{{ $lang->code }}][short_description]" class="form-control @error("translations.{$lang->code}.short_description") is-invalid @enderror" rows="3">{{ old("translations.{$lang->code}.short_description", $translation['short_description'] ?? '') }}</textarea>
                                @error("translations.{$lang->code}.short_description")
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            @foreach(['about' => 'About', 'responsibilities' => 'Responsibilities', 'requirements' => 'Requirements', 'join_the_team' => 'Join the Team'] as $field => $label)
                            <div class="col-12">
                                <label class="form-label fw-bold">{{ $label }}</label>
                                <textarea name="translations[{{ $lang->code }}][{{ $field }}]" class="form-control tinymce-editor @error("translations.{$lang->code}.{$field}") is-invalid @enderror" rows="8">{{ old("translations.{$lang->code}.{$field}", $translation[$field] ?? '') }}</textarea>
                                @error("translations.{$lang->code}.{$field}")
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-search me-2"></i>SEO Metadata</h6>
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Title</label>
                            <input type="text" name="metadata[meta_title]" class="form-control @error('metadata.meta_title') is-invalid @enderror" value="{{ old('metadata.meta_title', $item->metadata['meta_title'] ?? '') }}">
                            @error('metadata.meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Description</label>
                            <textarea name="metadata[meta_description]" class="form-control @error('metadata.meta_description') is-invalid @enderror" rows="3">{{ old('metadata.meta_description', $item->metadata['meta_description'] ?? '') }}</textarea>
                            @error('metadata.meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Keywords</label>
                            <input type="text" name="metadata[meta_keywords]" class="form-control @error('metadata.meta_keywords') is-invalid @enderror" value="{{ old('metadata.meta_keywords', $item->metadata['meta_keywords'] ?? '') }}" placeholder="keyword1, keyword2">
                            @error('metadata.meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body p-4">
                    <div class="row g-4 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Order</label>
                            <input type="number" name="order_index" class="form-control @error('order_index') is-invalid @enderror" value="{{ old('order_index', $item->order_index ?? $nextOrder) }}" min="1">
                            @error('order_index')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="status" id="careerStatus" value="1" {{ old('status', $item->status ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="careerStatus">Status (Active)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">{{ $submitLabel }}</button>
                <a href="{{ route('cms.careers.vacancies.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    tinymce.init({
        selector: '.tinymce-editor',
        height: 360,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
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
</script>
@endpush
