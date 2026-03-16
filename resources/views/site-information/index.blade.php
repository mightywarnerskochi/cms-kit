@extends('cms-kit::layouts.cms')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page">Site Information</li>
@endsection

@section('content')
<div class="container-fluid">

    <div class="alert alert-light border-start border-primary border-4 py-3 mb-4 shadow-sm">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle text-primary fs-4 me-3"></i>
            <div>
                <h6 class="mb-1 fw-bold text-primary">Site Configuration Note</h6>
                <p class="mb-0 text-muted small">Update your company details, contact information, and global site settings here. All required fields are marked with <span class="text-danger">*</span>.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('cms.site-information.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <!-- Left Column: Company & Contact -->
            <div class="col-md-8">
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-primary">Company & Contact Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $siteInfo->company_name) }}">
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Country</label>
                                <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country', $siteInfo->country) }}">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">P.O Box</label>
                                <input type="text" name="po_box" class="form-control @error('po_box') is-invalid @enderror" value="{{ old('po_box', $siteInfo->po_box) }}">
                                @error('po_box')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fax</label>
                                <input type="text" name="fax" class="form-control @error('fax') is-invalid @enderror" value="{{ old('fax', $siteInfo->fax) }}">
                                @error('fax')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $siteInfo->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="fw-bold mb-3 text-secondary">Phone Numbers</h6>
                                @for($i = 1; $i <= 4; $i++)
                                @php $field = "phone_$i"; @endphp
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Phone {{ $i }} @if($i == 1) <span class="text-danger">*</span> @endif</label>
                                    <input type="text" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $siteInfo->$field) }}">
                                    @error($field)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endfor
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="fw-bold mb-3 text-secondary">Email Addresses</h6>
                                @for($i = 1; $i <= 4; $i++)
                                @php $field = "email_$i"; @endphp
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Email {{ $i }} @if($i == 1) <span class="text-danger">*</span> @endif</label>
                                    <input type="email" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $siteInfo->$field) }}">
                                    @error($field)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @endfor
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Main WhatsApp Number</label>
                                <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', $siteInfo->whatsapp_number) }}" placeholder="e.g. +1234567890">
                                @error('whatsapp_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Receipt Email</label>
                                <input type="email" name="receipt_email" class="form-control @error('receipt_email') is-invalid @enderror" value="{{ old('receipt_email', $siteInfo->receipt_email) }}" placeholder="e.g. billing@company.com">
                                @error('receipt_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Legal Content</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Privacy Policy</label>
                            <textarea name="privacy_policy" id="privacy_policy" class="form-control">{{ $siteInfo->privacy_policy }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="form-control">{{ $siteInfo->terms_and_conditions }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Disclaimer</label>
                            <textarea name="disclaimer" id="disclaimer" class="form-control">{{ $siteInfo->disclaimer }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Visual Assets, Social, SEO -->
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold">Visual Assets</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Main Logo</label>
                            <small class="text-muted d-block mb-1">Recommended size: 200x60px (PNG/SVG)</small>
                            @if($siteInfo->logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $siteInfo->logo) }}" class="img-thumbnail rounded" style="max-height: 80px;">
                            </div>
                            @endif
                            <input type="file" name="logo" class="form-control mb-2 @error('logo') is-invalid @enderror">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <input type="text" name="logo_alt" class="form-control @error('logo_alt') is-invalid @enderror" placeholder="Logo Alt Text" value="{{ old('logo_alt', $siteInfo->logo_alt) }}">
                            @error('logo_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Favicon</label>
                            <small class="text-muted d-block mb-1">Recommended size: 32x32px (ICO/PNG)</small>
                            @if($siteInfo->favicon)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $siteInfo->favicon) }}" class="img-thumbnail rounded" style="max-height: 32px;">
                            </div>
                            @endif
                            <input type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror">
                            @error('favicon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Footer Logo</label>
                            <small class="text-muted d-block mb-1">Recommended size: 150x50px (PNG/SVG)</small>
                            @if($siteInfo->footer_logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $siteInfo->footer_logo) }}" class="img-thumbnail rounded" style="max-height: 80px;">
                            </div>
                            @endif
                            <input type="file" name="footer_logo" class="form-control mb-2 @error('footer_logo') is-invalid @enderror">
                            @error('footer_logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <input type="text" name="footer_logo_alt" class="form-control mb-2 @error('footer_logo_alt') is-invalid @enderror" placeholder="Footer Logo Alt Text" value="{{ old('footer_logo_alt', $siteInfo->footer_logo_alt) }}">
                            @error('footer_logo_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <textarea name="footer_description" class="form-control @error('footer_description') is-invalid @enderror" rows="3" placeholder="Footer Description">{{ old('footer_description', $siteInfo->footer_description) }}</textarea>
                            @error('footer_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold">Social Media</h5>
                    </div>
                    <div class="card-body">
                        @foreach(['facebook', 'twitter', 'linkedin', 'instagram', 'tiktok', 'snapchat', 'pinterest', 'youtube', 'skype', 'whatsapp_social' => 'Whatsapp', 'vimeo'] as $key => $label)
                            @php 
                                $inputKey = is_numeric($key) ? $label : $key;
                                $displayLabel = is_numeric($key) ? ucfirst($label) : $label;
                            @endphp
                            <div class="mb-2">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-{{ $inputKey == 'whatsapp_social' ? 'whatsapp' : str_replace('_', '-', $inputKey) }}"></i></span>
                                    <input type="text" name="{{ $inputKey }}" class="form-control" placeholder="{{ $displayLabel }} URL" value="{{ $siteInfo->$inputKey }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold">Extra SEO & Tracking</h5>
                        <small class="text-muted">Google Tag Manager and other tracking/SEO scripts. These are injected site-wide in the front-end layout.</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Google Tag Manager container ID(s)</label>
                            <textarea name="gtag" class="form-control" rows="2" placeholder="One per line, e.g.&#10;GTM-P0GFW4PT&#10;GTM-W7VXZ48P">{{ $siteInfo->gtag }}</textarea>
                            <small class="text-muted">Enter one GTM container ID per line (e.g. GTM-XXXXXXX). The standard GTM script and noscript iframe will be added automatically.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Custom head scripts</label>
                            <textarea name="custom_head_script" class="form-control" rows="4" placeholder="Paste any <script>, <meta>, or other HTML to inject in <head>">{{ $siteInfo->custom_head_script }}</textarea>
                            <small class="text-muted">Optional. Raw HTML injected before &lt;/head&gt;. Use for analytics, verification tags, etc.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Custom body scripts</label>
                            <textarea name="custom_body_script" class="form-control" rows="4" placeholder="Paste any <noscript>, <script>, or other HTML to inject at start of <body>">{{ $siteInfo->custom_body_script }}</textarea>
                            <small class="text-muted">Optional. Raw HTML injected right after &lt;body&gt;. Use for GTM noscript fallbacks or other body-level snippets.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5">Save Information</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    var common = { 
        height: 350, 
        plugins: 'lists link image code', 
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
        branding: false,
        promotion: false
    };
    if (typeof tinymce !== 'undefined') {
        tinymce.init(Object.assign({ selector: '#privacy_policy' }, common));
        tinymce.init(Object.assign({ selector: '#terms_and_conditions' }, common));
        tinymce.init(Object.assign({ selector: '#disclaimer' }, common));
    }
</script>
@endpush
