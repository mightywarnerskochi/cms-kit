@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('cms.blogs.index') }}">Blogs</a></li>
    <li class="breadcrumb-item active">Add Blog</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Add New Blog Post</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('cms.blogs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Slug (Optional)</label>
                    <input type="text" name="slug" class="form-control" placeholder="auto-generated if empty">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Published Date <span class="text-danger">*</span></label>
                    <input type="date" name="published_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

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
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="panel-{{ $lang->code }}" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold">Blog Title <span class="text-danger">*</span></label>
                            <input type="text" name="translations[{{ $lang->code }}][title]" class="form-control @error("translations.{$lang->code}.title") is-invalid @enderror" value="{{ old("translations.{$lang->code}.title") }}" required>
                            @error("translations.{$lang->code}.title")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Content <span class="text-danger">*</span></label>
                            <textarea name="translations[{{ $lang->code }}][content]" class="form-control tinymce-editor @error("translations.{$lang->code}.content") is-invalid @enderror" rows="10">{{ old("translations.{$lang->code}.content") }}</textarea>
                            @error("translations.{$lang->code}.content")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Images</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Feature Image (Listing) <span class="text-danger">*</span></label>
                            <input type="file" name="feature_image" class="form-control" required>
                            <small class="text-muted">Recommended: {{ $imagesConfig['feature_image']['width'] }}x{{ $imagesConfig['feature_image']['height'] }}px</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Feature Image ALT text</label>
                            <input type="text" name="feature_image_alt" class="form-control">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Detail Image (Hero) <span class="text-danger">*</span></label>
                            <input type="file" name="detail_image" class="form-control" required>
                            <small class="text-muted">Recommended: {{ $imagesConfig['detail_image']['width'] }}x{{ $imagesConfig['detail_image']['height'] }}px</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Detail Image ALT text</label>
                            <input type="text" name="detail_image_alt" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Banner Image (Optional)</label>
                            <input type="file" name="banner_image" class="form-control">
                            <small class="text-muted">Recommended: {{ $imagesConfig['banner_image']['width'] }}x{{ $imagesConfig['banner_image']['height'] }}px</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Banner ALT text</label>
                            <input type="text" name="banner_alt" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Image 3 (Optional)</label>
                            <input type="file" name="image_3" class="form-control">
                            <small class="text-muted">Recommended: {{ $imagesConfig['image_3']['width'] }}x{{ $imagesConfig['image_3']['height'] }}px</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image 3 ALT text</label>
                            <input type="text" name="image_3_alt" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Image 4 (Optional)</label>
                            <input type="file" name="image_4" class="form-control">
                            <small class="text-muted">Recommended: {{ $imagesConfig['image_4']['width'] }}x{{ $imagesConfig['image_4']['height'] }}px</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image 4 ALT text</label>
                            <input type="text" name="image_4_alt" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-search me-2"></i>SEO & Metadata</h6>
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Title</label>
                            <input type="text" name="metadata[meta_title]" class="form-control" placeholder="Page title for search engines">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Description</label>
                            <textarea name="metadata[meta_description]" class="form-control" rows="3" placeholder="Page description for search results"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Meta Keywords</label>
                            <input type="text" name="metadata[meta_keywords]" class="form-control" placeholder="keyword1, keyword2, ...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Canonical URL</label>
                            <input type="url" name="metadata[canonical_url]" class="form-control" placeholder="https://example.com/blog/post-slug">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">OG Title</label>
                            <input type="text" name="metadata[og_title]" class="form-control" placeholder="Title for social sharing">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">OG Description</label>
                            <textarea name="metadata[og_description]" class="form-control" rows="2" placeholder="Description for social sharing"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">OG Image</label>
                            <input type="file" name="metadata[og_image]" class="form-control">
                            <small class="text-muted">Recommended: 1200x630px. Max: 512KB</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Other Meta Tags</label>
                            <textarea name="metadata[other_meta_tags]" class="form-control" rows="3" placeholder='e.g. <meta name="robots" content="index, follow" />'></textarea>
                            <small class="text-muted">Raw HTML tags to be included in the header.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 border-top pt-4">
                <button type="submit" class="btn btn-primary px-5">Save Blog Post</button>
                <a href="{{ route('cms.blogs.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/site-manager/js/tinymce/tinymce.min.js') }}"></script>
<script>
    tinymce.init({
        selector: '.tinymce-editor',
        height: 400,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
    });
</script>
@endpush
