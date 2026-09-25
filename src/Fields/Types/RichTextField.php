<?php

namespace Packstub\FormBuilder\Fields\Types;

use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Component;
use Illuminate\Support\HtmlString;
use Packstub\FormBuilder\Fields\Field;

/**
 * A long answer with basic formatting: a rich editor in the Livewire
 * renderer, a plain text area elsewhere (no JavaScript needed). Stored as
 * HTML limited to a few safe tags.
 */
class RichTextField extends TextareaField
{
    public const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><s><ul><ol><li><a><h2><h3><h4><blockquote><code><pre>';

    public static function id(): string
    {
        return 'richtext';
    }

    public function icon(): string
    {
        return 'heroicon-o-document-text';
    }

    public function view(): string
    {
        return 'packstub-form-builder::fields.textarea';
    }

    public function normalize(mixed $value, Field $field): mixed
    {
        $value = parent::normalize($value, $field);

        if (! is_string($value)) {
            return $value;
        }

        $html = strip_tags($value, self::ALLOWED_TAGS);
        // Drop on* handlers and javascript: links.
        $html = (string) preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = (string) preg_replace('/href\s*=\s*("|\')\s*javascript:[^"\']*\1/i', 'href="#"', $html);

        // Plain text from the text area: keep the line breaks.
        if (! str_contains($html, '<')) {
            $html = nl2br(e($html));
        }

        return $html;
    }

    public function format(mixed $value, Field $field): string
    {
        return is_string($value) ? trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br />', '</p>'], "\n", $value)))) : '';
    }

    public function display(mixed $value, Field $field): string|HtmlString
    {
        return is_string($value) ? new HtmlString(strip_tags($value, self::ALLOWED_TAGS)) : '';
    }

    public function formComponent(Field $field): Component
    {
        return $this->configure(RichEditor::make($field->key)->toolbarButtons([
            'bold', 'italic', 'underline', 'strike', 'link', 'bulletList', 'orderedList', 'h2', 'h3', 'blockquote', 'undo', 'redo',
        ]), $field);
    }
}
