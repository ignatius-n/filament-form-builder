<?php

namespace Packstub\FormBuilder\Fields;

use Illuminate\Validation\Rule;

/**
 * The validation rules the builder offers as a list, each with the kinds of
 * field it applies to and the value it takes. A field stores them as
 * "validation": [{rule: "min", value: "3"}, ...]; toLaravel() turns one
 * into the Laravel rule.
 */
final class ValidationRules
{
    public const CATEGORY_TEXT = 'text';

    public const CATEGORY_NUMBER = 'number';

    public const CATEGORY_DATE = 'date';

    public const CATEGORY_CHOICE = 'choice';

    public const CATEGORY_MULTIPLE = 'multiple';

    public const CATEGORY_FILE = 'file';

    public const CATEGORY_BOOLEAN = 'boolean';

    /**
     * rule id => [categories, value kind (text / number / date / list / none)].
     *
     * @return array<string, array{0: array<int, string>, 1: string}>
     */
    public static function catalog(): array
    {
        $text = [self::CATEGORY_TEXT];
        $number = [self::CATEGORY_NUMBER];
        $date = [self::CATEGORY_DATE];
        $multiple = [self::CATEGORY_MULTIPLE];
        $file = [self::CATEGORY_FILE];

        return [
            'min' => [[self::CATEGORY_TEXT, self::CATEGORY_NUMBER, self::CATEGORY_MULTIPLE], 'number'],
            'max' => [[self::CATEGORY_TEXT, self::CATEGORY_NUMBER, self::CATEGORY_MULTIPLE], 'number'],
            'between' => [[self::CATEGORY_TEXT, self::CATEGORY_NUMBER], 'text'],
            'size' => [[self::CATEGORY_TEXT, self::CATEGORY_NUMBER, self::CATEGORY_MULTIPLE], 'number'],
            'regex' => [$text, 'text'],
            'not_regex' => [$text, 'text'],
            'alpha' => [$text, 'none'],
            'alpha_num' => [$text, 'none'],
            'alpha_dash' => [$text, 'none'],
            'ascii' => [$text, 'none'],
            'lowercase' => [$text, 'none'],
            'uppercase' => [$text, 'none'],
            'starts_with' => [$text, 'list'],
            'ends_with' => [$text, 'list'],
            'doesnt_start_with' => [$text, 'list'],
            'doesnt_end_with' => [$text, 'list'],
            'in' => [[self::CATEGORY_TEXT, self::CATEGORY_NUMBER], 'list'],
            'not_in' => [[self::CATEGORY_TEXT, self::CATEGORY_NUMBER, self::CATEGORY_CHOICE], 'list'],
            'email' => [$text, 'none'],
            'url' => [$text, 'none'],
            'uuid' => [$text, 'none'],
            'ip' => [$text, 'none'],
            'json' => [$text, 'none'],
            'integer' => [$number, 'none'],
            'decimal' => [$number, 'text'],
            'digits' => [$number, 'number'],
            'digits_between' => [$number, 'text'],
            'multiple_of' => [$number, 'number'],
            'gt' => [$number, 'number'],
            'lt' => [$number, 'number'],
            'after' => [$date, 'date'],
            'after_or_equal' => [$date, 'date'],
            'before' => [$date, 'date'],
            'before_or_equal' => [$date, 'date'],
            'date_format' => [$date, 'text'],
            'distinct' => [$multiple, 'none'],
            'mimes' => [$file, 'list'],
            'extensions' => [$file, 'list'],
            'max_size' => [$file, 'number'],
            'min_size' => [$file, 'number'],
            'dimensions' => [$file, 'text'],
            'accepted' => [[self::CATEGORY_BOOLEAN], 'none'],
            'declined' => [[self::CATEGORY_BOOLEAN], 'none'],
        ];
    }

    /**
     * The rule ids that apply to a category.
     *
     * @return array<int, string>
     */
    public static function forCategory(?string $category): array
    {
        if ($category === null) {
            return [];
        }

        return array_keys(array_filter(self::catalog(), fn (array $entry): bool => in_array($category, $entry[0], true)));
    }

    /**
     * rule id => label, for a select.
     *
     * @return array<string, string>
     */
    public static function options(?string $category): array
    {
        $options = [];

        foreach (self::forCategory($category) as $id) {
            $options[$id] = self::label($id);
        }

        return $options;
    }

    public static function label(string $id): string
    {
        $key = 'packstub-form-builder::form-builder.rules.'.$id;
        $label = __($key);

        return $label === $key ? str_replace('_', ' ', ucfirst($id)) : $label;
    }

    /**
     * The kind of value a rule takes: text, number, date, list or none.
     */
    public static function valueKind(string $id): string
    {
        return self::catalog()[$id][1] ?? 'text';
    }

    public static function takesValue(string $id): bool
    {
        return self::valueKind($id) !== 'none';
    }

    /**
     * Turn a stored {rule, value} pair into a Laravel rule, or null when it
     * is unknown or lacks a value.
     */
    public static function toLaravel(string $id, mixed $value): mixed
    {
        if (! array_key_exists($id, self::catalog())) {
            return null;
        }

        $kind = self::valueKind($id);
        $value = is_array($value) ? implode(',', $value) : trim((string) ($value ?? ''));

        if ($kind === 'none') {
            return $id;
        }

        if ($value === '') {
            return null;
        }

        return match ($id) {
            'in' => Rule::in(self::list($value)),
            'not_in' => Rule::notIn(self::list($value)),
            'max_size' => 'max:'.(int) $value,
            'min_size' => 'min:'.(int) $value,
            'mimes', 'extensions', 'starts_with', 'ends_with', 'doesnt_start_with', 'doesnt_end_with' => $id.':'.implode(',', self::list($value)),
            default => $id.':'.$value,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function list(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), fn (string $item): bool => $item !== ''));
    }

    /**
     * The Laravel rule name a stored rule id validates as (for custom messages).
     */
    public static function laravelName(string $id): string
    {
        return match ($id) {
            'max_size' => 'max',
            'min_size' => 'min',
            default => $id,
        };
    }
}
