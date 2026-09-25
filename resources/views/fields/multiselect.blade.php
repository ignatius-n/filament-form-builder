@include('packstub-form-builder::fields._label')
@php $selected = array_map('strval', (array) ($value ?? [])); @endphp
<select
    class="fb-input fb-select fb-select--multiple"
    id="{{ $inputId }}"
    name="{{ $field->key }}[]"
    multiple
    size="{{ min(6, max(3, count($field->choices()))) }}"
    @if ($field->required && ! $field->isConditional()) required aria-required="true" @endif
    aria-describedby="{{ $field->hint ? $inputId.'-hint ' : '' }}{{ $inputId }}-error"
    @if ($error) aria-invalid="true" @endif
>
    @foreach ($field->choices() as $choiceValue => $choiceLabel)
        <option value="{{ $choiceValue }}" @selected(in_array((string) $choiceValue, $selected, true))>{{ $choiceLabel }}</option>
    @endforeach
</select>
@include('packstub-form-builder::fields._hint')
