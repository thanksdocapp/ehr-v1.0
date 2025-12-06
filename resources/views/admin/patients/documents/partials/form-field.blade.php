{{--
    Form Field Partial
    Renders individual form fields based on field type with existing data

    Variables:
    - $field: array with type, name, label, required, options, etc.
    - $formData: array of existing form data
    - $readonly: boolean whether the field should be read-only
--}}

@php
    $fieldName = $field['name'] ?? '';
    $fieldLabel = $field['label'] ?? ucwords(str_replace('_', ' ', $fieldName));
    $fieldType = $field['type'] ?? 'text';
    $isRequired = $field['required'] ?? false;
    $value = $formData[$fieldName] ?? '';
    $inputName = "form_data[{$fieldName}]";
@endphp

@switch($fieldType)
    @case('text')
        <div class="col-md-6 form-field-group">
            <label class="form-label">
                {{ $fieldLabel }}
                @if($isRequired)<span class="required-indicator">*</span>@endif
            </label>
            <input type="text"
                   class="form-control"
                   name="{{ $inputName }}"
                   value="{{ old($inputName, $value) }}"
                   {{ $isRequired && !$readonly ? 'required' : '' }}
                   {{ $readonly ? 'readonly' : '' }}>
        </div>
        @break

    @case('textarea')
        <div class="col-12 form-field-group">
            <label class="form-label">
                {{ $fieldLabel }}
                @if($isRequired)<span class="required-indicator">*</span>@endif
            </label>
            <textarea class="form-control"
                      name="{{ $inputName }}"
                      rows="{{ $field['rows'] ?? 4 }}"
                      {{ $isRequired && !$readonly ? 'required' : '' }}
                      {{ $readonly ? 'readonly' : '' }}>{{ old($inputName, $value) }}</textarea>
        </div>
        @break

    @case('number')
        <div class="col-md-4 form-field-group">
            <label class="form-label">
                {{ $fieldLabel }}
                @if($isRequired)<span class="required-indicator">*</span>@endif
            </label>
            <input type="number"
                   class="form-control"
                   name="{{ $inputName }}"
                   value="{{ old($inputName, $value) }}"
                   @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                   @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                   step="{{ $field['step'] ?? 1 }}"
                   {{ $isRequired && !$readonly ? 'required' : '' }}
                   {{ $readonly ? 'readonly' : '' }}>
        </div>
        @break

    @case('date')
        <div class="col-md-4 form-field-group">
            <label class="form-label">
                {{ $fieldLabel }}
                @if($isRequired)<span class="required-indicator">*</span>@endif
            </label>
            <input type="date"
                   class="form-control"
                   name="{{ $inputName }}"
                   value="{{ old($inputName, $value) }}"
                   @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                   @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                   {{ $isRequired && !$readonly ? 'required' : '' }}
                   {{ $readonly ? 'readonly' : '' }}>
        </div>
        @break

    @case('select')
        <div class="col-md-6 form-field-group">
            <label class="form-label">
                {{ $fieldLabel }}
                @if($isRequired)<span class="required-indicator">*</span>@endif
            </label>
            <select class="form-select"
                    name="{{ $inputName }}"
                    {{ $isRequired && !$readonly ? 'required' : '' }}
                    {{ $readonly ? 'disabled' : '' }}>
                <option value="">-- Select --</option>
                @foreach($field['options'] ?? [] as $option)
                    @php
                        $optValue = is_array($option) ? ($option['value'] ?? $option) : $option;
                        $optLabel = is_array($option) ? ($option['label'] ?? $optValue) : $option;
                    @endphp
                    <option value="{{ $optValue }}" {{ old($inputName, $value) == $optValue ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
            @if($readonly)
                <input type="hidden" name="{{ $inputName }}" value="{{ $value }}">
            @endif
        </div>
        @break

    @case('checkbox')
        <div class="col-md-6 form-field-group">
            <div class="form-check mt-4">
                <input type="hidden" name="{{ $inputName }}" value="0">
                <input type="checkbox"
                       class="form-check-input"
                       id="field_{{ $fieldName }}"
                       name="{{ $inputName }}"
                       value="1"
                       {{ old($inputName, $value) ? 'checked' : '' }}
                       {{ $readonly ? 'disabled' : '' }}>
                <label class="form-check-label" for="field_{{ $fieldName }}">
                    {{ $fieldLabel }}
                    @if($isRequired)<span class="required-indicator">*</span>@endif
                </label>
                @if($readonly && $value)
                    <input type="hidden" name="{{ $inputName }}" value="1">
                @endif
            </div>
        </div>
        @break

    @case('checkbox_group')
        @php
            $selectedValues = is_array($value) ? $value : (is_string($value) && !empty($value) ? explode(',', $value) : []);
        @endphp
        <div class="col-12 form-field-group">
            <label class="form-label">
                {{ $fieldLabel }}
                @if($isRequired)<span class="required-indicator">*</span>@endif
            </label>
            <div class="checkbox-group-container">
                @foreach($field['options'] ?? [] as $idx => $option)
                    @php
                        $optValue = is_array($option) ? ($option['value'] ?? $option) : $option;
                        $optLabel = is_array($option) ? ($option['label'] ?? $optValue) : $option;
                        $isChecked = in_array($optValue, $selectedValues);
                    @endphp
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="field_{{ $fieldName }}_{{ $idx }}"
                               name="{{ $inputName }}[]"
                               value="{{ $optValue }}"
                               {{ $isChecked ? 'checked' : '' }}
                               {{ $readonly ? 'disabled' : '' }}>
                        <label class="form-check-label" for="field_{{ $fieldName }}_{{ $idx }}">
                            {{ $optLabel }}
                        </label>
                    </div>
                    @if($readonly && $isChecked)
                        <input type="hidden" name="{{ $inputName }}[]" value="{{ $optValue }}">
                    @endif
                @endforeach
            </div>
        </div>
        @break

    @case('radio_group')
        <div class="col-12 form-field-group">
            <label class="form-label">
                {{ $fieldLabel }}
                @if($isRequired)<span class="required-indicator">*</span>@endif
            </label>
            <div class="radio-group-container">
                @foreach($field['options'] ?? [] as $idx => $option)
                    @php
                        $optValue = is_array($option) ? ($option['value'] ?? $option) : $option;
                        $optLabel = is_array($option) ? ($option['label'] ?? $optValue) : $option;
                    @endphp
                    <div class="form-check">
                        <input type="radio"
                               class="form-check-input"
                               id="field_{{ $fieldName }}_{{ $idx }}"
                               name="{{ $inputName }}"
                               value="{{ $optValue }}"
                               {{ old($inputName, $value) == $optValue ? 'checked' : '' }}
                               {{ $isRequired && !$readonly ? 'required' : '' }}
                               {{ $readonly ? 'disabled' : '' }}>
                        <label class="form-check-label" for="field_{{ $fieldName }}_{{ $idx }}">
                            {{ $optLabel }}
                        </label>
                    </div>
                @endforeach
                @if($readonly && $value)
                    <input type="hidden" name="{{ $inputName }}" value="{{ $value }}">
                @endif
            </div>
        </div>
        @break

    @case('info_text')
        <div class="col-12 form-field-group">
            <div class="info-text-field">
                <i class="fas fa-info-circle me-2"></i>{{ $field['text'] ?? $fieldLabel }}
            </div>
        </div>
        @break

    @default
        <div class="col-md-6 form-field-group">
            <label class="form-label">
                {{ $fieldLabel }}
                @if($isRequired)<span class="required-indicator">*</span>@endif
            </label>
            <input type="text"
                   class="form-control"
                   name="{{ $inputName }}"
                   value="{{ old($inputName, $value) }}"
                   {{ $isRequired && !$readonly ? 'required' : '' }}
                   {{ $readonly ? 'readonly' : '' }}>
        </div>
@endswitch
