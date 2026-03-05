@extends('cms-kit::layouts.cms')

@section('title', 'Testimonials')

@section('content')
<div class="header">
    <h2><i class="fas fa-comment-dots text-primary"></i> Testimonials</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active">Testimonials</li>
        </ol>
    </nav>
</div>

<!-- Section Settings -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Section Settings</h5>
        <form action="{{ route('cms.testimonials.update-section') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Language Tabs for Section Settings -->
            <ul class="nav nav-tabs mb-3" id="sectionTabs" role="tablist">
                @foreach($languages as $lang)
                <li class="nav-item">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="section-tab-{{ $lang->code }}" data-bs-toggle="tab" data-bs-target="#section-{{ $lang->code }}" type="button">
                        {{ $lang->name }}
                    </button>
                </li>
                @endforeach
            </ul>

            <div class="tab-content" id="sectionTabsContent">
                @foreach($languages as $lang)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="section-{{ $lang->code }}">
                    <div class="row g-3">
                        @if(config('cms-kit.testimonials.columns.title'))
                        <div class="col-md-4">
                            <label class="form-label">Title ({{ $lang->code }})</label>
                            <input type="text" name="translations[{{ $lang->code }}][section_title]" class="form-control" value="{{ $section->translations[$lang->code]['section_title'] ?? '' }}">
                        </div>
                        @endif
                        
                        @if(config('cms-kit.testimonials.columns.sub_heading_1'))
                        <div class="col-md-4">
                            <label class="form-label">Sub Heading 1</label>
                            <input type="text" name="translations[{{ $lang->code }}][section_sub_heading_1]" class="form-control" value="{{ $section->translations[$lang->code]['section_sub_heading_1'] ?? '' }}">
                        </div>
                        @endif

                        @if(config('cms-kit.testimonials.columns.sub_heading_2'))
                        <div class="col-md-4">
                            <label class="form-label">Sub Heading 2</label>
                            <input type="text" name="translations[{{ $lang->code }}][section_sub_heading_2]" class="form-control" value="{{ $section->translations[$lang->code]['section_sub_heading_2'] ?? '' }}">
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-3">
                @if(config('cms-kit.testimonials.columns.section_image'))
                <div class="col-md-12 mb-3">
                    <label class="form-label">Section Image</label>
                    <input type="file" name="section_image" class="form-control">
                    @if($section->section_image)
                        <img src="{{ asset('storage/'.$section->section_image) }}" class="mt-2" style="height: 50px;">
                    @endif
                </div>
                @endif
                <button type="submit" class="btn btn-primary">Update Section</button>
            </div>
        </form>
    </div>
</div>

<!-- Testimonials List -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Testimonial Items</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
            <i class="fas fa-plus"></i> Add Testimonial
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table premium-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Image</th>
                        <th>Name (Default)</th>
                        <th>Content Preview</th>
                        @if(config('cms-kit.testimonials.columns.rating')) <th>Rating</th> @endif
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimonials as $item)
                    <tr>
                        <td class="ps-4">
                            @if($item->image)
                                <img src="{{ asset('storage/'.$item->image) }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle text-center" style="width: 40px; height: 40px; line-height: 40px;">
                                    <i class="fas fa-user text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->getTranslation('name') }}</strong><br>
                            <small class="text-muted">{{ $item->getTranslation('designation') }}</small>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px;">{!! strip_tags($item->getTranslation('content')) !!}</div>
                        </td>
                        @if(config('cms-kit.testimonials.columns.rating'))
                        <td>{{ $item->rating }}/5</td>
                        @endif
                        <td>
                            <span class="badge {{ $item->status ? 'bg-success' : 'bg-secondary' }}">
                                {{ $item->status ? 'ON' : 'OFF' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <form action="{{ route('cms.testimonials.destroy', $item->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Delete this?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No testimonials found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addTestimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('cms.testimonials.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Language Tabs for New Testimonial -->
                    <ul class="nav nav-tabs mb-3" id="addTabs" role="tablist">
                        @foreach($languages as $lang)
                        <li class="nav-item">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="add-tab-{{ $lang->code }}" data-bs-toggle="tab" data-bs-target="#add-{{ $lang->code }}" type="button">
                                {{ $lang->name }}
                            </button>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content" id="addTabsContent">
                        @foreach($languages as $lang)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="add-{{ $lang->code }}">
                            <div class="mb-3">
                                <label class="form-label">Name ({{ $lang->name }})</label>
                                <input type="text" name="translations[{{ $lang->code }}][name]" class="form-control" {{ $loop->first ? 'required' : '' }}>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Designation ({{ $lang->name }})</label>
                                <input type="text" name="translations[{{ $lang->code }}][designation]" class="form-control">
                            </div>
                            @if(config('cms-kit.testimonials.columns.content'))
                            <div class="mb-3">
                                <label class="form-label">Content ({{ $lang->name }})</label>
                                <textarea name="translations[{{ $lang->code }}][content]" class="form-control tinymce-editor" rows="4"></textarea>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <hr>

                    <!-- Shared / Non-translated fields -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        @if(config('cms-kit.testimonials.columns.rating'))
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select">
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                            </select>
                        </div>
                        @endif
                    </div>

                    <!-- Extra Dynamic Fields -->
                    @foreach(config('cms-kit.testimonials.extra_fields', []) as $key => $field)
                    <div class="mb-3">
                        <label class="form-label">{{ $field['label'] ?? $key }}</label>
                        @if(($field['type'] ?? 'text') == 'textarea')
                            <textarea name="extra_fields[{{ $key }}]" class="form-control" rows="2"></textarea>
                        @else
                            <input type="text" name="extra_fields[{{ $key }}]" class="form-control">
                        @endif
                    </div>
                    @endforeach

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" checked id="statusSwitch">
                        <label class="form-check-label" for="statusSwitch">Status (ON/OFF)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Testimonial</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
