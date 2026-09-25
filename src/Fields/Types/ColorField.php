<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Component;
use Packstub\FormBuilder\Fields\Field;

class ColorField extends InputField
{
    public static function id(): string
    {
        return 'color';
    }

    public function icon(): string
    {
        return 'heroicon-o-swatch';
    }

    public function inputType(): string
    {
        return 'color';
    }

    public function hasPlaceholder(): bool
    {
        return false;
    }

    public function editorSchema(): array
    {
        return [];
    }

    public function inputAttributes(Field $field): array
    {
        return [];
    }

    public function rules(Field $field): array
    {
        return ['string', 'regex:/^#[0-9a-fA-F]{6}$/'];
    }

    public function normalize(mixed $value, Field $field): mixed
    {
        $value = parent::normalize($value, $field);

        return is_string($value) ? strtolower($value) : $value;
    }

    public function formComponent(Field $field): Component
    {
        return $this->configure(ColorPicker::make($field->key), $field);
    }
}
