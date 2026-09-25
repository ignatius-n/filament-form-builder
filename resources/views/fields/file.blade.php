@include('packstub-form-builder::fields._label')
@php
    $type = $field->type;
    $multiple = $type->isMultiple($field);
    $accept = $type->acceptAttribute($field);
@endphp
<input
    class="fb-input fb-file"
    type="file"
    id="{{ $inputId }}"
    name="{{ $field->key }}[]"
    @if ($multiple) multiple @endif
    @if ($accept) accept="{{ $accept }}" @endif
    data-fb-max-kb="{{ $type->maxKb($field) }}"
    @if ($field->required && ! $field->isConditional()) required aria-required="true" @endif
    aria-describedby="{{ $inputId }}-hint {{ $inputId }}-error"
    @if ($error) aria-invalid="true" @endif
>
<p class="fb-hint" id="{{ $inputId }}-hint">
    @if ($field->hint){{ $field->hint }} · @endif
    {{ $accept ? __('packstub-form-builder::form-builder.frontend.file_hint_types', ['size' => $type->maxKb($field), 'types' => str_replace(',', ', ', $accept)]) : __('packstub-form-builder::form-builder.frontend.file_hint', ['size' => $type->maxKb($field)]) }}
</p>
