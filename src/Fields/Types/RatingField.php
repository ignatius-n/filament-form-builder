<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Component;
use Packstub\FormBuilder\Fields\Field;
use Packstub\FormBuilder\Fields\FieldType;
use Packstub\FormBuilder\Fields\ValidationRules;

/**
 * A score from 1 to N (5 stars by default).
 */
class RatingField extends FieldType
{
    public static function id(): string
    {
        return 'rating';
    }

    public function icon(): string
    {
        return 'heroicon-o-star';
    }

    public function ruleCategory(): ?string
    {
        return ValidationRules::CATEGORY_NUMBER;
    }

    public function hasPlaceholder(): bool
    {
        return false;
    }

    public function editorSchema(): array
    {
        return [
            TextInput::make('max')
                ->label(__('packstub-form-builder::form-builder.editor.max_stars'))
                ->helperText(__('packstub-form-builder::form-builder.editor.max_stars_hint'))
                ->integer()
                ->minValue(1)
                ->maxValue(10)
                ->default(5),
        ];
    }

    public function max(Field $field): int
    {
        return max(1, min(10, (int) ($field->option('max') ?: 5)));
    }

    public function rules(Field $field): array
    {
        return ['integer', 'between:1,'.$this->max($field)];
    }

    public function normalize(mixed $value, Field $field): mixed
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    public function format(mixed $value, Field $field): string
    {
        return $value === null || $value === '' ? '' : $value.' / '.$this->max($field);
    }

    public function formComponent(Field $field): Component
    {
        $options = [];

        for ($score = 1; $score <= $this->max($field); $score++) {
            $options[$score] = (string) $score;
        }

        return $this->configure(ToggleButtons::make($field->key)->options($options)->inline()->grouped(), $field);
    }
}
