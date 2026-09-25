<?php

namespace Packstub\FormBuilder\Fields;

/**
 * A group of conditions on other fields' values, used for a field's or a
 * section's visibility ("show when", "hide when") and for a field's
 * requirement ("required only when", "required except when").
 *
 * Stored in the builder data as three keys under a prefix: "{prefix}"
 * (the mode: always / when / unless), "{prefix}_logic" (all / any) and
 * "{prefix}_rules" (a list of {field, operator, value}).
 */
final class Conditions
{
    public const MODE_ALWAYS = 'always';

    public const MODE_WHEN = 'when';

    public const MODE_UNLESS = 'unless';

    public const LOGIC_ALL = 'all';

    public const LOGIC_ANY = 'any';

    public const OPERATORS = [
        'equals',
        'not_equals',
        'contains',
        'not_contains',
        'greater_than',
        'less_than',
        'is_empty',
        'is_not_empty',
    ];

    /**
     * @param  array<int, array{field: string, operator: string, value: mixed}>  $rules
     */
    public function __construct(
        public readonly string $mode = self::MODE_ALWAYS,
        public readonly string $logic = self::LOGIC_ALL,
        public readonly array $rules = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data  The builder data of a field or section.
     */
    public static function fromData(array $data, string $prefix): self
    {
        $mode = in_array($data[$prefix] ?? null, [self::MODE_WHEN, self::MODE_UNLESS], true) ? $data[$prefix] : self::MODE_ALWAYS;
        $logic = ($data[$prefix.'_logic'] ?? null) === self::LOGIC_ANY ? self::LOGIC_ANY : self::LOGIC_ALL;
        $rules = [];

        foreach (is_array($data[$prefix.'_rules'] ?? null) ? $data[$prefix.'_rules'] : [] as $rule) {
            if (! is_array($rule)) {
                continue;
            }

            $field = trim((string) ($rule['field'] ?? ''));
            $operator = (string) ($rule['operator'] ?? 'equals');

            if ($field === '' || ! in_array($operator, self::OPERATORS, true)) {
                continue;
            }

            $rules[] = ['field' => $field, 'operator' => $operator, 'value' => $rule['value'] ?? null];
        }

        return new self($mode, $logic, $rules);
    }

    public static function always(): self
    {
        return new self;
    }

    /**
     * Whether the group is a no-op (always on).
     */
    public function isAlways(): bool
    {
        return $this->mode === self::MODE_ALWAYS || $this->rules === [];
    }

    /**
     * The keys of the fields the rules look at.
     *
     * @return array<int, string>
     */
    public function fieldKeys(): array
    {
        return array_values(array_unique(array_column($this->rules, 'field')));
    }

    /**
     * Whether the rules match the given values (ignoring the mode).
     *
     * @param  array<string, mixed>  $values
     */
    public function matches(array $values): bool
    {
        if ($this->rules === []) {
            return true;
        }

        foreach ($this->rules as $rule) {
            $result = self::compare($rule['operator'], $values[$rule['field']] ?? null, $rule['value']);

            if ($this->logic === self::LOGIC_ANY && $result) {
                return true;
            }

            if ($this->logic === self::LOGIC_ALL && ! $result) {
                return false;
            }
        }

        return $this->logic === self::LOGIC_ALL;
    }

    /**
     * Whether the group is on for the given values: always, or the rules
     * match ("when"), or the rules do not match ("unless").
     *
     * @param  array<string, mixed>  $values
     */
    public function passes(array $values): bool
    {
        return match ($this->mode) {
            self::MODE_WHEN => $this->rules === [] || $this->matches($values),
            self::MODE_UNLESS => $this->rules === [] || ! $this->matches($values),
            default => true,
        };
    }

    public static function compare(string $operator, mixed $actual, mixed $expected): bool
    {
        $empty = $actual === null || $actual === '' || $actual === [] || $actual === false;

        return match ($operator) {
            'is_empty' => $empty,
            'is_not_empty' => ! $empty,
            'equals' => self::equals($actual, $expected),
            'not_equals' => ! self::equals($actual, $expected),
            'contains' => self::contains($actual, $expected),
            'not_contains' => ! self::contains($actual, $expected),
            'greater_than' => is_numeric($actual) && is_numeric($expected) && (float) $actual > (float) $expected,
            'less_than' => is_numeric($actual) && is_numeric($expected) && (float) $actual < (float) $expected,
            default => false,
        };
    }

    private static function equals(mixed $actual, mixed $expected): bool
    {
        if (is_array($actual)) {
            $actual = count($actual) === 1 ? reset($actual) : $actual;
        }

        if (is_array($actual)) {
            return false;
        }

        if (is_bool($actual)) {
            return $actual === filter_var($expected, FILTER_VALIDATE_BOOLEAN);
        }

        if (is_numeric($actual) && is_numeric($expected)) {
            return (float) $actual === (float) $expected;
        }

        return strcasecmp(trim((string) $actual), trim((string) $expected)) === 0;
    }

    private static function contains(mixed $actual, mixed $expected): bool
    {
        if (is_array($actual)) {
            foreach ($actual as $item) {
                if (self::equals($item, $expected)) {
                    return true;
                }
            }

            return false;
        }

        if ($actual === null || is_bool($actual) || $expected === null || $expected === '') {
            return false;
        }

        return mb_stripos((string) $actual, (string) $expected) !== false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
            'logic' => $this->logic,
            'rules' => $this->rules,
        ];
    }
}
