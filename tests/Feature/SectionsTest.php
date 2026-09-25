<?php

use Illuminate\Validation\ValidationException;
use Packstub\FormBuilder\Fields\ValidationRules;
use Packstub\FormBuilder\Models\Form;
use Packstub\FormBuilder\Submissions\Submitter;

function section(string $label, array $fields, array $data = []): array
{
    return ['type' => 'section', 'data' => ['label' => $label, 'fields' => $fields, ...$data]];
}

function wizardForm(array $settings = ['mode' => 'wizard']): Form
{
    return Form::query()->create([
        'name' => 'Registration',
        'slug' => 'registration',
        'settings' => $settings,
        'fields' => [
            field('paragraph', 'Intro', ['text' => 'Welcome']),
            section('Attendee', [
                field('text', 'Name', ['required' => true]),
                field('email', 'Email', ['required' => true]),
            ], ['description' => 'Who is coming']),
            section('Ticket', [
                field('radio', 'Type', ['key' => 'type', 'choices' => ['free' => 'Free', 'paid' => 'Paid'], 'required' => true]),
                field('text', 'Voucher', ['key' => 'voucher']),
            ]),
            section('Payment', [
                field('text', 'Card holder', ['key' => 'holder', 'required' => true]),
            ], ['visibility' => 'when', 'visibility_rules' => [['field' => 'type', 'operator' => 'equals', 'value' => 'paid']]]),
            section('Hidden', [field('text', 'Ghost', ['key' => 'ghost'])], ['hidden' => true]),
        ],
    ]);
}

it('groups fields into sections with an implicit one for loose fields', function (): void {
    $form = wizardForm();
    $sections = $form->sections();

    expect($sections)->toHaveCount(4)
        ->and($sections[0]->implicit)->toBeTrue()
        ->and($sections[0]->fields->pluck('key')->all())->toBe(['intro'])
        ->and($sections[1]->key)->toBe('attendee')
        ->and($sections[1]->label)->toBe('Attendee')
        ->and($sections[1]->description)->toBe('Who is coming')
        ->and($sections[1]->fields->pluck('key')->all())->toBe(['name', 'email'])
        ->and($sections[3]->visibility->isAlways())->toBeFalse()
        ->and($form->fieldList()->pluck('key')->all())->toBe(['intro', 'name', 'email', 'type', 'voucher', 'holder'])
        ->and($form->hasSections())->toBeTrue()
        ->and($form->isWizard())->toBeTrue()
        ->and(tap($form)->update(['settings' => ['mode' => 'single']])->isWizard())->toBeFalse()
        ->and($form->field('name')->section)->toBe('attendee');
});

it('keeps keys unique across sections and drops section blocks nested in sections', function (): void {
    $form = Form::query()->create([
        'name' => 'Keys',
        'fields' => [
            field('text', 'Name'),
            section('One', [field('text', 'Name'), section('Nested', [field('text', 'Deep')])]),
            section('One', [field('text', 'Name', ['key' => 'name'])]),
        ],
    ]);

    expect($form->allInputFields()->keys()->all())->toBe(['name', 'name_2', 'name_3'])
        ->and(collect($form->fields)->pluck('data.key')->all())->toBe(['name', 'one', 'one_2'])
        ->and(collect($form->fields[1]['data']['fields'])->pluck('type')->all())->toBe(['text']);
});

it('hides the fields of a hidden section during validation', function (): void {
    $form = wizardForm();
    $submitter = app(Submitter::class);

    $data = $submitter->validate($form, ['name' => 'Ada', 'email' => 'ada@example.com', 'type' => 'free', 'holder' => 'ignored']);

    expect($data)->toHaveKey('holder')->and($data['holder'])->toBeNull()->and($data)->not->toHaveKey('ghost');

    $errors = null;

    try {
        $submitter->validate($form, ['name' => 'Ada', 'email' => 'ada@example.com', 'type' => 'paid']);
    } catch (ValidationException $e) {
        $errors = $e->errors();
    }

    expect($errors)->toHaveKey('holder');
});

it('validates one step at a time through the validate endpoint', function (): void {
    wizardForm();

    $this->postJson('/forms/registration/validate', ['_fb_step' => 1, 'name' => 'Ada'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email'])
        ->assertJsonMissingValidationErrors(['type', 'holder']);

    $this->postJson('/forms/registration/validate', ['_fb_fields' => ['name', 'email'], 'name' => 'Ada', 'email' => 'ada@example.com'])
        ->assertOk()
        ->assertJson(['ok' => true]);

    $this->postJson('/forms/registration/validate', ['_fb_step' => 2, 'type' => 'paid'])
        ->assertOk();
});

it('renders the sections, the steps and the logic for the script', function (): void {
    wizardForm();

    $html = $this->get('/forms/registration')->assertOk()->getContent();

    expect($html)
        ->toContain('fb-form--wizard', 'data-fb-section="attendee"', 'data-fb-step="1"', '<h3 class="fb-section__title">', 'fb-section__number', 'Who is coming')
        ->toContain('data-fb-progress', 'data-fb-previous', 'data-fb-next', '>Next</button>', '>Back</button>')
        ->toContain('<script type="application/json" data-fb-logic>')
        ->toContain('"wizard":{"progress":true,"numbers":true,"navigation":true}')
        ->toContain('"sections":[{"key":"section_1"', '"key":"payment","visibility":{"mode":"when"');
});

it('turns picked rules into Laravel rules with custom messages', function (): void {
    $form = Form::query()->create([
        'name' => 'Rules',
        'slug' => 'rules',
        'fields' => [
            field('text', 'Code', ['key' => 'code', 'required' => true, 'message' => 'Give us a proper code.', 'validation' => [
                ['rule' => 'min', 'value' => '3'],
                ['rule' => 'starts_with', 'value' => 'AB, CD'],
                ['rule' => 'uppercase'],
                ['rule' => 'bogus', 'value' => 'x'],
                ['rule' => 'in', 'value' => ''],
            ]]),
            field('number', 'Age', ['key' => 'age', 'validation' => [['rule' => 'integer'], ['rule' => 'gt', 'value' => '17']]]),
            field('file', 'Doc', ['key' => 'doc', 'validation' => [['rule' => 'max_size', 'value' => '512']]]),
        ],
    ]);

    $code = $form->field('code');

    expect($code->rules())->toBe(['required', 'string', 'max:255', 'min:3', 'starts_with:AB,CD', 'uppercase'])
        ->and($code->messages())->toHaveKey('code.min')
        ->and($code->messages()['code.required'])->toBe('Give us a proper code.')
        ->and($form->field('age')->rules())->toBe(['nullable', 'numeric', 'integer', 'gt:17'])
        ->and($form->field('doc')->rules())->toContain('max:512')
        ->and(ValidationRules::forCategory('date'))->toContain('after', 'before_or_equal')
        ->and(ValidationRules::forCategory('date'))->not->toContain('regex')
        ->and(ValidationRules::takesValue('alpha'))->toBeFalse()
        ->and(ValidationRules::valueKind('mimes'))->toBe('list')
        ->and((string) ValidationRules::toLaravel('in', 'a, b'))->toBe('in:"a","b"');

    $errors = null;

    try {
        app(Submitter::class)->validate($form, ['code' => 'ab', 'age' => '10']);
    } catch (ValidationException $e) {
        $errors = $e->errors();
    }

    expect($errors['code'][0])->toBe('Give us a proper code.')
        ->and($errors['age'][0])->toContain('greater than 17');
});
