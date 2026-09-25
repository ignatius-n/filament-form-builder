<?php

namespace Packstub\FormBuilder\Filament;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Packstub\FormBuilder\Filament\Resources\FormResource;
use Packstub\FormBuilder\FormBuilder;
use Packstub\FormBuilder\FormBuilderPlugin;
use Packstub\FormBuilder\Livewire\FormBuilderForm;
use Packstub\FormBuilder\Models\Form;
use Packstub\FormBuilder\Templates\Templates;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The actions of the Forms resource: share, preview, duplicate, export,
 * import and "use a template".
 */
class FormActions
{
    /**
     * The public link (or, for a private form, a signed share link with an
     * optional expiry), the availability window and the embed snippets.
     */
    public static function share(): Action
    {
        return Action::make('share')
            ->label(__('packstub-form-builder::form-builder.share.action'))
            ->icon('heroicon-o-share')
            ->color('gray')
            ->modalHeading(fn (Form $record): string => __('packstub-form-builder::form-builder.share.heading', ['form' => $record->name]))
            ->modalSubmitActionLabel(fn (Form $record): string => $record->isPrivate()
                ? __('packstub-form-builder::form-builder.share.generate')
                : __('packstub-form-builder::form-builder.share.save'))
            ->modalCancelActionLabel(__('filament::components/modal.actions.close.label'))
            ->modalWidth('2xl')
            ->fillForm(fn (Form $record): array => [
                'opens_at' => $record->opens_at,
                'closes_at' => $record->closes_at,
                'expires_at' => null,
            ])
            ->schema(fn (Form $record): array => [
                TextEntry::make('link')
                    ->label($record->isPrivate() ? __('packstub-form-builder::form-builder.share.private_link') : __('packstub-form-builder::form-builder.share.link'))
                    ->helperText($record->isPrivate() ? __('packstub-form-builder::form-builder.share.private_link_hint') : __('packstub-form-builder::form-builder.share.link_hint'))
                    ->state(fn (Get $get): string => static::linkFor($record, $get('expires_at')) ?? '—')
                    ->copyable()
                    ->copyableState(fn (string $state): string => $state)
                    ->copyMessage(__('packstub-form-builder::form-builder.embed.copied'))
                    ->icon('heroicon-o-clipboard')
                    ->iconPosition(IconPosition::After)
                    ->tooltip(__('packstub-form-builder::form-builder.embed.copy'))
                    ->fontFamily(FontFamily::Mono)
                    ->formatStateUsing(fn (string $state): string => nl2br(e($state)))
                    ->html()
                    ->columnSpanFull(),
                DateTimePicker::make('expires_at')
                    ->label(__('packstub-form-builder::form-builder.share.expires_at'))
                    ->helperText(__('packstub-form-builder::form-builder.share.expires_at_hint'))
                    ->native()
                    ->live()
                    ->visible($record->isPrivate()),
                DateTimePicker::make('opens_at')
                    ->label(__('packstub-form-builder::form-builder.fields.opens_at'))
                    ->native(),
                DateTimePicker::make('closes_at')
                    ->label(__('packstub-form-builder::form-builder.fields.closes_at'))
                    ->native()
                    ->after('opens_at'),
                TextEntry::make('iframe')
                    ->label(__('packstub-form-builder::form-builder.embed.iframe'))
                    ->state(fn (): string => FormResource::iframeSnippet($record))
                    ->copyable()
                    ->copyableState(fn (string $state): string => $state)
                    ->copyMessage(__('packstub-form-builder::form-builder.embed.copied'))
                    ->icon('heroicon-o-clipboard')
                    ->iconPosition(IconPosition::After)
                    ->tooltip(__('packstub-form-builder::form-builder.embed.copy'))
                    ->fontFamily(FontFamily::Mono)
                    ->formatStateUsing(fn (string $state): string => nl2br(e($state)))
                    ->html()
                    ->columnSpanFull(),
            ])
            ->action(function (Form $record, array $data): void {
                $record->update([
                    'opens_at' => $data['opens_at'] ?? null,
                    'closes_at' => $data['closes_at'] ?? null,
                ]);

                $link = static::linkFor($record, $data['expires_at'] ?? null);

                Notification::make()
                    ->title($link ?? __('packstub-form-builder::form-builder.share.link'))
                    ->success()
                    ->send();
            })
            ->visible(fn (Form $record): bool => $record->pageUrl() !== null);
    }

    public static function linkFor(Form $form, mixed $expiresAt): ?string
    {
        if (! $form->isPrivate()) {
            return $form->pageUrl();
        }

        $until = null;

        if (filled($expiresAt)) {
            try {
                $until = Carbon::parse($expiresAt);
            } catch (\Throwable) {
                $until = null;
            }
        }

        return $form->shareUrl($until);
    }

