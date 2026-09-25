<label class="fb-choice fb-choice--single" for="{{ $inputId }}">
    <input type="checkbox" id="{{ $inputId }}" name="{{ $field->key }}" value="1" @checked(filter_var($value, FILTER_VALIDATE_BOOLEAN)) @if ($field->required && ! $field->isConditional()) required aria-required="true" @endif aria-describedby="{{ $field->hint ? $inputId.'-hint ' : '' }}{{ $inputId }}-error" @if ($error) aria-invalid="true" @endif>
    <span>
        {{ $field->label }}
        @if (filled($field->option('link_url')))
            <a href="{{ $field->option('link_url') }}" target="_blank" rel="noopener" class="fb-link">{{ $field->option('link_text') ?: $field->option('link_url') }}</a>
        @endif
        @if ($field->required)
            <span class="fb-required" aria-hidden="true">*</span>
        @endif
    </span>
</label>
@include('packstub-form-builder::fields._hint')
