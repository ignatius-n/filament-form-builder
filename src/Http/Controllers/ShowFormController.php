<?php

namespace Packstub\FormBuilder\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Packstub\FormBuilder\FormBuilder;

/**
 * The hosted page: the form on its own, in the configured layout. A private
 * form opens only through a signed share link; "?embed=1" renders the bare
 * layout for an iframe.
 */
class ShowFormController
{
    public function __invoke(Request $request, string $form): View
    {
        $form = FormBuilder::formModel()::query()->where('slug', $form)->firstOrFail();

        if ($form->isPrivate() && ! $request->hasValidSignature(false)) {
            abort(403, __('packstub-form-builder::form-builder.frontend.private'));
        }

        $embed = $request->boolean('embed');

        return view('packstub-form-builder::pages.show', [
            'form' => $form,
            'embed' => $embed,
            'layout' => $embed ? 'packstub-form-builder::embed-layout' : config('packstub-form-builder.routes.page_layout', 'packstub-form-builder::layout'),
        ]);
    }
}
