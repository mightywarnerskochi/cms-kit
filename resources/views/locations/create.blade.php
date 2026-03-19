@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('cms.locations.index') }}">Locations</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Location</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Add Location</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('cms.locations.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="alert alert-light border-start border-primary border-4 py-2 mb-4 shadow-sm" style="font-size: 0.9rem;">
                <i class="fas fa-info-circle text-primary me-2"></i> 
                <strong>Note:</strong> Please ensure all required fields <span class="text-danger">(*)</span> are filled across all language tabs.
            </div>

            <!-- Improved Language Switcher -->
            <ul class="nav nav-pills mb-4 bg-light p-2 rounded-3" id="langTabs" role="tablist">
                @foreach($languages as $lang)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }} px-4 py-2 fw-medium" id="{{ $lang->code }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $lang->code }}-content" type="button" role="tab">
                        <i class="fas fa-language me-2 opacity-75"></i>{{ $lang->name }}
                    </button>
                </li>
                @endforeach
            </ul>

            <div class="tab-content mb-4">
                @foreach($languages as $lang)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $lang->code }}-content" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Country <span class="text-danger">*</span></label>
                            <input type="text" name="translations[{{ $lang->code }}][country]" class="form-control @error("translations.{$lang->code}.country") is-invalid @enderror" value="{{ old("translations.{$lang->code}.country") }}" placeholder="e.g. United Arab Emirates" required>
                            @error("translations.{$lang->code}.country")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Title/City <span class="text-danger">*</span></label>
                            <input type="text" name="translations[{{ $lang->code }}][title]" class="form-control @error("translations.{$lang->code}.title") is-invalid @enderror" value="{{ old("translations.{$lang->code}.title") }}" placeholder="e.g. Deira, Dubai, UAE." required>
                            @error("translations.{$lang->code}.title")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                            <textarea name="translations[{{ $lang->code }}][address]" class="form-control @error("translations.{$lang->code}.address") is-invalid @enderror" rows="3" required>{{ old("translations.{$lang->code}.address") }}</textarea>
                            @error("translations.{$lang->code}.address")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @include('cms-kit::partials.extra-fields-translatable', [
                            'configKey' => 'locations.items',
                            'lang' => $lang,
                            'existingTranslations' => [],
                        ])
                    </div>
                </div>
                @endforeach
            </div>

            <hr>

            <div class="row g-4">
                <!-- Images -->
                <div class="col-md-6">
                    <label class="form-label d-block">Location Image</label>
                    <small class="text-muted d-block mb-1">Recommended size: {{ $imageConfig['width'] }}x{{ $imageConfig['height'] }}px, Max: {{ $imageConfig['max_size'] }}KB</small>
                    <input type="file" name="image" class="form-control">
                    <input type="text" name="image_alt" class="form-control mt-2" placeholder="Image ALT text">
                </div>
                <div class="col-md-6">
                    <label class="form-label d-block">Flag Image</label>
                    <small class="text-muted d-block mb-1">Recommended size: {{ $flagConfig['width'] }}x{{ $flagConfig['height'] }}px, Max: {{ $flagConfig['max_size'] }}KB</small>
                    <input type="file" name="flag" class="form-control">
                    <input type="text" name="flag_alt" class="form-control mt-2" placeholder="Flag ALT text">
                </div>

                <!-- Contact info -->
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+971 4 123 4567">
                </div>
                <div class="col-md-4">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp') }}" placeholder="+971 50 123 4567">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fax</label>
                    <input type="text" name="fax" class="form-control" value="{{ old('fax') }}">
                </div>

                <div class="col-12">
                    <label class="form-label">Emails (multiple)</label>
                    <textarea name="emails" class="form-control" rows="3" placeholder="One email per line">{{ old('emails') }}</textarea>
                    <small class="text-muted">Enter one email per line. Comma or semicolon also accepted.</small>
                </div>



                <!-- Map -->
                <div class="col-12">
                    <label class="form-label">Google Map Link / Embed URL</label>
                    <input type="text" name="map_link" class="form-control" value="{{ old('map_link') }}" placeholder="https://maps.google.com/...">
                </div>

                <!-- Settings -->
                <div class="col-md-4">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="order_index" class="form-control" value="{{ old('order_index', 1) }}" min="1">
                </div>
                <div class="col-md-4 d-flex align-items-end pb-2">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="status" id="locationStatus" checked>
                        <label class="form-check-label" for="locationStatus">Active</label>
                    </div>
                </div>

                @include('cms-kit::partials.extra-fields-global', [
                    'configKey' => 'locations.items',
                    'existingValues' => [],
                ])
            </div>


            <div class="mt-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">Save</button>
                <a href="{{ route('cms.locations.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
