@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('cms.brands.index') }}">Brands</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Brand</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Add Brand</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('cms.brands.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="alert alert-light border-start border-primary border-4 py-2 mb-4 shadow-sm" style="font-size: 0.9rem;">
                <i class="fas fa-info-circle text-primary me-2"></i> 
                <strong>Note:</strong> Please upload a high-quality logo. Required fields are marked with <span class="text-danger">*</span>.
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label fw-bold">Brand Logo <span class="text-danger">*</span></label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" required>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Recommended size: {{ $imageConfig['width'] }}x{{ $imageConfig['height'] }} px. Max: {{ $imageConfig['max_size'] }} KB.</small>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Image ALT Text <span class="text-danger">*</span></label>
                    <input type="text" name="image_alt" class="form-control @error('image_alt') is-invalid @enderror" value="{{ old('image_alt') }}" placeholder="Describe image for accessibility" required>
                    @error('image_alt')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Sort Order</label>
                    <input type="number" name="order_index" class="form-control @error('order_index') is-invalid @enderror" value="{{ old('order_index', 1) }}">
                    @error('order_index')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 d-flex align-items-end pb-2">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="status" id="brandStatus" checked>
                        <label class="form-check-label fw-bold" for="brandStatus">Active Status</label>
                    </div>
                </div>

                {{-- Dynamic Extra Fields --}}
                @php
                    $extraFields = config('cms-kit.database.brands.items.extra_fields', []);
                @endphp
                @if(!empty($extraFields))
                    <div class="col-12 mt-4">
                        <h6 class="text-muted border-bottom pb-2 mb-3">Additional Information</h6>
                        <div class="row g-3">
                            @foreach($extraFields as $name => $field)
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ $field['label'] }} @if($field['required'] ?? false) <span class="text-danger">*</span> @endif</label>
                                    @if(($field['type'] ?? 'text') === 'textarea')
                                        <textarea name="extra_fields[{{ $name }}]" class="form-control @error("extra_fields.{$name}") is-invalid @enderror" rows="3" {{ ($field['required'] ?? false) ? 'required' : '' }}>{{ old('extra_fields.' . $name) }}</textarea>
                                    @else
                                        <input type="{{ $field['type'] ?? 'text' }}" name="extra_fields[{{ $name }}]" class="form-control @error("extra_fields.{$name}") is-invalid @enderror" value="{{ old('extra_fields.' . $name) }}" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                    @endif
                                    @error("extra_fields.{$name}")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">Save</button>
                <a href="{{ route('cms.brands.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
