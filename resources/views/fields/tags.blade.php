@include('packstub-form-builder::fields._label')
<input
    class="fb-input"
    type="text"
    id="{{ $inputId }}"
    name="{{ $field->key }}"
    value="{{ is_array($value) ? implode(', ', $value) : (is_scalar($value) ? $value : '') }}"
    @if ($field->placeholder) placeholder="{{ $field->placeholder }}" @endif
    @if ($field->required && ! $field->isConditional()) required aria-required="true" @endif
    aria-describedby="{{ $field->hint ? $inputId.'-hint ' : '' }}{{ $inputId }}-error"
    @if ($error) aria-invalid="true" @endif
>
@include('packstub-form-builder::fields._hint')
