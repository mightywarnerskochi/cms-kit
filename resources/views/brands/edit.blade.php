@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('cms.brands.index') }}">Brands</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Brand</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Edit Brand</h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('cms.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="alert alert-light border-start border-primary border-4 py-2 mb-4 shadow-sm" style="font-size: 0.9rem;">
                <i class="fas fa-info-circle text-primary me-2"></i> 
                <strong>Note:</strong> Update the brand logo and details. Required fields are marked with <span class="text-danger">*</span>.
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label fw-bold">Current Logo</label>
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $brand->image) }}" class="img-thumbnail rounded" style="height: 100px;">
                    </div>
                    <label class="form-label fw-bold">Update Logo</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">Recommended size: {{ $imageConfig['width'] }}x{{ $imageConfig['height'] }} px. Max: {{ $imageConfig['max_size'] }} KB.</small>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Image ALT Text <span class="text-danger">*</span></label>
                    <input type="text" name="image_alt" class="form-control @error('image_alt') is-invalid @enderror" value="{{ old('image_alt', $brand->image_alt) }}" required>
                    @error('image_alt')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Sort Order</label>
                    <input type="number" name="order_index" class="form-control @error('order_index') is-invalid @enderror" value="{{ old('order_index', $brand->order_index) }}">
                    @error('order_index')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 d-flex align-items-end pb-2">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="status" id="brandStatus" {{ $brand->status ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="brandStatus">Active Status</label>
                    </div>
                </div>

                @include('cms-kit::partials.extra-fields-global', [
                    'configKey' => 'brands.items',
                    'existingValues' => $brand->extra_fields ?? [],
                ])

            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">Update</button>
                <a href="{{ route('cms.brands.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
