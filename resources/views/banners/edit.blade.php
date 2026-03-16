@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('cms.banners.index') }}">Home Banners</a></li>
    <li class="breadcrumb-item active">Edit Banner</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Dynamic Banner</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('cms.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            @if(count($allowedTypes) > 1)
            <div class="row mb-5">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Banner Type</label>
                    <select name="banner_type" id="banner_type" class="form-select">
                        @foreach($allowedTypes as $type)
                        <option value="{{ $type }}" {{ $banner->banner_type === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @else
            <input type="hidden" name="banner_type" id="banner_type" value="{{ $banner->banner_type }}">
            @endif

            <div class="alert alert-light border-start border-primary border-4 py-2 mb-4 shadow-sm" style="font-size: 0.9rem;">
                <i class="fas fa-info-circle text-primary me-2"></i> 
                <strong>Note:</strong> Please ensure all required fields <span class="text-danger">(*)</span> are filled across all language tabs.
            </div>

            <!-- Improved Language Switcher -->
            <ul class="nav nav-pills mb-4 bg-light p-2 rounded-3" id="languageTabs" role="tablist">
                @foreach($languages as $lang)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }} px-4 py-2 fw-medium" id="tab-{{ $lang->code }}" data-bs-toggle="tab" data-bs-target="#panel-{{ $lang->code }}" type="button" role="tab">
                        <i class="fas fa-language me-2 opacity-75"></i>{{ $lang->name }}
                    </button>
                </li>
                @endforeach
            </ul>

            <div class="tab-content mb-4">
                @foreach($languages as $lang)
                @php
                    $trans = $banner->translations[$lang->code] ?? [];
                    $buttons = $trans['buttons'] ?? [['label' => '', 'url' => '']];
                @endphp
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="panel-{{ $lang->code }}" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold">Line 1 (Heading text) <span class="text-danger">*</span></label>
                            <input type="text" name="translations[{{ $lang->code }}][line_1]" class="form-control @error("translations.{$lang->code}.line_1") is-invalid @enderror" value="{{ old("translations.{$lang->code}.line_1", $trans['line_1'] ?? '') }}" required>
                            @error("translations.{$lang->code}.line_1")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-1 text-muted">The main heading text displayed on the banner.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Line 2 (Sub-heading)</label>
                            <input type="text" name="translations[{{ $lang->code }}][line_2]" class="form-control @error("translations.{$lang->code}.line_2") is-invalid @enderror" value="{{ old("translations.{$lang->code}.line_2", $trans['line_2'] ?? '') }}">
                            @error("translations.{$lang->code}.line_2")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-1 text-muted">A secondary line of text for additional impact.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Banner description</label>
                            <textarea name="translations[{{ $lang->code }}][content]" class="form-control @error("translations.{$lang->code}.content") is-invalid @enderror" rows="3">{{ old("translations.{$lang->code}.content", $trans['content'] ?? '') }}</textarea>
                            @error("translations.{$lang->code}.content")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-1 text-muted">Short descriptive text appearing under the headings.</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold d-block mt-3 border-bottom pb-2">Action Buttons</label>
                            <div class="buttons-container-{{ $lang->code }} bg-light p-3 rounded-3 mb-2">
                                @foreach($buttons as $index => $btn)
                                <div class="button-row row g-2 mb-2">
                                    <div class="col-md-5">
                                        <input type="text" name="translations[{{ $lang->code }}][buttons][{{ $index }}][label]" class="form-control" placeholder="Button Label" value="{{ old("translations.{$lang->code}.buttons.{$index}.label", $btn['label'] ?? '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="translations[{{ $lang->code }}][buttons][{{ $index }}][url]" class="form-control" placeholder="Button URL" value="{{ old("translations.{$lang->code}.buttons.{$index}.url", $btn['url'] ?? '') }}">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-button"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-primary mt-2 add-button rounded-pill px-3 shadow-sm" data-lang="{{ $lang->code }}">
                                <i class="fas fa-plus me-1"></i> Add Another Button
                            </button>
                            <div class="form-text mt-2">Add or update call-to-action buttons for this banner.</div>
                        </div>

                        <div class="col-12 mt-4">
                            <label class="form-label fw-bold">Social Proof (e.g., Google reviews text)</label>
                            <input type="text" name="translations[{{ $lang->code }}][google_review_text]" class="form-control @error("translations.{$lang->code}.google_review_text") is-invalid @enderror" value="{{ old("translations.{$lang->code}.google_review_text", $trans['google_review_text'] ?? '') }}" placeholder="e.g., Based on 500 Reviews">
                            @error("translations.{$lang->code}.google_review_text")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-1 text-muted">Optional text to highlight positive client feedback.</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Media & Settings</h6>
                    <div class="row g-4">
                        <div class="col-md-6 {{ $banner->banner_type !== 'image' ? 'd-none' : '' }} {{ !in_array('image', $allowedTypes) ? 'd-none' : '' }}" id="image-section">
                            <label class="form-label">Banner Image</label>
                            <input type="file" name="image" class="form-control">
                            @if($banner->image)
                            <div class="mt-2 p-2 border rounded d-inline-block bg-white">
                                <img src="{{ asset('storage/' . $banner->image) }}" style="height: 80px;">
                            </div>
                            @endif
                            <small class="text-muted d-block mt-1">Recommended: {{ $mainImageConfig['width'] }}x{{ $mainImageConfig['height'] }}px (Max: {{ $mainImageConfig['max_size'] / 1024 }}MB)</small>
                        </div>
                        <div class="col-md-12 {{ $banner->banner_type !== 'video' ? 'd-none' : '' }} {{ !in_array('video', $allowedTypes) ? 'd-none' : '' }}" id="video-section">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label d-block fw-bold">Video Source</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input video-source-toggle" type="radio" name="video_source" id="video_url_source" value="url" {{ !$banner->video_file ? 'checked' : '' }}>
                                        <label class="form-check-label" for="video_url_source">Video URL (YouTube/Vimeo)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input video-source-toggle" type="radio" name="video_source" id="video_file_source" value="file" {{ $banner->video_file ? 'checked' : '' }}>
                                        <label class="form-check-label" for="video_file_source">Upload Video File</label>
                                    </div>
                                </div>
                                <div class="col-md-12 {{ $banner->video_file ? 'd-none' : '' }}" id="video-url-input">
                                    <label class="form-label">Video URL <span class="text-danger">*</span></label>
                                    <input type="text" name="video_url" class="form-control" placeholder="YouTube/Vimeo or self-hosted URL" value="{{ old('video_url', $banner->video_url) }}">
                                </div>
                                <div class="col-md-12 {{ !$banner->video_file ? 'd-none' : '' }}" id="video-file-input">
                                    <label class="form-label">Upload Video <span class="text-danger">*</span></label>
                                    <input type="file" name="video_file" class="form-control">
                                    @if($banner->video_file)
                                    <div class="mt-2 p-2 border rounded d-inline-block bg-white">
                                        <video height="80" controls>
                                            <source src="{{ asset('storage/' . $banner->video_file) }}">
                                            Your browser does not support the video tag.
                                        </video>
                                        <div class="small text-muted mt-1">{{ basename($banner->video_file) }}</div>
                                    </div>
                                    @endif
                                    <small class="text-muted d-block mt-1">Max size: {{ config('cms-kit.images.banners.banner_video.max_size', 10240) / 1024 }}MB. Supported: mp4, mov, avi.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image Alt Text</label>
                            <input type="text" name="image_alt" class="form-control" value="{{ $banner->image_alt }}">
                        </div>
                    </div>
                </div>
            </div>

            @php
                $extra = $banner->extra_fields ?? [];
                $avatars = $extra['google_avatars'] ?? [];
            @endphp
            <div class="card border-primary border-opacity-25 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="fab fa-google me-2"></i>Google Reviews Stats</h6>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">Google Rating</label>
                            <input type="text" name="google_rating" class="form-control" value="{{ $extra['google_rating'] ?? '' }}" placeholder="e.g. 5.0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Trusted Clients Count</label>
                            <input type="text" name="google_review_count" class="form-control" value="{{ $extra['google_review_count'] ?? '' }}" placeholder="e.g. 8231">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Client Avatars (Multiple)</label>
                            <input type="file" name="google_avatars[]" class="form-control" multiple>
                            @if(!empty($avatars))
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                @foreach($avatars as $avatar)
                                <img src="{{ asset('storage/' . $avatar) }}" class="rounded-circle border" style="width: 30px; height: 30px; object-fit: cover;">
                                @endforeach
                            </div>
                            @endif
                            <small class="text-muted d-block mt-1">Recommended: {{ $avatarConfig['width'] }}x{{ $avatarConfig['height'] }}px (Max: {{ $avatarConfig['max_size'] }}KB)</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Client Avatars Alt Text</label>
                            <input type="text" name="google_avatars_alt" class="form-control" value="{{ $extra['google_avatars_alt'] ?? '' }}" placeholder="e.g. Happy Clients">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="order_index" class="form-control" value="{{ $banner->order_index }}">
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch pt-4">
                        <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" {{ $banner->status ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="statusSwitch">Active Status</label>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4">
                <button type="submit" class="btn btn-primary px-5">Update Banner</button>
                <a href="{{ route('cms.banners.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Banner Type Toggle
    $('#banner_type').change(function() {
        if ($(this).val() === 'video') {
            $('#video-section').removeClass('d-none');
            $('#image-section').addClass('d-none');
        } else {
            $('#image-section').removeClass('d-none');
            $('#video-section').addClass('d-none');
        }
    });

    // Video Source Toggle
    $('.video-source-toggle').change(function() {
        if ($(this).val() === 'url') {
            $('#video-url-input').removeClass('d-none');
            $('#video-file-input').addClass('d-none');
        } else {
            $('#video-file-input').removeClass('d-none');
            $('#video-url-input').addClass('d-none');
        }
    });

    // Dynamic Buttons
    $('.add-button').click(function() {
        const lang = $(this).data('lang');
        const container = $('.buttons-container-' + lang);
        const index = container.find('.button-row').length;
        
        const html = `
            <div class="button-row row g-2 mb-2">
                <div class="col-md-5">
                    <input type="text" name="translations[${lang}][buttons][${index}][label]" class="form-control" placeholder="Button Label">
                </div>
                <div class="col-md-6">
                    <input type="text" name="translations[${lang}][buttons][${index}][url]" class="form-control" placeholder="Button URL">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger w-100 remove-button"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        container.append(html);
    });

    $(document).on('click', '.remove-button', function() {
        const container = $(this).closest('.button-row').parent();
        if (container.find('.button-row').length > 1) {
            $(this).closest('.button-row').remove();
        } else {
            $(this).closest('.button-row').find('input').val('');
        }
    });
});
</script>
@endpush
