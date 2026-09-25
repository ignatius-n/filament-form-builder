<?php

namespace Packstub\FormBuilder\Filament;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Packstub\FormBuilder\Fields\Conditions;
use Packstub\FormBuilder\Fields\FieldType;
use Packstub\FormBuilder\Fields\FieldTypeRegistry;
use Packstub\FormBuilder\Fields\ValidationRules;
use Packstub\FormBuilder\Models\Form;

/**
 * The Builder field that edits a form's fields: one block per field type,
 * each with the common settings followed by the type's own, plus a section
 * block that groups fields (a card, or a step of a multi-step form).
 */
class FieldBlocks
{
    public static function make(string $name = 'fields', bool $withSections = true): Builder
    {
        $registry = app(FieldTypeRegistry::class);
        $blocks = $registry->all()->map(fn (FieldType $type): Block => static::block($type))->values()->all();

        if ($withSections) {
            array_unshift($blocks, static::sectionBlock());
        }

        return Builder::make($name)
            ->label(__('packstub-form-builder::form-builder.fields.fields'))
            ->hiddenLabel()
            ->blocks($blocks)
            ->addActionLabel(__('packstub-form-builder::form-builder.editor.add_field'))
            ->blockNumbers(false)
            ->blockIcons()
            ->blockPickerColumns(2)
            ->collapsible()
            ->collapsed()
            ->cloneable()
            ->reorderableWithButtons()
            ->rule(static::uniqueKeysRule())
            ->columnSpanFull();
    }

