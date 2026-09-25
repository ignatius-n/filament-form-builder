<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\HtmlString;
use Packstub\FormBuilder\Fields\Field;

/**
 * "I agree to the terms": a checkbox whose label links to a page.
 */
class ConsentField extends CheckboxField
{
    public static function id(): string
    {
        return 'consent';
    }

    public function icon(): string
    {
        return 'heroicon-o-shield-check';
    }

    public function view(): string
    {
        return 'packstub-form-builder::fields.consent';
    }

    public function editorSchema(): array
    {
        return [
            TextInput::make('link_text')
                ->label(__('packstub-form-builder::form-builder.editor.link_text'))
                ->placeholder('privacy policy')
                ->maxLength(255),
            TextInput::make('link_url')
                ->label(__('packstub-form-builder::form-builder.editor.link_url'))
                ->url()
                ->maxLength(2048),
        ];
    }

    public function formComponent(Field $field): Component
    {
        $checkbox = Checkbox::make($field->key);

        if ($field->required) {
            $checkbox->accepted();
        }

        $component = $this->configure($checkbox, $field);

        if (filled($url = $field->option('link_url'))) {
            $text = e((string) ($field->option('link_text') ?: $url));
            $component->label(new HtmlString(e($field->label).' <a href="'.e($url).'" target="_blank" rel="noopener" class="fi-link">'.$text.'</a>'));
        }

        return $component;
    }
}
