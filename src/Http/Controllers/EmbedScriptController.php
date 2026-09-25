<?php

namespace Packstub\FormBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Packstub\FormBuilder\FormBuilder;
use Packstub\FormBuilder\View\Components\Form;

/**
 * GET /forms/{slug}/embed.js: a script that renders the form into a
 * container on any site (<div data-form-builder="contact">, or the element
 * the script tag's data-target names) through the JSON API, with the
 * package stylesheet and the in-place submit, conditions and steps.
 */
class EmbedScriptController
{
    public function __invoke(Request $request, string $form): Response
    {
        $form = FormBuilder::formModel()::query()->where('slug', $form)->firstOrFail();

        $script = (string) file_get_contents(__DIR__.'/../../../resources/js/form-builder.js')
            ."\n".(string) file_get_contents(__DIR__.'/../../../resources/js/embed.js');

        $config = json_encode([
            'slug' => $form->slug,
            'definition' => $form->definitionUrl(),
            'styles' => (string) file_get_contents(__DIR__.'/../../../resources/css/form-builder.css'),
            'strings' => Form::strings(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        return response(str_replace('__FB_EMBED_CONFIG__', $config, $script), 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
