<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Packstub\FormBuilder\Fields\Countries;
use Packstub\FormBuilder\Fields\Field;
use Packstub\FormBuilder\Fields\ValidationRules;

/**
 * A country, stored as its ISO 3166-1 alpha-2 code.
 */
class CountryField extends SelectField
{
    public static function id(): string
    {
        return 'country';
    }

    public function icon(): string
    {
        return 'heroicon-o-globe-alt';
    }

    public function editorSchema(): array
    {
        return [
            TextInput::make('countries')
                ->label(__('packstub-form-builder::form-builder.editor.countries'))
                ->helperText(__('packstub-form-builder::form-builder.editor.countries_hint'))
                ->placeholder('US, GB, DE'),
        ];
    }

    public function choices(Field $field): array
    {
        $all = Countries::all();
        $only = array_map('strtoupper', ValidationRules::list((string) $field->option('countries', '')));

        if ($only === []) {
            return $all;
        }

        $choices = [];

        foreach ($only as $code) {
            if (isset($all[$code])) {
                $choices[$code] = $all[$code];
            }
        }

        return $choices === [] ? $all : $choices;
    }

    public function view(): string
    {
        return 'packstub-form-builder::fields.select';
    }

    public function prepare(mixed $value, Field $field): mixed
    {
        return is_string($value) ? strtoupper(trim($value)) : $value;
    }

    public function normalize(mixed $value, Field $field): mixed
    {
        $value = parent::normalize($value, $field);

        return is_string($value) ? strtoupper($value) : $value;
    }

    public function formComponent(Field $field): Component
    {
        return $this->configure(Select::make($field->key)->options($field->choices())->searchable()->native(false), $field);
    }
}
