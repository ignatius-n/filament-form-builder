<?php

namespace Packstub\FormBuilder\Uploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Packstub\FormBuilder\Models\Form;
use Packstub\FormBuilder\Models\FormSubmission;

/**
 * Where the file upload field keeps files: a directory per form on the
 * uploads disk, a random name per file with the original one kept after a
 * separator, so a download can carry it back.
 */
final class Uploads
{
    public const SEPARATOR = '__';

    public static function disk(): string
    {
        return (string) config('packstub-form-builder.uploads.disk', 'local');
    }

    public static function directory(Form $form): string
    {
        return trim((string) config('packstub-form-builder.uploads.directory', 'form-builder'), '/').'/'.$form->getKey();
    }

    /**
     * Store an uploaded file and return its path on the disk.
     */
    public static function store(UploadedFile $file, Form $form): string
    {
        $original = Str::limit(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file', 60, '');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $name = Str::ulid().self::SEPARATOR.$original.'.'.$extension;

        return (string) $file->storeAs(self::directory($form), $name, ['disk' => self::disk()]);
    }

    /**
     * Store a base64 data URI (the JSON API) and return its path.
     */
    public static function storeDataUri(string $dataUri, Form $form, ?string $filename = null): ?string
    {
        if (! preg_match('~^data:([\w/.+-]+);base64,(.+)$~s', $dataUri, $matches)) {
            return null;
        }

        $contents = base64_decode($matches[2], true);

        if ($contents === false) {
            return null;
        }

        $temp = tempnam(sys_get_temp_dir(), 'fb-upload');
        file_put_contents($temp, $contents);

        $extension = $filename !== null ? pathinfo($filename, PATHINFO_EXTENSION) : '';
        $file = new UploadedFile($temp, $filename ?: 'upload'.($extension !== '' ? '.'.$extension : ''), $matches[1], null, true);

        try {
            return self::store($file, $form);
        } finally {
            @unlink($temp);
        }
    }

    /**
     * The original file name a stored path carries.
     */
    public static function originalName(string $path): string
    {
        $basename = basename($path);
        $position = strpos($basename, self::SEPARATOR);

        return $position === false ? $basename : substr($basename, $position + strlen(self::SEPARATOR));
    }

    /**
     * The stored paths a submitted value holds (a path or a list of paths).
     *
     * @return array<int, string>
     */
    public static function files(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(array_filter(array_map(fn ($item): string => is_string($item) ? $item : '', (array) $value)));
    }

    /**
     * A signed download URL for a file of a submission.
     */
    public static function url(FormSubmission $submission, string $field, int $index = 0): ?string
    {
        if (! app('router')->has('packstub-form-builder.file')) {
            return null;
        }

        return URL::signedRoute('packstub-form-builder.file', [
            'submission' => $submission->getKey(),
            'field' => $field,
            'index' => $index,
        ]);
    }

    /**
     * Delete every file a submission holds.
     */
    public static function deleteFor(FormSubmission $submission): void
    {
        $disk = Storage::disk(self::disk());

        foreach ($submission->fields ?? [] as $key => $meta) {
            if (($meta['type'] ?? null) !== 'file') {
                continue;
            }

            foreach (self::files($submission->value($key)) as $path) {
                $disk->delete($path);
            }
        }
    }
}
