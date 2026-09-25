<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Carbon;
use Packstub\FormBuilder\Fields\Field;
use Packstub\FormBuilder\Fields\ValidationRules;

class TimeField extends InputField
{
    public static function id(): string
    {
        return 'time';
    }

    public function icon(): string
    {
        return 'heroicon-o-clock';
    }

    public function inputType(): string
    {
        return 'time';
    }

    public function ruleCategory(): ?string
    {
        return ValidationRules::CATEGORY_DATE;
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
        return ['date_format:H:i,H:i:s'];
    }

    public function normalize(mixed $value, Field $field): mixed
    {
        $value = parent::normalize($value, $field);

        return is_string($value) ? substr($value, 0, 5) : $value;
    }

    public function format(mixed $value, Field $field): string
    {
        if (! is_string($value) || $value === '') {
            return '';
        }

        try {
            return Carbon::createFromFormat('H:i', substr($value, 0, 5))->format((string) config('packstub-form-builder.formats.time', 'H:i'));
        } catch (\Throwable) {
            return $value;
        }
    }

    public function formComponent(Field $field): Component
    {
        return $this->configure(TimePicker::make($field->key)->native()->seconds(false), $field);
    }
}