    /**
     * The form as the visitor sees it, from the builder's current, unsaved
     * state: the Livewire renderer in a modal, submissions not stored.
     */
    public static function preview(): Action
    {
        return Action::make('preview')
            ->label(__('packstub-form-builder::form-builder.actions.preview'))
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalHeading(__('packstub-form-builder::form-builder.actions.preview'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('filament::components/modal.actions.close.label'))
            ->modalWidth('3xl')
            ->slideOver()
            ->schema(fn ($livewire): array => [
                Livewire::make(FormBuilderForm::class, [
                    'form' => static::previewDefinition($livewire),
                    'preview' => true,
                ])->key('form-builder-preview-'.Str::random(6)),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function previewDefinition(mixed $livewire): array
    {
        $state = data_get($livewire, 'data');

        if (! is_array($state)) {
            try {
                $state = $livewire->getSchema('form')?->getRawState() ?? [];
            } catch (\Throwable) {
                $state = [];
            }
        }

        $state = $state instanceof Arrayable ? $state->toArray() : (array) $state;
        $settings = is_array($state['settings'] ?? null) ? $state['settings'] : [];

        return [
            'name' => (string) ($state['name'] ?? 'Preview'),
            'slug' => 'preview-'.Str::slug((string) ($state['slug'] ?? $state['name'] ?? 'form')),
            'description' => $state['description'] ?? null,
            'fields' => is_array($state['fields'] ?? null) ? array_values($state['fields']) : [],
            'submit_label' => $state['submit_label'] ?? null,
            'success_message' => $state['success_message'] ?? null,
            'store_submissions' => false,
            'settings' => [...$settings, 'password' => null, 'captcha' => 'none', 'require_login' => false, 'one_per_person' => false, 'max_submissions' => null, 'honeypot' => false, 'min_seconds' => 0, 'custom_js' => null],
        ];
    }

    public static function duplicate(): Action
    {
        return Action::make('duplicate')
            ->label(__('packstub-form-builder::form-builder.actions.duplicate'))
            ->icon('heroicon-o-document-duplicate')
            ->color('gray')
            ->schema(fn (Form $record): array => [
                TextInput::make('name')
                    ->label(__('packstub-form-builder::form-builder.fields.name'))
                    ->default($record->name.' (copy)')
                    ->required()
                    ->maxLength(255),
            ])
            ->action(function (Form $record, array $data): void {
                $copy = static::createFrom([...$record->toPortable(), 'name' => $data['name'], 'is_active' => false]);

                Notification::make()->title(__('packstub-form-builder::form-builder.actions.duplicated'))->success()->send();

                redirect(FormBuilderPlugin::get()->getResource()::getUrl('edit', ['record' => $copy]));
            });
    }

    public static function export(): Action
    {
        return Action::make('exportJson')
            ->label(__('packstub-form-builder::form-builder.actions.export'))
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(fn (Form $record): StreamedResponse => response()->streamDownload(
                fn () => print json_encode(['packstub-form-builder' => 1, 'form' => $record->toPortable()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                $record->slug.'.form.json',
                ['Content-Type' => 'application/json'],
            ));
    }

    public static function import(): Action
    {
        return Action::make('importJson')
            ->label(__('packstub-form-builder::form-builder.actions.import'))
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->schema([
                FileUpload::make('file')
                    ->label(__('packstub-form-builder::form-builder.actions.import_file'))
                    ->acceptedFileTypes(['application/json', 'text/plain'])
                    ->storeFiles(false)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $file = $data['file'] ?? null;
                $contents = $file instanceof TemporaryUploadedFile ? $file->get() : (is_string($file) ? Storage::get($file) : null);
                $decoded = is_string($contents) ? json_decode($contents, true) : null;
                $definition = is_array($decoded) ? ($decoded['form'] ?? $decoded) : null;

                if (! is_array($definition) || ! isset($definition['name'])) {
                    Notification::make()->title(__('packstub-form-builder::form-builder.actions.import_invalid'))->danger()->send();

                    return;
                }

                $form = static::createFrom($definition);

                Notification::make()->title(__('packstub-form-builder::form-builder.actions.imported'))->success()->send();

                redirect(FormBuilderPlugin::get()->getResource()::getUrl('edit', ['record' => $form]));
            });
    }

    public static function useTemplate(): Action
    {
        return Action::make('useTemplate')
            ->label(__('packstub-form-builder::form-builder.templates.action'))
            ->icon('heroicon-o-sparkles')
            ->color('gray')
            ->modalHeading(__('packstub-form-builder::form-builder.templates.heading'))
            ->schema([
                Select::make('template')
                    ->label(__('packstub-form-builder::form-builder.templates.template'))
                    ->options(fn (): array => Templates::options())
                    ->searchable()
                    ->required()
                    ->live()
                    ->native(false)
                    ->afterStateUpdated(fn (?string $state, $set) => $set('name', Templates::find((string) $state)['name'] ?? null)),
                TextInput::make('name')
                    ->label(__('packstub-form-builder::form-builder.templates.name'))
                    ->required()
                    ->maxLength(255),
            ])
            ->action(function (array $data): void {
                $template = Templates::find((string) $data['template']);

                if ($template === null) {
                    return;
                }

                $form = static::createFrom([...$template['form'], 'name' => $data['name']]);

                Notification::make()->title(__('packstub-form-builder::form-builder.templates.created'))->success()->send();

                redirect(FormBuilderPlugin::get()->getResource()::getUrl('edit', ['record' => $form]));
            });
    }

    /**
     * Save a form from a portable array with a slug that is free.
     *
     * @param  array<string, mixed>  $definition
     */
    public static function createFrom(array $definition): Form
    {
        $form = Form::fromArray($definition);
        $base = Str::slug((string) ($definition['slug'] ?? $form->name)) ?: 'form';
        $slug = $base;
        $suffix = 2;
        $model = FormBuilder::formModel();

        while ($model::query()->withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        $form->slug = $slug;
        $form->save();

        return $form;
    }
}
