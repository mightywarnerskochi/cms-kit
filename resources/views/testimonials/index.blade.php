@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Testimonials</li>
@endsection

@section('content')
@php
    $itemRequired = config('cms-kit.database.testimonials.items.required', []);
    $sectionRequired = config('cms-kit.database.testimonials.section.required', []);
@endphp

<!-- Section Settings -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Section Settings</h5>
        <form action="{{ route('cms.testimonials.update-section') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Language Tabs for Section Settings -->
            <div class="alert alert-info py-2 mb-3">
                <i class="fas fa-info-circle me-1"></i> Please ensure all required fields are filled across all language tabs before saving.
            </div>
            <!-- Improved Language Switcher -->
            <ul class="nav nav-pills mb-4 bg-light p-2 rounded-3" id="sectionTabs" role="tablist">
                @foreach($languages as $lang)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }} px-4 py-2 fw-medium" id="section-tab-{{ $lang->code }}" data-bs-toggle="tab" data-bs-target="#section-{{ $lang->code }}" type="button" role="tab">
                        <i class="fas fa-language me-2 opacity-75"></i>{{ $lang->name }}
                    </button>
                </li>
                @endforeach
            </ul>

            <div class="tab-content" id="sectionTabsContent">
                @foreach($languages as $lang)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="section-{{ $lang->code }}" role="tabpanel">
                    <div class="row g-3">
                        @if(config('cms-kit.database.testimonials.section.title'))
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Title ({{ strtoupper($lang->code) }}) {!! in_array('title', $sectionRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                            <input type="text" name="translations[{{ $lang->code }}][section_title]" class="form-control @error("translations.{$lang->code}.section_title") is-invalid @enderror" value="{{ old("translations.{$lang->code}.section_title", $section->translations[$lang->code]['section_title'] ?? '') }}" {{ in_array('title', $sectionRequired) ? 'required' : '' }}>
                            @error("translations.{$lang->code}.section_title")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif
                        
                        @if(config('cms-kit.database.testimonials.section.sub_heading_1'))
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Sub Heading 1 {!! in_array('sub_heading_1', $sectionRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                            <input type="text" name="translations[{{ $lang->code }}][section_sub_heading_1]" class="form-control @error("translations.{$lang->code}.section_sub_heading_1") is-invalid @enderror" value="{{ old("translations.{$lang->code}.section_sub_heading_1", $section->translations[$lang->code]['section_sub_heading_1'] ?? '') }}" {{ in_array('sub_heading_1', $sectionRequired) ? 'required' : '' }}>
                            @error("translations.{$lang->code}.section_sub_heading_1")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        @if(config('cms-kit.database.testimonials.section.sub_heading_2'))
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Sub Heading 2 {!! in_array('sub_heading_2', $sectionRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                            <input type="text" name="translations[{{ $lang->code }}][section_sub_heading_2]" class="form-control @error("translations.{$lang->code}.section_sub_heading_2") is-invalid @enderror" value="{{ old("translations.{$lang->code}.section_sub_heading_2", $section->translations[$lang->code]['section_sub_heading_2'] ?? '') }}" {{ in_array('sub_heading_2', $sectionRequired) ? 'required' : '' }}>
                            @error("translations.{$lang->code}.section_sub_heading_2")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        @if(config('cms-kit.database.testimonials.section.description'))
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold">Description ({{ strtoupper($lang->code) }}) {!! in_array('description', $sectionRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                            <textarea name="description[{{ $lang->code }}]" class="form-control tinymce-editor @error("description.{$lang->code}") is-invalid @enderror" rows="3">{{ old("description.{$lang->code}", $section->description[$lang->code] ?? '') }}</textarea>
                            @error("description.{$lang->code}")
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row mt-3">
                <div class="col-md-2">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status" id="sectionStatus" {{ ($section->status ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="sectionStatus">Status</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="display_home" id="sectionDisplayHome" {{ ($section->display_home ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="sectionDisplayHome">Display Home</label>
                    </div>
                </div>
            </div>

            <div class="mt-3">

                <div class="row">
                    @if(config('cms-kit.database.testimonials.section.section_image'))
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Section Image {!! in_array('section_image', $sectionRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                        <div class="alert alert-light border py-1 px-2 mb-2" style="font-size: 0.8rem;">
                            <i class="fas fa-info-circle text-primary me-1"></i> This image is used across all languages.
                        </div>
                        <input type="file" name="section_image" class="form-control mb-2" {{ in_array('section_image', $sectionRequired) && !$section->section_image ? 'required' : '' }}>
                        @if(config('cms-kit.database.testimonials.section.section_image_alt'))
                        <input type="text" name="section_image_alt" class="form-control" placeholder="Section Image Alt Text" value="{{ $section->section_image_alt }}">
                        @endif
                        <small class="text-muted d-block mt-1">Recommended: {{ config('cms-kit.images.testimonials.section_image.width') }}x{{ config('cms-kit.images.testimonials.section_image.height') }}px</small>
                        @if($section->section_image)
                            <img src="{{ asset('storage/'.$section->section_image) }}" class="mt-2" style="height: 50px;">
                        @endif
                    </div>
                    @endif

                    @if(config('cms-kit.database.testimonials.section.banner'))
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Banner {!! in_array('banner', $sectionRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                        <div class="alert alert-light border py-1 px-2 mb-2" style="font-size: 0.8rem;">
                            <i class="fas fa-info-circle text-primary me-1"></i> This image is used across all languages.
                        </div>
                        <input type="file" name="banner" class="form-control mb-2" {{ in_array('banner', $sectionRequired) && !$section->banner ? 'required' : '' }}>
                        @if(config('cms-kit.database.testimonials.section.banner_alt'))
                        <input type="text" name="banner_alt" class="form-control" placeholder="Banner Alt Text" value="{{ $section->banner_alt }}">
                        @endif
                        <small class="text-muted d-block mt-1">Recommended: {{ config('cms-kit.images.testimonials.banner.width') }}x{{ config('cms-kit.images.testimonials.banner.height') }}px</small>
                        @if($section->banner)
                            <img src="{{ asset('storage/'.$section->banner) }}" class="mt-2" style="height: 50px;">
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Section Extra Fields -->
                @php $sectionExtra = config('cms-kit.database.testimonials.section.extra_fields', []); @endphp
                @if(count($sectionExtra) > 0)
                <div class="row g-3 mb-3">
                    @foreach($sectionExtra as $key => $field)
                    <div class="col-md-4">
                        <label class="form-label">{{ $field['label'] ?? $key }}</label>
                        @if(($field['type'] ?? 'text') == 'textarea')
                            <textarea name="extra_fields[{{ $key }}]" class="form-control" rows="2">{{ $section->extra_fields[$key] ?? '' }}</textarea>
                        @else
                            <input type="text" name="extra_fields[{{ $key }}]" class="form-control" value="{{ $section->extra_fields[$key] ?? '' }}">
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <button type="submit" class="btn btn-primary">Update Section</button>
            </div>
        </form>
    </div>
</div>

<!-- Testimonials List -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">Testimonial Items</h5>
        <div class="d-flex gap-2">
            <div class="dropdown" id="bulkActions" style="display: none;">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Bulk Actions (<span id="selectedCount">0</span>)
                </button>
                <ul class="dropdown-menu">
                    <li><button class="dropdown-item" onclick="bulkAction('active')"><i class="fas fa-check-circle text-success me-2"></i> Mark Active</button></li>
                    <li><button class="dropdown-item" onclick="bulkAction('inactive')"><i class="fas fa-times-circle text-secondary me-2"></i> Mark Inactive</button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button class="dropdown-item" onclick="bulkAction('delete')"><i class="fas fa-trash text-danger me-2"></i> Delete Selected</button></li>
                </ul>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
                <i class="fas fa-plus"></i> Add Testimonial
            </button>
        </div>
    </div>
    <form id="bulkForm" action="{{ route('cms.testimonials.bulk-action') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulkActionInput">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table premium-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name (Default)</th>
                        <th>Content Preview</th>
                        @if(config('cms-kit.database.testimonials.items.rating')) <th>Rating</th> @endif
                        <th>Order</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </form>
</div>

<!-- Edit Modals Container -->
<div id="editModalsContainer">
    @foreach($testimonials as $item)
    <!-- Edit Testimonial Modal -->
    <div class="modal fade" id="editTestimonialModal{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('cms.testimonials.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-primary">Edit Testimonial</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Language Tabs for Edit Testimonial -->
                        <div class="alert alert-light border-start border-primary border-4 py-2 mb-4 shadow-sm" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle text-primary me-2"></i> <strong>Note:</strong> Ensure all required fields <span class="text-danger">(*)</span> are filled across all language tabs.
                        </div>

                        <!-- Improved Language Switcher -->
                        <ul class="nav nav-pills mb-4 bg-light p-2 rounded-3" id="editTabs{{ $item->id }}" role="tablist">
                            @foreach($languages as $lang)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }} px-3 py-2 fw-medium" id="edit-tab-{{ $item->id }}-{{ $lang->code }}" data-bs-toggle="tab" data-bs-target="#edit-{{ $item->id }}-{{ $lang->code }}" type="button" role="tab">
                                    <i class="fas fa-language me-2 opacity-75"></i>{{ $lang->name }}
                                </button>
                            </li>
                            @endforeach
                        </ul>

                        <div class="tab-content mb-4" id="editTabsContent{{ $item->id }}">
                            @foreach($languages as $lang)
                            @php $trans = $item->translations[$lang->code] ?? []; @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="edit-{{ $item->id }}-{{ $lang->code }}" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Name ({{ strtoupper($lang->code) }}) {!! in_array('name', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                                    <input type="text" name="translations[{{ $lang->code }}][name]" class="form-control @error("translations.{$lang->code}.name") is-invalid @enderror" value="{{ old("translations.{$lang->code}.name", $trans['name'] ?? '') }}" {{ in_array('name', $itemRequired) ? 'required' : '' }}>
                                    @error("translations.{$lang->code}.name")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Designation ({{ strtoupper($lang->code) }}) {!! in_array('designation', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                                    <input type="text" name="translations[{{ $lang->code }}][designation]" class="form-control @error("translations.{$lang->code}.designation") is-invalid @enderror" value="{{ old("translations.{$lang->code}.designation", $trans['designation'] ?? '') }}" {{ in_array('designation', $itemRequired) ? 'required' : '' }}>
                                    @error("translations.{$lang->code}.designation")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if(config('cms-kit.database.testimonials.items.content'))
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Content ({{ strtoupper($lang->code) }}) {!! in_array('content', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                                    <textarea name="translations[{{ $lang->code }}][content]" class="form-control tinymce-editor @error("translations.{$lang->code}.content") is-invalid @enderror" rows="4">{{ old("translations.{$lang->code}.content", $trans['content'] ?? '') }}</textarea>
                                    @error("translations.{$lang->code}.content")
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <hr class="my-4">

                        <!-- Shared / Non-translated fields -->
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Image {!! in_array('image', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                                <div class="alert alert-light border py-1 px-2 mb-2 shadow-sm" style="font-size: 0.75rem;">
                                    <i class="fas fa-info-circle text-primary me-1"></i> This image is used across all languages.
                                </div>
                                <input type="file" name="image" class="form-control mb-2 @error('image') is-invalid @enderror">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(config('cms-kit.database.testimonials.items.image_alt'))
                                <input type="text" name="image_alt" class="form-control mt-2" placeholder="Image Alt Text" value="{{ old('image_alt', $item->image_alt) }}">
                                @endif
                                <small class="text-muted d-block mt-2">Recommended: {{ config('cms-kit.images.testimonials.item_image.width') }}x{{ config('cms-kit.images.testimonials.item_image.height') }}px</small>
                                @if($item->image)
                                    <div class="mt-2 position-relative d-inline-block">
                                        <img src="{{ asset('storage/'.$item->image) }}" class="rounded shadow-sm" style="height: 60px; width: 60px; object-fit: cover;">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-light">Current</span>
                                    </div>
                                @endif
                            </div>
                            @if(config('cms-kit.database.testimonials.items.rating'))
                            <div class="col-md-6 text-center">
                                <label class="form-label fw-bold d-block text-start">Rating {!! in_array('rating', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                                <div class="rating-input d-inline-flex bg-light p-3 rounded-pill shadow-sm">
                                    <select name="rating" class="form-select border-0 bg-transparent fw-bold text-warning h4 mb-0" style="width: auto;" {{ in_array('rating', $itemRequired) ? 'required' : '' }}>
                                        <option value="5" {{ old('rating', $item->rating) == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 Stars)</option>
                                        <option value="4" {{ old('rating', $item->rating) == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ (4 Stars)</option>
                                        <option value="3" {{ old('rating', $item->rating) == 3 ? 'selected' : '' }}>⭐⭐⭐ (3 Stars)</option>
                                        <option value="2" {{ old('rating', $item->rating) == 2 ? 'selected' : '' }}>⭐⭐ (2 Stars)</option>
                                        <option value="1" {{ old('rating', $item->rating) == 1 ? 'selected' : '' }}>⭐ (1 Star)</option>
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="row align-items-center mt-4 pt-3 border-top">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Order Index</label>
                                <input type="number" name="order_index" value="{{ old('order_index', $item->order_index) }}" class="form-control shadow-sm">
                            </div>
                            <div class="col-md-8">
                                <div class="form-check form-switch pt-4">
                                    <input class="form-check-input h5 mb-0" type="checkbox" name="status" {{ old('status', $item->status) ? 'checked' : '' }} id="statusSwitch{{ $item->id }}">
                                    <label class="form-check-label fw-bold ms-2 mt-1" for="statusSwitch{{ $item->id }}">Status (ON/OFF)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-check me-2"></i>Update Testimonial
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>

<!-- Add Modal -->
<div class="modal fade" id="addTestimonialModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<form action="{{ route('cms.testimonials.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="modal-content border-0 shadow">
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold text-white"><i class="fas fa-plus-circle me-2"></i>Add New Testimonial</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
<div class="modal-body">
    <!-- Language Tabs for New Testimonial -->
    <div class="alert alert-light border-start border-primary border-4 py-2 mb-4 shadow-sm" style="font-size: 0.85rem;">
        <i class="fas fa-info-circle text-primary me-2"></i> <strong>Note:</strong> Please ensure all required fields <span class="text-danger">(*)</span> are filled across all language tabs.
    </div>

    <!-- Improved Language Switcher -->
    <ul class="nav nav-pills mb-4 bg-light p-2 rounded-3" id="addTabs" role="tablist">
        @foreach($languages as $lang)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $loop->first ? 'active' : '' }} px-3 py-2 fw-medium" id="add-tab-{{ $lang->code }}" data-bs-toggle="tab" data-bs-target="#add-{{ $lang->code }}" type="button" role="tab">
                <i class="fas fa-language me-2 opacity-75"></i>{{ $lang->name }}
            </button>
        </li>
        @endforeach
    </ul>

    <div class="tab-content mb-4" id="addTabsContent">
        @foreach($languages as $lang)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="add-{{ $lang->code }}" role="tabpanel">
            <div class="mb-3">
                <label class="form-label fw-bold">Name ({{ strtoupper($lang->code) }}) {!! in_array('name', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                <input type="text" name="translations[{{ $lang->code }}][name]" class="form-control @error("translations.{$lang->code}.name") is-invalid @enderror" value="{{ old("translations.{$lang->code}.name") }}" {{ in_array('name', $itemRequired) ? 'required' : '' }}>
                @error("translations.{$lang->code}.name")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Designation ({{ strtoupper($lang->code) }}) {!! in_array('designation', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                <input type="text" name="translations[{{ $lang->code }}][designation]" class="form-control @error("translations.{$lang->code}.designation") is-invalid @enderror" value="{{ old("translations.{$lang->code}.designation") }}" {{ in_array('designation', $itemRequired) ? 'required' : '' }}>
                @error("translations.{$lang->code}.designation")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            @if(config('cms-kit.database.testimonials.items.content'))
            <div class="mb-3">
                <label class="form-label fw-bold">Content ({{ strtoupper($lang->code) }}) {!! in_array('content', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
                <textarea name="translations[{{ $lang->code }}][content]" class="form-control tinymce-editor @error("translations.{$lang->code}.content") is-invalid @enderror" rows="4">{{ old("translations.{$lang->code}.content") }}</textarea>
                @error("translations.{$lang->code}.content")
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <hr class="my-4">

    <!-- Shared / Non-translated fields -->
    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label fw-bold">Image {!! in_array('image', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
            <div class="alert alert-light border py-1 px-2 mb-2 shadow-sm" style="font-size: 0.75rem;">
                <i class="fas fa-info-circle text-primary me-1"></i> This image is used across all languages.
            </div>
            <input type="file" name="image" class="form-control mb-2 @error('image') is-invalid @enderror" {{ in_array('image', $itemRequired) ? 'required' : '' }}>
            @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if(config('cms-kit.database.testimonials.items.image_alt'))
            <input type="text" name="image_alt" class="form-control mt-2 shadow-sm" placeholder="Image Alt Text" value="{{ old('image_alt') }}">
            @endif
            <small class="text-muted d-block mt-2">Recommended: {{ config('cms-kit.images.testimonials.item_image.width') }}x{{ config('cms-kit.images.testimonials.item_image.height') }}px</small>
        </div>
        @if(config('cms-kit.database.testimonials.items.rating'))
        <div class="col-md-6 text-center">
            <label class="form-label fw-bold d-block text-start">Rating {!! in_array('rating', $itemRequired) ? '<span class="text-danger">*</span>' : '' !!}</label>
            <div class="rating-input d-inline-flex bg-light p-3 rounded-pill shadow-sm">
                <select name="rating" class="form-select border-0 bg-transparent fw-bold text-warning h4 mb-0" style="width: auto;" {{ in_array('rating', $itemRequired) ? 'required' : '' }}>
                    <option value="5" {{ old('rating') == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 Stars)</option>
                    <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ (4 Stars)</option>
                    <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>⭐⭐⭐ (3 Stars)</option>
                    <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>⭐⭐ (2 Stars)</option>
                    <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>⭐ (1 Star)</option>
                </select>
            </div>
        </div>
        @endif
    </div>

    <!-- Extra Dynamic Fields -->
    @php $itemExtra = config('cms-kit.database.testimonials.items.extra_fields', []); @endphp
    @if(count($itemExtra) > 0)
    <div class="row g-3 mt-3">
        @foreach($itemExtra as $key => $field)
        <div class="col-md-6">
            <label class="form-label fw-bold">{{ $field['label'] ?? $key }}</label>
            @if(($field['type'] ?? 'text') == 'textarea')
                <textarea name="extra_fields[{{ $key }}]" class="form-control shadow-sm" rows="2">{{ old("extra_fields.{$key}") }}</textarea>
            @else
                <input type="text" name="extra_fields[{{ $key }}]" class="form-control shadow-sm" value="{{ old("extra_fields.{$key}") }}">
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <div class="row align-items-center mt-4 pt-3 border-top">
        <div class="col-md-4">
            <label class="form-label fw-bold">Order Index</label>
            <input type="number" name="order_index" value="{{ old('order_index', $nextOrder) }}" class="form-control shadow-sm">
        </div>
        <div class="col-md-8">
            <div class="form-check form-switch pt-4">
                <input class="form-check-input h5 mb-0" type="checkbox" name="status" {{ old('status', true) ? 'checked' : '' }} id="statusSwitch">
                <label class="form-check-label fw-bold ms-2 mt-1" for="statusSwitch">Status (ON/OFF)</label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer bg-light border-0">
    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
    <button type="submit" class="btn btn-primary px-4 shadow-sm">
        <i class="fas fa-save me-2"></i>Save Testimonial
    </button>
</div>
</div>
</form>
</div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function() {
        const table = $('.premium-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('cms.testimonials.index') }}",
            columns: [
                {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false},
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'image', name: 'image', orderable: false, searchable: false},
                {data: 'name_info', name: 'name_info'},
                {data: 'content_preview', name: 'content_preview'},
                @if(config('cms-kit.database.testimonials.items.rating'))
                {data: 'rating', name: 'rating', orderable: true, searchable: false},
                @endif
                {data: 'order', name: 'order'},
                {data: 'status_toggle', name: 'status_toggle', className: 'text-center'},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
            ],
            order: [[1, 'asc']],
            drawCallback: function() {
                updateBulkVisibility();
            }
        });

        // Status Toggle
        $(document).on('change', '.status-toggle', function() {
            const id = $(this).data('id');
            $.post(`{{ url(config('cms-kit.common.auth.prefix', 'admin')) }}/testimonials/${id}/toggle-status`, {
                _token: '{{ csrf_token() }}'
            })
            .done(function(data) {
                if (!data.success) {
                    table.ajax.reload(null, false);
                    alert('Error updating status');
                }
            })
            .fail(function() {
                table.ajax.reload(null, false);
                alert('Request failed');
            });
        });

        // Reorder
        $(document).on('change', '.reorder-input', function() {
            const id = $(this).data('id');
            const order = $(this).val();
            $.post("{{ route('cms.testimonials.reorder') }}", {
                _token: '{{ csrf_token() }}',
                id: id,
                order_index: order
            })
            .done(function() {
                table.ajax.reload(null, false);
            })
            .fail(function() {
                table.ajax.reload(null, false);
                alert('Failed to update order');
            });
        });

        // Bulk Select
        $('#selectAll').on('change', function() {
            $('.row-checkbox').prop('checked', this.checked);
            updateBulkVisibility();
        });

        $(document).on('change', '.row-checkbox', function() {
            updateBulkVisibility();
        });

        function updateBulkVisibility() {
            const checkedCount = $('.row-checkbox:checked').length;
            $('#selectedCount').text(checkedCount);
            $('#bulkActions').toggle(checkedCount > 0);
            $('#selectAll').prop('checked', checkedCount > 0 && checkedCount === $('.row-checkbox').length);
        }

        window.bulkAction = function(action) {
            if (action === 'delete' && !confirm('Are you sure you want to delete selected items?')) {
                return;
            }
            $('#bulkActionInput').val(action);
            $('#bulkForm').submit();
        };

        // Handle HTML5 validation across language tabs
        document.addEventListener('invalid', function(e) {
            let invalidTabPane = e.target.closest('.tab-pane');
            if (invalidTabPane) {
                let tabId = invalidTabPane.id;
                let tabBtn = document.querySelector(`[data-bs-target="#${tabId}"]`);
                if (tabBtn && !tabBtn.classList.contains('active')) {
                    $(tabBtn).tab('show');
                    setTimeout(() => { e.target.focus(); }, 150);
                }
            }
        }, true);
    });
</script>
@endpush