    public static function sectionBlock(): Block
    {
        return Block::make(Form::SECTION_TYPE)
            ->label(fn (?array $state): string => filled($state['label'] ?? null)
                ? $state['label'].' · '.__('packstub-form-builder::form-builder.editor.section')
                : __('packstub-form-builder::form-builder.editor.section'))
            ->icon('heroicon-o-rectangle-stack')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('label')
                        ->label(__('packstub-form-builder::form-builder.editor.section_title'))
                        ->maxLength(255)
                        ->live(onBlur: true),
                    TextInput::make('key')
                        ->label(__('packstub-form-builder::form-builder.editor.key'))
                        ->helperText(__('packstub-form-builder::form-builder.editor.section_key_hint'))
                        ->maxLength(64)
                        ->regex('/^[a-z0-9_]*$/')
                        ->dehydrateStateUsing(fn (?string $state): string => Str::slug((string) $state, '_')),
                    Textarea::make('description')
                        ->label(__('packstub-form-builder::form-builder.editor.section_description'))
                        ->rows(2)
                        ->columnSpanFull(),
                    Toggle::make('hidden')
                        ->label(__('packstub-form-builder::form-builder.editor.hidden'))
                        ->helperText(__('packstub-form-builder::form-builder.editor.section_hidden_hint'))
                        ->inline(false),
                ]),
                static::logicSection(static::visibilitySchema(), isInput: false),
                static::make('fields', withSections: false)
                    ->addActionLabel(__('packstub-form-builder::form-builder.editor.add_field'))
                    ->rule(null),
            ]);
    }

    public static function block(FieldType $type): Block
    {
        return Block::make($type::id())
            ->label(fn (?array $state): string => filled($state['label'] ?? null)
                ? $state['label'].' · '.$type->label().(($state['hidden'] ?? false) ? ' · '.__('packstub-form-builder::form-builder.editor.hidden') : '')
                : $type->label())
            ->icon($type->icon())
            ->schema([
                Grid::make(2)->schema([
                    ...static::commonSchema($type),
                    ...$type->editorSchema(),
                ]),
                static::logicSection(static::logicSchema($type), $type->isInput()),
                ...static::validationSection($type),
            ]);
    }

    /**
     * The conditions of a field or section in a compact section that opens
     * when a condition is set, with a summary in its header.
     *
     * @param  array<int, Component>  $schema
     */
    protected static function logicSection(array $schema, bool $isInput): Section
    {
        return Section::make(__('packstub-form-builder::form-builder.editor.logic_section'))
            ->description(fn (Get $get): string => static::logicSummary($get, $isInput))
            ->schema([Grid::make(2)->schema($schema)])
            ->compact()
            ->collapsible()
            ->collapsed(fn (Get $get): bool => ! static::hasLogic($get, $isInput));
    }

    /**
     * The validation rules of a field in a compact section that opens when
     * a rule is set, with a summary in its header.
     *
     * @return array<int, Component>
     */
    protected static function validationSection(FieldType $type): array
    {
        $schema = static::validationSchema($type);

        if ($schema === []) {
            return [];
        }

        return [
            Section::make(__('packstub-form-builder::form-builder.editor.validation'))
                ->description(fn (Get $get): string => static::validationSummary($get))
                ->schema([Grid::make(2)->schema($schema)])
                ->compact()
                ->collapsible()
                ->collapsed(fn (Get $get): bool => static::countRules($get) === 0 && blank($get('message'))),
        ];
    }

    protected static function conditional(mixed $mode): bool
    {
        return in_array($mode, [Conditions::MODE_WHEN, Conditions::MODE_UNLESS], true);
    }

    protected static function hasLogic(Get $get, bool $isInput): bool
    {
        return static::conditional($get('visibility'))
            || ($isInput && (bool) $get('required') && static::conditional($get('requirement')));
    }

    protected static function logicSummary(Get $get, bool $isInput): string
    {
        $parts = [];

        if (static::conditional($get('visibility'))) {
            $parts[] = trans_choice(
                'packstub-form-builder::form-builder.editor.'.($get('visibility') === Conditions::MODE_WHEN ? 'logic_shown' : 'logic_hidden'),
                count((array) $get('visibility_rules')),
            );
        }

        if ($isInput && (bool) $get('required') && static::conditional($get('requirement'))) {
            $parts[] = trans_choice(
                'packstub-form-builder::form-builder.editor.'.($get('requirement') === Conditions::MODE_WHEN ? 'logic_required' : 'logic_required_unless'),
                count((array) $get('requirement_rules')),
            );
        }

        return $parts === [] ? __('packstub-form-builder::form-builder.editor.logic_none') : implode(' · ', $parts);
    }

    protected static function countRules(Get $get): int
    {
        return count((array) $get('validation')) + count((array) $get('rules'));
    }

    protected static function validationSummary(Get $get): string
    {
        $count = static::countRules($get);
        $summary = $count === 0
            ? __('packstub-form-builder::form-builder.editor.validation_none')
            : trans_choice('packstub-form-builder::form-builder.editor.validation_summary', $count);

        return filled($get('message'))
            ? $summary.' · '.__('packstub-form-builder::form-builder.editor.validation_message')
            : $summary;
    }

    /**
     * @return array<int, Component>
     */
    protected static function commonSchema(FieldType $type): array
    {
        $schema = [
            TextInput::make('label')
                ->label(__('packstub-form-builder::form-builder.editor.label'))
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                    if (blank($get('key'))) {
                        $set('key', Str::slug((string) $state, '_'));
                    }
                }),
        ];

        if ($type->isInput()) {
            $schema[] = TextInput::make('key')
                ->label(__('packstub-form-builder::form-builder.editor.key'))
                ->helperText(__('packstub-form-builder::form-builder.editor.key_hint'))
                ->maxLength(64)
                ->regex('/^[a-z0-9_]*$/')
                ->dehydrateStateUsing(fn (?string $state): string => Str::slug((string) $state, '_'));
        }

        if ($type->hasCommonSettings()) {
            if ($type->hasPlaceholder()) {
                $schema[] = TextInput::make('placeholder')
                    ->label(__('packstub-form-builder::form-builder.editor.placeholder'))
                    ->maxLength(255);
            }

            $schema[] = TextInput::make('hint')
                ->label(__('packstub-form-builder::form-builder.editor.hint'))
                ->maxLength(255);

            if ($type->hasDefault()) {
                $schema[] = TextInput::make('default')
                    ->label(__('packstub-form-builder::form-builder.editor.default'))
                    ->maxLength(255);
            }

            $schema[] = Select::make('width')
                ->label(__('packstub-form-builder::form-builder.editor.width'))
                ->options(static::widthOptions())
                ->default('full')
                ->formatStateUsing(fn (?string $state): string => $state ?: 'full')
                ->native(false);
        }

        if ($type->isInput()) {
            $schema[] = Toggle::make('required')
                ->label(__('packstub-form-builder::form-builder.editor.required'))
                ->live()
                ->inline(false);
        }

        $schema[] = Toggle::make('hidden')
            ->label(__('packstub-form-builder::form-builder.editor.hidden'))
            ->helperText(__('packstub-form-builder::form-builder.editor.hidden_hint'))
            ->live()
            ->inline(false);

        return $schema;
    }

    /**
     * @return array<string, string>
     */
    public static function widthOptions(): array
    {
        return [
            'full' => __('packstub-form-builder::form-builder.editor.width_full'),
            'three-quarters' => __('packstub-form-builder::form-builder.editor.width_three_quarters'),
            'two-thirds' => __('packstub-form-builder::form-builder.editor.width_two_thirds'),
            'half' => __('packstub-form-builder::form-builder.editor.width_half'),
            'third' => __('packstub-form-builder::form-builder.editor.width_third'),
            'quarter' => __('packstub-form-builder::form-builder.editor.width_quarter'),
        ];
    }

    /**
     * The visibility and requirement conditions of a field.
     *
     * @return array<int, Component>
     */
    protected static function logicSchema(FieldType $type): array
    {
        $visibility = static::visibilitySchema();

        if (! $type->isInput()) {
            return $visibility;
        }

        $requirement = static::conditionsSchema('requirement', __('packstub-form-builder::form-builder.editor.requirement'), [
            Conditions::MODE_ALWAYS => __('packstub-form-builder::form-builder.editor.requirement_always'),
            Conditions::MODE_WHEN => __('packstub-form-builder::form-builder.editor.requirement_when'),
            Conditions::MODE_UNLESS => __('packstub-form-builder::form-builder.editor.requirement_unless'),
        ]);

        [$mode, $logic, $rules] = $requirement;
        $required = fn (Get $get): bool => (bool) $get('required');
        $active = fn (Get $get): bool => (bool) $get('required') && static::conditional($get('requirement'));

        $mode->visible($required);
        $logic->visible($active);
        $rules->visible($active);

        return [...$visibility, $mode, $logic, $rules];
    }

    /**
     * @return array<int, Component>
     */
    protected static function visibilitySchema(): array
    {
        return static::conditionsSchema('visibility', __('packstub-form-builder::form-builder.editor.visibility'), [
            Conditions::MODE_ALWAYS => __('packstub-form-builder::form-builder.editor.visibility_always'),
            Conditions::MODE_WHEN => __('packstub-form-builder::form-builder.editor.visibility_when'),
            Conditions::MODE_UNLESS => __('packstub-form-builder::form-builder.editor.visibility_unless'),
        ]);
    }

    /**
     * A conditions group: mode, logic and the rules on other fields.
     *
     * @param  array<string, string>  $modes
     * @return array<int, Component>
     */
    public static function conditionsSchema(string $prefix, string $label, array $modes): array
    {
        $active = fn (Get $get): bool => in_array($get($prefix), [Conditions::MODE_WHEN, Conditions::MODE_UNLESS], true);

        return [
            Select::make($prefix)
                ->label($label)
                ->options($modes)
                ->default(Conditions::MODE_ALWAYS)
                ->formatStateUsing(fn (?string $state): string => $state ?: Conditions::MODE_ALWAYS)
                ->selectablePlaceholder(false)
                ->native(false)
                ->live(),
            Select::make($prefix.'_logic')
                ->label(__('packstub-form-builder::form-builder.editor.logic'))
                ->options([
                    Conditions::LOGIC_ALL => __('packstub-form-builder::form-builder.editor.logic_all'),
                    Conditions::LOGIC_ANY => __('packstub-form-builder::form-builder.editor.logic_any'),
                ])
                ->default(Conditions::LOGIC_ALL)
                ->formatStateUsing(fn (?string $state): string => $state ?: Conditions::LOGIC_ALL)
                ->selectablePlaceholder(false)
                ->native(false)
                ->visible($active),
            Repeater::make($prefix.'_rules')
                ->label(__('packstub-form-builder::form-builder.editor.conditions'))
                ->addActionLabel(__('packstub-form-builder::form-builder.editor.add_condition'))
                ->schema([
                    Select::make('field')
                        ->label(__('packstub-form-builder::form-builder.editor.condition_field'))
                        ->options(fn (Get $get, Component $component): array => static::otherFieldOptions($get, $component))
                        ->searchable()
                        ->required()
                        ->native(false),
                    Select::make('operator')
                        ->label(__('packstub-form-builder::form-builder.editor.condition_operator'))
                        ->options(collect(Conditions::OPERATORS)->mapWithKeys(fn (string $operator): array => [$operator => __('packstub-form-builder::form-builder.operators.'.$operator)])->all())
                        ->default('equals')
                        ->selectablePlaceholder(false)
                        ->required()
                        ->native(false)
                        ->live(),
                    TextInput::make('value')
                        ->label(__('packstub-form-builder::form-builder.editor.condition_value'))
                        ->maxLength(255)
                        ->hidden(fn (Get $get): bool => in_array($get('operator'), ['is_empty', 'is_not_empty'], true)),
                ])
                ->columns(3)
                ->compact()
                ->defaultItems(1)
                ->reorderable(false)
                ->columnSpanFull()
                ->visible($active),
        ];
    }

    /**
     * The rule picker, the custom message and the free-text rules.
     *
     * @return array<int, Component>
     */
    protected static function validationSchema(FieldType $type): array
    {
        if (! $type->isInput() || $type::id() === 'hidden') {
            return [];
        }

        $category = $type->ruleCategory();
        $schema = [];

        if ($category !== null && ValidationRules::forCategory($category) !== []) {
            $schema[] = Repeater::make('validation')
                ->label(__('packstub-form-builder::form-builder.editor.validation'))
                ->addActionLabel(__('packstub-form-builder::form-builder.editor.add_rule'))
                ->schema([
                    Select::make('rule')
                        ->label(__('packstub-form-builder::form-builder.editor.rule'))
                        ->options(ValidationRules::options($category))
                        ->required()
                        ->native(false)
                        ->live(),
                    TextInput::make('value')
                        ->label(__('packstub-form-builder::form-builder.editor.rule_value'))
                        ->placeholder(fn (Get $get): ?string => match (ValidationRules::valueKind((string) $get('rule'))) {
                            'list' => 'a, b, c',
                            'date' => '2026-12-31',
                            'number' => '10',
                            default => null,
                        })
                        ->maxLength(255)
                        ->required(fn (Get $get): bool => ValidationRules::takesValue((string) $get('rule')))
                        ->hidden(fn (Get $get): bool => ! ValidationRules::takesValue((string) $get('rule'))),
                ])
                ->columns(2)
                ->compact()
                ->defaultItems(0)
                ->reorderable(false)
                ->columnSpanFull();
        }

        $schema[] = TextInput::make('message')
            ->label(__('packstub-form-builder::form-builder.editor.message'))
            ->helperText(__('packstub-form-builder::form-builder.editor.message_hint'))
            ->maxLength(255);

        $schema[] = TagsInput::make('rules')
            ->label(__('packstub-form-builder::form-builder.editor.rules'))
            ->helperText(__('packstub-form-builder::form-builder.editor.rules_hint'))
            ->placeholder('max:100')
            ->default([]);

        return $schema;
    }

    /**
     * key => label of every other input field in the form, for the
     * conditions' field select. Reads the whole builder state, so fields
     * added in the same editing session are offered too.
     *
     * @return array<string, string>
     */
    public static function otherFieldOptions(Get $get, Component $component): array
    {
        $registry = app(FieldTypeRegistry::class);
        $ownKey = static::keyOf((array) [
            'key' => $get('../../key'),
            'label' => $get('../../label'),
        ]);
        $root = $component->getRootContainer()->getStatePath();
        $items = $get(($root !== null && $root !== '' ? $root.'.' : '').'fields', isAbsolute: true);
        $options = [];

        foreach (static::flatten(is_array($items) ? $items : []) as $item) {
            $type = $registry->find((string) ($item['type'] ?? ''));
            $data = is_array($item['data'] ?? null) ? $item['data'] : [];

            if ($type === null || ! $type->isComparable()) {
                continue;
            }

            $key = static::keyOf($data);

            if ($key === '' || $key === $ownKey) {
                continue;
            }

            $options[$key] = filled($data['label'] ?? null) ? (string) $data['label'].' ('.$key.')' : $key;
        }

        return $options;
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    protected static function flatten(array $items): array
    {
        $flat = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            if (($item['type'] ?? null) === Form::SECTION_TYPE) {
                $flat = [...$flat, ...static::flatten((array) data_get($item, 'data.fields', []))];

                continue;
            }

            $flat[] = $item;
        }

        return $flat;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected static function keyOf(array $data): string
    {
        return Str::slug((string) ($data['key'] ?? ''), '_') ?: Str::slug((string) ($data['label'] ?? ''), '_');
    }

    protected static function uniqueKeysRule(): \Closure
    {
        return fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
            $registry = app(FieldTypeRegistry::class);
            $seen = [];

            foreach (static::flatten((array) $value) as $item) {
                $type = $registry->find((string) ($item['type'] ?? ''));

                if ($type === null || ! $type->isInput()) {
                    continue;
                }

                $key = static::keyOf((array) ($item['data'] ?? []));

                if ($key !== '' && isset($seen[$key])) {
                    $fail(__('packstub-form-builder::form-builder.editor.duplicate_key', ['key' => $key]));

                    return;
                }

                $seen[$key] = true;
            }
        };
    }
}
