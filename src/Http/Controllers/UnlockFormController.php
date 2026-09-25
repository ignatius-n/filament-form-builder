<?php

namespace Packstub\FormBuilder\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Packstub\FormBuilder\FormBuilder;
use Packstub\FormBuilder\Submissions\PasswordGate;

/**
 * Take the password of a protected form. Answers JSON clients with the key
 * to send as "_fb_key"; browsers go back to the page with the key in the
 * session (or in the "fb_key" query parameter on session-less sites).
 */
class UnlockFormController
{
    public function __invoke(Request $request, string $form, PasswordGate $gate): JsonResponse|RedirectResponse
    {
        $form = FormBuilder::formModel()::query()->where('slug', $form)->firstOrFail();
        $matches = $gate->matches($form, $request->input('password'));
        $key = $matches ? $gate->unlock($form, $request) : null;

        if ($request->expectsJson()) {
            return $matches
                ? response()->json(['ok' => true, 'key' => $key, 'key_field' => PasswordGate::FIELD])
                : response()->json(['ok' => false, 'message' => __('packstub-form-builder::form-builder.frontend.password_wrong')], 422);
        }

        $back = $request->input('_fb_return');
        $back = is_string($back) && $back !== '' && $this->sameHost($request, $back) ? $back : ($form->pageUrl() ?? $request->root());
        $back = preg_replace('/([?&])(fb_key|fb_locked)=[^&#]*(&|$)/', '$1', $back) ?? $back;
        $back = rtrim(rtrim($back, '&'), '?');
        $separator = str_contains($back, '?') ? '&' : '?';

        if (! $matches) {
            return redirect()->to($back.$separator.'fb_locked='.$form->slug.'#form-'.$form->slug);
        }

        return redirect()->to(($request->hasSession() ? $back : $back.$separator.'fb_key='.rawurlencode((string) $key)).'#form-'.$form->slug);
    }

    protected function sameHost(Request $request, string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return $host === null || $host === false || strcasecmp((string) $host, $request->getHost()) === 0;
    }
}
