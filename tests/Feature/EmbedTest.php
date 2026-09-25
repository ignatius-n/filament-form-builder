<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Packstub\FormBuilder\Filament\Resources\FormResource;
use Packstub\FormBuilder\Models\Form;

beforeEach(function (): void {
    Route::middleware('web')->get('/contact-us', fn () => Blade::render('<x-form-builder::form form="contact" />'));
    Route::middleware('web')->get('/prefilled', fn () => Blade::render('<x-form-builder::form form="contact" :values="[\'topic\' => \'support\']" />'));
});

it('serves the embed script with the definition URL and the stylesheet', function (): void {
    contactForm();

    $response = $this->get('/forms/contact/embed.js')->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('application/javascript')
        ->and($response->getContent())->toContain('window.PackstubFormBuilder', '"definition":"'.url('/forms/contact/definition').'"', '.fb-form{', 'data-form-builder')
        ->and($response->getContent())->not->toContain('__FB_EMBED_CONFIG__');

    $this->get('/forms/missing/embed.js')->assertNotFound();
});

it('renders the bare layout for an iframe', function (): void {
    contactForm(['description' => 'Say hello']);

    $html = $this->get('/forms/contact?embed=1')->assertOk()->getContent();

    expect($html)->toContain('form-builder:resize', 'name="email"', 'name="robots" content="noindex"')
        ->and($html)->not->toContain('<h1', 'Say hello');

    $snippet = FormResource::iframeSnippet(Form::query()->firstOrFail());

    expect($snippet)->toContain('<iframe src="'.url('/forms/contact').'?embed=1"', 'form-builder:resize', 'data-form-builder-frame="contact"');
});

it('prefills fields from the page URL and the values attribute', function (): void {
    contactForm();

    $html = $this->get('/contact-us?email=ada%40example.com&name=Ada&unknown=x&topic=sales')->assertOk()->getContent();

    expect($html)->toContain('value="ada@example.com"', 'value="Ada"', '<option value="sales" selected>');

    $html = $this->get('/prefilled?topic=sales')->assertOk()->getContent();

    // The attribute wins over the query string.
    expect($html)->toContain('<option value="support" selected>');

    // A form can refuse query prefill.
    Form::query()->update(['settings' => ['prefill' => false]]);
    $html = $this->get('/contact-us?name=Ada')->getContent();
    expect($html)->not->toContain('value="Ada"');
});

it('puts the page meta, the logo, the brand colour and the custom code on the hosted page', function (): void {
    contactForm(['settings' => [
        'page_title' => 'Talk to us',
        'page_description' => 'We answer fast.',
        'page_image' => 'https://example.com/og.png',
        'page_logo' => 'https://example.com/logo.svg',
        'brand_color' => '#ff6600',
        'custom_css' => '.fb-form .fb-submit { border-radius: 999px }</style><script>alert(1)</script>',
        'custom_js' => "form.dataset.ready = '1';</script>",
        'layout' => 'horizontal',
    ]]);

    $html = $this->get('/forms/contact')->assertOk()->getContent();

    expect($html)
        ->toContain('<title>Talk to us</title>', 'content="We answer fast."', 'property="og:image" content="https://example.com/og.png"', 'src="https://example.com/logo.svg"')
        ->toContain('style="--fb-color-primary: #ff6600"', 'fb-form--horizontal', '.fb-submit { border-radius: 999px }', "form.dataset.ready = '1';")
        ->and($html)->not->toContain('</style><script>alert(1)</script>')
        ->and(substr_count($html, '<style'))->toBe(3);
});

it('describes the protection in the definition', function (): void {
    contactForm(['settings' => ['password' => 'x']]);

    $json = $this->getJson('/forms/contact/definition')->assertOk()->json();

    expect($json['protection']['password'])->toBeTrue()
        ->and($json['protection']['unlock_url'])->toBe(url('/forms/contact/unlock'))
        ->and($json['protection']['captcha'])->toBeNull()
        ->and($json['validate_url'])->toBe(url('/forms/contact/validate'))
        ->and($json['layout'])->toBe('stacked')
        ->and($json['fields'][0]['section'])->toBe('section_1');
});
