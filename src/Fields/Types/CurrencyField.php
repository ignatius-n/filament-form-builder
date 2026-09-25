<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Packstub\FormBuilder\Fields\Field;

/**
 * An amount: a decimal number with a prefix or suffix (a currency sign, a
 * unit) and a fixed number of decimals.
 */
class CurrencyField extends NumberField
{
    public static function id(): string
    {
        return 'currency';
    }

    public function icon(): string
    {
        return 'heroicon-o-banknotes';
    }

    public function editorSchema(): array
    {
        return [
            TextInput::make('prefix')
                ->label(__('packstub-form-builder::form-builder.editor.prefix'))
                ->placeholder('$')
                ->maxLength(10),
            TextInput::make('suffix')
                ->label(__('packstub-form-builder::form-builder.editor.suffix'))
                ->placeholder('EUR')
                ->maxLength(10),
            TextInput::make('min')
                ->label(__('packstub-form-builder::form-builder.editor.min_value'))
                ->numeric(),
            TextInput::make('max')
                ->label(__('packstub-form-builder::form-builder.editor.max_value'))
                ->numeric(),
            TextInput::make('decimals')
                ->label(__('packstub-form-builder::form-builder.editor.decimals'))
                ->integer()
                ->minValue(0)
                ->maxValue(6)
                ->default(2),
        ];
    }

    public function decimals(Field $field): int
    {
        return max(0, min(6, (int) ($field->option('decimals') ?? 2)));
    }

    public function inputAttributes(Field $field): array
    {
        $decimals = $this->decimals($field);

        return array_filter([
            'min' => $field->option('min'),
            'max' => $field->option('max'),
            'step' => $decimals === 0 ? '1' : '0.'.str_repeat('0', $decimals - 1).'1',
            'inputmode' => 'decimal',
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    public function normalize(mixed $value, Field $field): mixed
    {
        $value = parent::normalize($value, $field);

        return is_numeric($value) ? round((float) $value, $this->decimals($field)) : $value;
    }

    public function format(mixed $value, Field $field): string
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return '';
        }

        return trim((string) $field->option('prefix', '')).number_format((float) $value, $this->decimals($field)).' '.trim((string) $field->option('suffix', ''));
    }

    public function formComponent(Field $field): Component
    {
        $input = TextInput::make($field->key)->numeric()->step($this->decimals($field) === 0 ? 1 : 1 / (10 ** $this->decimals($field)));

        if (filled($prefix = $field->option('prefix'))) {
            $input->prefix($prefix);
        }

        if (filled($suffix = $field->option('suffix'))) {
            $input->suffix($suffix);
        }

        if (filled($min = $field->option('min'))) {
            $input->minValue($min);
        }

        if (filled($max = $field->option('max'))) {
            $input->maxValue($max);
        }

        return $this->configure($input, $field);
    }
}
