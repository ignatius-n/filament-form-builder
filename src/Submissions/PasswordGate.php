<?php

namespace Packstub\FormBuilder\Submissions;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Packstub\FormBuilder\Models\Form;

/**
 * A form's access password. Once the visitor types it, the renderer keeps
 * an encrypted key (the form id and a hash of the password) in a hidden
 * input, the session and the "fb_key" query parameter, so the form works
 * on session-less pages and for JSON clients (POST /forms/{slug}/unlock).
 */
class PasswordGate
{
    public const FIELD = '_fb_key';

    public function matches(Form $form, mixed $password): bool
    {
        $expected = $form->password();

        return $expected !== null && is_string($password) && hash_equals($expected, $password);
    }

    public function key(Form $form): string
    {
        return Crypt::encryptString(json_encode([
            'f' => $form->getKey(),
            'p' => hash('sha256', (string) $form->password()),
        ], JSON_THROW_ON_ERROR));
    }

    public function keyIsValid(Form $form, mixed $key): bool
    {
        if ($form->password() === null) {
            return true;
        }

        if (! is_string($key) || $key === '') {
            return false;
        }

        try {
            $payload = json_decode(Crypt::decryptString($key), true, 4, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            return false;
        }

        return is_array($payload)
            && (string) ($payload['f'] ?? '') === (string) $form->getKey()
            && hash_equals(hash('sha256', (string) $form->password()), (string) ($payload['p'] ?? ''));
    }

    /**
     * Whether the request carries a valid key (input, query or session).
     */
    public function isUnlocked(Form $form, Request $request): bool
    {
        if ($form->password() === null) {
            return true;
        }

        foreach ([$request->input(self::FIELD), $request->query('fb_key'), $request->hasSession() ? $request->session()->get($this->sessionKey($form)) : null] as $candidate) {
            if ($this->keyIsValid($form, $candidate)) {
                return true;
            }
        }

        return false;
    }

    public function unlock(Form $form, Request $request): string
    {
        $key = $this->key($form);

        if ($request->hasSession()) {
            $request->session()->put($this->sessionKey($form), $key);
        }

        return $key;
    }

    public function sessionKey(Form $form): string
    {
        return 'packstub-form-builder.key.'.$form->getKey();
    }
}
