@php $text = trim($field->label) !== '' && $field->label !== $field->type->label() ? $field->label : null; @endphp
<div class="fb-divider" role="separator" id="{{ $inputId }}">
    @if ($text)
        <span class="fb-divider__text">{{ $text }}</span>
    @endif
</div>
