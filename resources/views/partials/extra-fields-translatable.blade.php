@php
    /**
     * Required variables:
     * - $configKey (string): config path under cms-kit.database (e.g., "testimonials.items")
     * - $lang (object/array): language object with "code" (and optionally "name")
     * - $existingTranslations (array): translations array (optional)
     */
    $configKey = $configKey ?? null;
    $lang = $lang ?? null;
    $existingTranslations = $existingTranslations ?? [];

    $extraFields = [];
    if ($configKey) {
        $extraFields = config("cms-kit.database.{$configKey}.extra_fields", []);
    }

    $translatableFields = collect($extraFields)
        ->filter(fn($field) => ($field['translatable'] ?? false));

    $values = $existingTranslations[$lang->code]['extra_fields'] ?? [];
@endphp

@if($lang && $translatableFields->count())
<div class="mb-4">
    @php $showLanguageUi = config('cms-kit.common.modules.languages', true); @endphp
    <h6 class="fw-bold mb-3">Additional Fields@if($showLanguageUi) ({{ strtoupper($lang->code) }})@endif</h6>
    <div class="row g-3">
        @foreach($translatableFields as $fieldName => $fieldConfig)
            @php
                $fieldValue = old("translations.{$lang->code}.extra_fields.{$fieldName}", $values[$fieldName] ?? '');
            @endphp
            <div class="col-md-6">
                <label class="form-label">{{ $fieldConfig['label'] ?? ucfirst(str_replace('_', ' ', $fieldName)) }} @if($fieldConfig['required'] ?? false)<span class="text-danger">*</span>@endif</label>

                @php $fieldType = $fieldConfig['type'] ?? 'text'; @endphp

                @if($fieldType === 'textarea')
                    <textarea name="translations[{{ $lang->code }}][extra_fields][{{ $fieldName }}]" class="form-control @error("translations.{$lang->code}.extra_fields.{$fieldName}") is-invalid @enderror" rows="3" placeholder="{{ $fieldConfig['placeholder'] ?? '' }}" {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}>{{ $fieldValue }}</textarea>
                @elseif($fieldType === 'select')
                    <select name="translations[{{ $lang->code }}][extra_fields][{{ $fieldName }}]" class="form-select @error("translations.{$lang->code}.extra_fields.{$fieldName}") is-invalid @enderror" {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}>
                        <option value="">-- Select --</option>
                        @foreach($fieldConfig['options'] ?? [] as $optValue => $optLabel)
                            <option value="{{ $optValue }}" {{ $fieldValue == $optValue ? 'selected' : '' }}>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                @elseif($fieldType === 'file')
                    <input type="file" name="translations[{{ $lang->code }}][extra_fields][{{ $fieldName }}]" class="form-control @error("translations.{$lang->code}.extra_fields.{$fieldName}") is-invalid @enderror" {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}>
                @elseif($fieldType === 'checkbox')
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="translations[{{ $lang->code }}][extra_fields][{{ $fieldName }}]" value="0">
                        <input type="checkbox" name="translations[{{ $lang->code }}][extra_fields][{{ $fieldName }}]" value="1" class="form-check-input @error("translations.{$lang->code}.extra_fields.{$fieldName}") is-invalid @enderror" id="translatable-extra-field-{{ $lang->code }}-{{ $fieldName }}" {{ $fieldValue ? 'checked' : '' }} {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}>
                        <label class="form-check-label" for="translatable-extra-field-{{ $lang->code }}-{{ $fieldName }}">{{ $fieldConfig['placeholder'] ?? 'Enable' }}</label>
                    </div>
                @elseif($fieldType === 'number')
                    <input type="number" name="translations[{{ $lang->code }}][extra_fields][{{ $fieldName }}]" class="form-control @error("translations.{$lang->code}.extra_fields.{$fieldName}") is-invalid @enderror" value="{{ $fieldValue }}" placeholder="{{ $fieldConfig['placeholder'] ?? '' }}" {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}>
                @elseif($fieldType === 'email')
                    <input type="email" name="translations[{{ $lang->code }}][extra_fields][{{ $fieldName }}]" class="form-control @error("translations.{$lang->code}.extra_fields.{$fieldName}") is-invalid @enderror" value="{{ $fieldValue }}" placeholder="{{ $fieldConfig['placeholder'] ?? '' }}" {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}>
                @else
                    <input type="text" name="translations[{{ $lang->code }}][extra_fields][{{ $fieldName }}]" class="form-control @error("translations.{$lang->code}.extra_fields.{$fieldName}") is-invalid @enderror" value="{{ $fieldValue }}" placeholder="{{ $fieldConfig['placeholder'] ?? '' }}" {{ ($fieldConfig['required'] ?? false) ? 'required' : '' }}>
                @endif

                @if(isset($fieldConfig['helpText']))
                    <small class="text-muted d-block mt-1">{{ $fieldConfig['helpText'] }}</small>
                @endif

                @error("translations.{$lang->code}.extra_fields.{$fieldName}")
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        @endforeach
    </div>
</div>
@endif
