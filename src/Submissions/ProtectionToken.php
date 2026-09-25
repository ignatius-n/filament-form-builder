<?php

namespace Packstub\FormBuilder\Submissions;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Packstub\FormBuilder\Models\Form;

/**
 * The time-trap token: an encrypted "form id + rendered at + nonce" triple
 * that the form carries in a hidden input, so a submission posted too
 * quickly after the render (a bot) can be told apart, and a token cannot
 * be replayed for a second submission.
 */
class ProtectionToken
{
    public function make(Form $form, ?int $renderedAt = null): string
    {
        return Crypt::encryptString(json_encode([
            'f' => $form->getKey(),
            't' => $renderedAt ?? time(),
            'n' => Str::random(12),
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Seconds elapsed since the token was issued for this form, or null when
     * the token is missing, tampered with or belongs to another form.
     */
    public function age(Form $form, mixed $token): ?int
    {
        $payload = $this->payload($form, $token);

        return $payload === null ? null : max(0, time() - (int) ($payload['t'] ?? 0));
    }

    /**
     * Whether the token was already used for an accepted submission.
     */
    public function isUsed(Form $form, mixed $token): bool
    {
        $payload = $this->payload($form, $token);

        return $payload !== null && isset($payload['n']) && Cache::has($this->usedKey($payload['n']));
    }

    /**
     * Remember that the token was used, for as long as it could still pass
     * the time trap (config "spam.token_ttl" minutes).
     */
    public function markUsed(Form $form, mixed $token): void
    {
        $payload = $this->payload($form, $token);

        if ($payload === null || ! isset($payload['n'])) {
            return;
        }

        Cache::put($this->usedKey($payload['n']), true, now()->addMinutes((int) config('packstub-form-builder.spam.token_ttl', 120)));
    }

    public function field(): string
    {
        return (string) config('packstub-form-builder.spam.token_field', '_fb_token');
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function payload(Form $form, mixed $token): ?array
    {
        if (! is_string($token) || $token === '') {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($token), true, 8, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            return null;
        }

        if (! is_array($payload) || (string) ($payload['f'] ?? '') !== (string) $form->getKey()) {
            return null;
        }

        return $payload;
    }

    protected function usedKey(string $nonce): string
    {
        return 'packstub-form-builder:token:'.$nonce;
    }
}
