<?php

namespace Packstub\FormBuilder\Fields;

use Illuminate\Support\Collection;

/**
 * A group of fields with a heading: a card on a single-page form, a step
 * on a multi-step one. Fields outside any section form an unnamed section.
 */
final class Section
{
    /**
     * @param  Collection<int, Field>  $fields
     */
    public function __construct(
        public readonly string $key,
        public readonly ?string $label,
        public readonly ?string $description,
        public readonly Conditions $visibility,
        public readonly Collection $fields,
        public readonly bool $implicit = false,
    ) {}

    public function isNamed(): bool
    {
        return ! $this->implicit && $this->label !== null;
    }

    /**
     * Whether the section shows for the given values: its own conditions
     * and, without them, at least one visible field.
     *
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $visibleKeys  The keys of the fields visible for the values.
     */
    public function isVisible(array $values, array $visibleKeys): bool
    {
        if (! $this->visibility->passes($values)) {
            return false;
        }

        foreach ($this->fields as $field) {
            if (! $field->isInput() || in_array($field->key, $visibleKeys, true)) {
                return true;
            }
        }

        return $this->fields->isEmpty();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'visibility' => $this->visibility->toArray(),
            'fields' => $this->fields->map(fn (Field $field): string => $field->key)->values()->all(),
        ];
    }
}
