# Extending

## Field types

A field type is a class extending `Packstub\FormBuilder\Fields\FieldType`:

| Method | Purpose |
| --- | --- |
| `id()` | The short id stored with every field |
| `label()`, `icon()` | Shown in the block picker (label from `types.{id}` in the language file) |
| `isInput()` | `false` for layout-only types |
| `hasChoices()`, `acceptsMultiple()`, `choices(Field $field)` | Choice lists, list values, computed choices |
| `hasPlaceholder()`, `hasDefault()` | Which common settings the builder shows |
| `ruleCategory()` | Which rules the picker offers: `text`, `number`, `date`, `choice`, `multiple`, `file`, `boolean` or `null` |
| `editorSchema()` | Extra settings shown in the builder, as Filament components |
| `rules(Field $field)`, `elementRules(Field $field)` | Validation rules (required / nullable are added for you) |
| `prepare(mixed $value, Field $field)` | Shape the raw input before validation (split a string into a list, decode a base64 file) |
| `comparableValue(mixed $value, Field $field)` | The raw value as the conditions compare it |
| `normalize(mixed $value, Field $field)` | The stored value |
| `format(mixed $value, Field $field)` | The value as text (tables, emails, CSV) |
| `display(mixed $value, Field $field)` | The value in the details view; return an `HtmlString` for HTML |
| `tableColumn(Field $field)`, `tableFilter(Field $field)` | A column and a filter for the submissions table, or `null` for the defaults |
| `view()` | The Blade view of the plain renderer (receives `field`, `inputId`, `error`, `value`) |
| `formComponent(Field $field)` | The Filament component of the Livewire renderer |

Extend a built-in when it is close to what you need:

```php
use Packstub\FormBuilder\Fields\Field;
use Packstub\FormBuilder\Fields\Types\NumberField;

class ScoreField extends NumberField
{
    public static function id(): string
    {
        return 'score';
    }

    public function icon(): string
    {
        return 'heroicon-o-star';
    }

    public function rules(Field $field): array
    {
        return ['integer', 'between:1,100'];
    }
}
```

Register it in `field_types`, with `FormBuilderPlugin::make()->fieldTypes([ScoreField::class])`, or with `FormBuilder::registerFieldTypes([...])`. Hide built-ins with `withoutFieldTypes([TextField::class, 'url'])`. A type that renders a Filament component the compiled Livewire stylesheet lacks needs `frontend.livewire_theme` pointed at a fuller theme (see [Rendering](rendering.md#livewire)).

## Templates

Offer your own templates next to the built-in ones with `Templates::add($directory)`; see [Sharing and templates](sharing-and-templates.md#templates).

## Models and tables

Point `models.form` / `models.submission` / `models.webhook_delivery` to your subclasses for extra columns, scopes or relationships, and `tables.*` to other table names before migrating.

## Tenancy

With `tenancy.enabled`, forms carry the current tenant's key in `tenancy.column` (`tenant_id`): the one Filament resolves in a panel with `->tenant()`, or what the `tenancy.resolver` callable returns. Queries see the current tenant's forms only (outside a tenant, the public routes see every form) and new forms get the key on creation; `$form->tenant()` is a `BelongsTo` to `tenancy.model`. With a database per tenant ([Filament Tenancy](https://packstub.dev/docs/filament-tenancy)) leave it off: each tenant has its own tables. To limit forms or submissions per plan, gate the resource with [Filament Features](https://packstub.dev/docs/filament-features) in `authorize()`.

## Resource

`FormBuilderPlugin::make()->resource(MyFormResource::class)` swaps the resource (extend `FormResource`); `withoutResource()` skips it. `authorize(fn () => auth()->user()->isEditor())` and the `gate` config key restrict who sees it; a policy on the `Form` model works as well.

## Embedding in another package

A package that wants to offer forms as a block or a widget can:

- render with the Blade component, passing its own `return` URL and `:styles="false"` to inherit the host's CSS variables, or a portable array instead of a slug;
- read the definition with `$form->toDefinition()`, the sections with `$form->sections()` and the fields with `$form->fieldList()`;
- resolve the conditions for a set of values with `$form->visibleKeys($values)`;
- submit with `app(Submitter::class)->submit($form, $input, SubmissionContext::fromRequest($request, 'my-package'))`;
- list forms for a picker with `FormBuilder::formModel()::query()->where('is_active', true)->pluck('name', 'slug')`.
