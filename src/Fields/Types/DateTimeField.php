<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Carbon;
use Packstub\FormBuilder\Fields\Field;

class DateTimeField extends DateField
{
    public static function id(): string
    {
        return 'datetime';
    }

    public function icon(): string
    {
        return 'heroicon-o-clock';
    }

    public function inputType(): string
    {
        return 'datetime-local';
    }

    public function normalize(mixed $value, Field $field): mixed
    {
        $value = parent::normalize($value, $field);

        if (! is_string($value)) {
            return $value;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return $value;
        }
    }

    public function format(mixed $value, Field $field): string
    {
        if (! is_string($value) || $value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format((string) config('packstub-form-builder.formats.datetime', 'Y-m-d H:i'));
        } catch (\Throwable) {
            return $value;
        }
    }

    public function formComponent(Field $field): Component
    {
        $input = DateTimePicker::make($field->key)->native()->seconds(false);

        if (filled($min = $field->option('min'))) {
            $input->minDate($min);
        }

        if (filled($max = $field->option('max'))) {
            $input->maxDate($max);
        }

        return $this->configure($input, $field);
    }
}
