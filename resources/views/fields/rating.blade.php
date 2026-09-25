@php $max = $field->type->max($field); @endphp
<fieldset class="fb-fieldset" aria-describedby="{{ $field->hint ? $inputId.'-hint ' : '' }}{{ $inputId }}-error" @if ($error) aria-invalid="true" @endif>
    <legend class="fb-label">
        {{ $field->label }}
        @if ($field->required)
            <span class="fb-required" aria-hidden="true">*</span>
        @endif
    </legend>
    <div class="fb-rating" role="radiogroup">
        @for ($score = 1; $score <= $max; $score++)
            <label class="fb-rating__star" for="{{ $inputId }}-{{ $score }}" title="{{ $score }} / {{ $max }}">
                <input type="radio" id="{{ $inputId }}-{{ $score }}" name="{{ $field->key }}" value="{{ $score }}" @checked((string) $value === (string) $score) @if ($field->required && ! $field->isConditional()) required @endif>
                <span aria-hidden="true">★</span>
                <span class="fb-sr-only">{{ $score }}</span>
            </label>
        @endfor
    </div>
    @include('packstub-form-builder::fields._hint')
</fieldset>
