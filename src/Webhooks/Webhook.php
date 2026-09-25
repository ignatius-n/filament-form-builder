<?php

namespace Packstub\FormBuilder\Webhooks;

use Illuminate\Support\Str;
use Packstub\FormBuilder\Models\Form;
use Packstub\FormBuilder\Models\FormSubmission;

/**
 * A form's webhook settings (Settings › Webhook) and the payload it posts.
 */
final class Webhook
{
    /**
     * @param  array<int, string>  $fields
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly string $url,
        public readonly string $method,
        public readonly string $secret,
        public readonly array $fields,
        public readonly bool $metadata,
        public readonly array $headers,
    ) {}

    public static function for(Form $form): ?self
    {
        $url = $form->setting('webhook_url');

        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $headers = [];

        foreach ((array) $form->setting('webhook_headers', []) as $name => $value) {
            if (is_array($value)) {
                $name = $value['name'] ?? $value['key'] ?? null;
                $value = $value['value'] ?? null;
            }

            if (is_string($name) && trim($name) !== '' && is_scalar($value)) {
                $headers[trim($name)] = (string) $value;
            }
        }

        return new self(
            url: $url,
            method: in_array(strtoupper((string) $form->setting('webhook_method', 'POST')), ['POST', 'PUT', 'PATCH'], true) ? strtoupper((string) $form->setting('webhook_method', 'POST')) : 'POST',
            secret: (string) $form->setting('webhook_secret', ''),
            fields: array_values(array_filter((array) $form->setting('webhook_fields', []))),
            metadata: (bool) $form->setting('webhook_metadata', true),
            headers: $headers,
        );
    }

    /**
     * A new signing secret, in the Standard Webhooks format.
     */
    public static function generateSecret(): string
    {
        return 'whsec_'.base64_encode(random_bytes(24));
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(FormSubmission $submission, string $event = 'submission.received'): array
    {
        $payload = $submission->toPayload($this->metadata);

        if ($this->fields !== []) {
            $payload['data'] = array_intersect_key($payload['data'], array_flip($this->fields));
            $payload['labels'] = array_intersect_key($payload['labels'], array_flip($this->fields));
        }

        if (! $this->metadata) {
            unset($payload['meta']);
        }

        return ['type' => $event, 'timestamp' => now()->toIso8601String(), 'data' => $payload];
    }

    /**
     * The Standard Webhooks headers: webhook-id, webhook-timestamp and
     * webhook-signature ("v1,<base64 hmac>" of "{id}.{timestamp}.{body}").
     *
     * @return array<string, string>
     */
    public function signatureHeaders(string $id, int $timestamp, string $body): array
    {
        $headers = ['webhook-id' => $id, 'webhook-timestamp' => (string) $timestamp];

        if ($this->secret !== '') {
            $key = Str::startsWith($this->secret, 'whsec_') ? base64_decode(substr($this->secret, 6), true) ?: $this->secret : $this->secret;
            $headers['webhook-signature'] = 'v1,'.base64_encode(hash_hmac('sha256', "{$id}.{$timestamp}.{$body}", $key, true));
        }

        return $headers;
    }
}
