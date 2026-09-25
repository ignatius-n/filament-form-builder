<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Packstub\FormBuilder\Fields\Field;
use Packstub\FormBuilder\Fields\FieldType;

/**
 * Free values the visitor types, comma-separated in the plain renderer and
 * as tags in the Livewire one; stored as a list.
 */
class TagsField extends FieldType
{
    public static function id(): string
    {
        return 'tags';
    }

    public function icon(): string
    {
        return 'heroicon-o-tag';
    }

    public function acceptsMultiple(): bool
    {
        return true;
    }

    public function editorSchema(): array
    {
        return [
            TextInput::make('max_items')
                ->label(__('packstub-form-builder::form-builder.editor.max_value'))
                ->integer()
                ->minValue(1),
        ];
    }

    public function rules(Field $field): array
    {
        $rules = ['array'];

        if (filled($max = $field->option('max_items'))) {
            $rules[] = 'max:'.(int) $max;
        }

        return $rules;
    }

    public function elementRules(Field $field): array
    {
        return ['string', 'max:100'];
    }

    public function prepare(mixed $value, Field $field): mixed
    {
        return $value === null || $value === '' ? null : $this->normalize($value, $field);
    }

    public function comparableValue(mixed $value, Field $field): mixed
    {
        return $this->normalize($value, $field);
    }

    public function normalize(mixed $value, Field $field): mixed
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($item): string => is_scalar($item) ? trim((string) $item) : '',
            (array) $value,
        ), fn (string $item): bool => $item !== '')));
    }

    public function format(mixed $value, Field $field): string
    {
        return is_array($value) ? implode(', ', $value) : (string) ($value ?? '');
    }

    public function formComponent(Field $field): Component
    {
        return $this->configure(TagsInput::make($field->key)->separator(','), $field);
    }
}
