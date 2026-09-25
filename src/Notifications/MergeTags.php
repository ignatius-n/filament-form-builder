<?php

namespace Packstub\FormBuilder\Notifications;

use Packstub\FormBuilder\Models\FormSubmission;

/**
 * The tags a subject or a message body may carry: {field_key} or
 * {{ field_key }} for any submitted value, plus {form_name},
 * {submission_number}, {submission_id}, {date} and {domain}.
 */
final class MergeTags
{
    /**
     * @return array<string, string>
     */
    public static function for(FormSubmission $submission): array
    {
        $form = $submission->form;
        $tags = [
            'form_name' => $form->name,
            'form_slug' => $form->slug,
            'submission_number' => (string) ($submission->number ?? $submission->getKey() ?? ''),
            'submission_id' => (string) ($submission->getKey() ?? ''),
            'date' => ($submission->created_at ?? now())->format((string) config('packstub-form-builder.formats.datetime', 'Y-m-d H:i')),
            'domain' => (string) parse_url((string) config('app.url'), PHP_URL_HOST),
        ];

        foreach ($submission->formatted() as $key => $row) {
            $tags[$key] = $row['value'];
        }

        return $tags;
    }

    public static function render(?string $text, FormSubmission $submission): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $tags = self::for($submission);

        return (string) preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}|\{([a-z0-9_]+)\}/i', function (array $match) use ($tags): string {
            $key = $match[1] !== '' ? $match[1] : $match[2];

            return $tags[$key] ?? $match[0];
        }, $text);
    }
}
