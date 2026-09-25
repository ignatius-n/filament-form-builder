<?php

use Packstub\FormBuilder\Facades\FormBuilder;
use Packstub\FormBuilder\Models\Form;

it('scopes forms to the current tenant', function (): void {
    $this->rebootWith([
        'packstub-form-builder.tenancy.enabled' => true,
        'packstub-form-builder.tenancy.resolver' => fn () => $GLOBALS['fb_tenant'] ?? null,
    ]);

    $GLOBALS['fb_tenant'] = 7;
    $acme = contactForm(['slug' => 'acme-contact']);

    $GLOBALS['fb_tenant'] = 8;
    $globex = contactForm(['slug' => 'globex-contact']);

    expect((string) $acme->tenant_id)->toBe('7')
        ->and((string) $globex->refresh()->tenant_id)->toBe('8')
        ->and(Form::query()->pluck('slug')->all())->toBe(['globex-contact'])
        ->and(FormBuilder::find('acme-contact'))->toBeNull()
        ->and(FormBuilder::find('globex-contact'))->not->toBeNull()
        ->and(Form::query()->withoutGlobalScopes()->count())->toBe(2);

    $GLOBALS['fb_tenant'] = null;
    expect(Form::query()->count())->toBe(2);

    // The public endpoints run outside a tenant and see every form.
    $this->get('/forms/acme-contact')->assertOk();

    unset($GLOBALS['fb_tenant']);
});

it('does nothing without tenancy', function (): void {
    expect(FormBuilder::tenantColumn())->toBeNull()
        ->and(FormBuilder::currentTenantKey())->toBeNull()
        ->and(contactForm()->tenant_id)->toBeNull();
});
