<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Packstub\FormBuilder\Models\Form;
use Packstub\FormBuilder\Models\WebhookDelivery;
use Packstub\FormBuilder\Submissions\SubmissionContext;
use Packstub\FormBuilder\Submissions\Submitter;
use Packstub\FormBuilder\Webhooks\DeliverWebhook;
use Packstub\FormBuilder\Webhooks\Webhook;

function webhookForm(array $settings = [], string $slug = 'contact'): Form
{
    return contactForm(['slug' => $slug, 'settings' => [
        'webhook_url' => 'https://hooks.example.com/forms',
        'webhook_secret' => 'whsec_'.base64_encode('top-secret'),
        'webhook_headers' => ['X-Team' => 'sales'],
        ...$settings,
    ]]);
}

it('posts a signed payload to the webhook and logs the delivery', function (): void {
    Http::fake(['hooks.example.com/*' => Http::response(['received' => true], 200)]);
    $form = webhookForm(['webhook_fields' => ['name', 'email'], 'webhook_metadata' => false]);

    $submission = app(Submitter::class)->submit($form, contactInput($form))->submission;

    $delivery = WebhookDelivery::query()->firstOrFail();

    expect($delivery->status)->toBe('delivered')
        ->and($delivery->attempts)->toBe(1)
        ->and($delivery->response_status)->toBe(200)
        ->and($delivery->submission_id)->toBe($submission->id)
        ->and($delivery->payload['type'])->toBe('submission.received')
        ->and($delivery->payload['data']['data'])->toBe(['name' => 'Ada Lovelace', 'email' => 'ada@example.com'])
        ->and($delivery->payload['data'])->not->toHaveKey('meta')
        ->and($delivery->payload['data']['form']['slug'])->toBe('contact');

    Http::assertSent(function ($request): bool {
        $id = $request->header('webhook-id')[0];
        $timestamp = $request->header('webhook-timestamp')[0];
        $expected = 'v1,'.base64_encode(hash_hmac('sha256', "{$id}.{$timestamp}.".$request->body(), 'top-secret', true));

        return $request->url() === 'https://hooks.example.com/forms'
            && $request->method() === 'POST'
            && $request->header('webhook-signature')[0] === $expected
            && $request->header('X-Team')[0] === 'sales'
            && $request['data']['number'] === 1;
    });

    expect($form->webhookDeliveries()->count())->toBe(1);
});

it('retries a failed delivery with a delay and gives up after the configured attempts', function (): void {
    config()->set('packstub-form-builder.webhooks.attempts', 2);
    Http::fake(['hooks.example.com/*' => Http::response('nope', 500)]);
    Queue::fake();
    $form = webhookForm();

    app(Submitter::class)->submit($form, contactInput($form));

    $delivery = WebhookDelivery::query()->firstOrFail();
    Queue::assertPushed(DeliverWebhook::class, 1);

    (new DeliverWebhook($delivery))->handle();
    $delivery->refresh();

    expect($delivery->status)->toBe('pending')
        ->and($delivery->attempts)->toBe(1)
        ->and($delivery->error)->toBe('HTTP 500')
        ->and($delivery->next_attempt_at)->not->toBeNull();

    Queue::assertPushed(DeliverWebhook::class, 2);

    (new DeliverWebhook($delivery))->handle();

    expect($delivery->refresh()->status)->toBe('failed')->and($delivery->attempts)->toBe(2);

    Queue::assertPushed(DeliverWebhook::class, 2);
});

it('sends nothing without a valid URL and includes the metadata by default', function (): void {
    Http::fake();
    $form = contactForm(['settings' => ['webhook_url' => 'not a url']]);

    app(Submitter::class)->submit($form, contactInput($form));

    Http::assertNothingSent();
    expect(WebhookDelivery::query()->count())->toBe(0)
        ->and(Webhook::for($form))->toBeNull();

    $hooked = webhookForm(slug: 'hooked');
    $submission = app(Submitter::class)->submit($hooked, contactInput($hooked), new SubmissionContext(ip: '10.0.0.1', sourceUrl: 'https://example.com/contact'))->submission;
    $payload = Webhook::for($hooked)->payload($submission);

    expect($payload['data']['meta'])->toBe(['ip' => '10.0.0.1', 'source_url' => 'https://example.com/contact', 'channel' => 'web'])
        ->and(Webhook::generateSecret())->toStartWith('whsec_');
});
