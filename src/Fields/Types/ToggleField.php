<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Packstub\FormBuilder\Fields\Field;

/**
 * A yes / no switch; the same value as a checkbox with another look.
 */
class ToggleField extends CheckboxField
{
    public static function id(): string
    {
        return 'toggle';
    }

    public function icon(): string
    {
        return 'heroicon-o-adjustments-horizontal';
    }

    public function view(): string
    {
        return 'packstub-form-builder::fields.toggle';
    }

    public function formComponent(Field $field): Component
    {
        $toggle = Toggle::make($field->key);

        if ($field->required) {
            $toggle->accepted();
        }

        return $this->configure($toggle, $field);
    }
}
