<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Packstub\FormBuilder\Fields\Field;

class MultiSelectField extends CheckboxesField
{
    public static function id(): string
    {
        return 'multiselect';
    }

    public function icon(): string
    {
        return 'heroicon-o-queue-list';
    }

    public function view(): string
    {
        return 'packstub-form-builder::fields.multiselect';
    }

    public function hasPlaceholder(): bool
    {
        return true;
    }

    public function formComponent(Field $field): Component
    {
        return $this->configure(Select::make($field->key)->options($field->choices())->multiple()->native(false), $field);
    }
}
