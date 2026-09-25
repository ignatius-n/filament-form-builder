<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Html;
use Packstub\FormBuilder\Fields\Field;
use Packstub\FormBuilder\Fields\FieldType;

/**
 * A horizontal rule, with an optional short text on it.
 */
class DividerField extends FieldType
{
    public static function id(): string
    {
        return 'divider';
    }

    public function icon(): string
    {
        return 'heroicon-o-minus';
    }

    public function isInput(): bool
    {
        return false;
    }

    public function formComponent(Field $field): Component
    {
        $label = trim($field->label) !== '' && $field->label !== $this->label() ? '<span class="fb-divider__text">'.e($field->label).'</span>' : '';

        return Html::make('<div class="fb-divider" role="separator">'.$label.'</div>')->columnSpanFull();
    }
}
