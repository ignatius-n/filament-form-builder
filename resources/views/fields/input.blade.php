@include('packstub-form-builder::fields._label')
@php
    $type = $field->type;
    $inputType = method_exists($type, 'inputType') ? $type->inputType() : 'text';
    $extra = method_exists($type, 'inputAttributes') ? $type->inputAttributes($field) : [];
    $autocomplete = match ($type::id()) { 'email' => 'email', 'phone' => 'tel', 'url' => 'url', default => null };
    $prefix = $type::id() === 'currency' ? $field->option('prefix') : null;
    $suffix = $type::id() === 'currency' ? $field->option('suffix') : null;
@endphp
@if ($prefix || $suffix)
<div class="fb-affix">
    @if ($prefix)<span class="fb-affix__prefix">{{ $prefix }}</span>@endif
@endif
<input
    class="fb-input"
    type="{{ $inputType }}"
    id="{{ $inputId }}"
    name="{{ $field->key }}"
    value="{{ is_scalar($value) ? $value : '' }}"
    @if ($field->placeholder) placeholder="{{ $field->placeholder }}" @endif
    @if ($field->required && ! $field->isConditional()) required aria-required="true" @endif
    @foreach ($extra as $attribute => $attributeValue) {{ $attribute }}="{{ $attributeValue }}" @endforeach
    @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
    aria-describedby="{{ $field->hint ? $inputId.'-hint ' : '' }}{{ $inputId }}-error"
    @if ($error) aria-invalid="true" @endif
>
@if ($prefix || $suffix)
    @if ($suffix)<span class="fb-affix__suffix">{{ $suffix }}</span>@endif
</div>
@endif
@include('packstub-form-builder::fields._hint')
