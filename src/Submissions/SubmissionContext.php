<?php

namespace Packstub\FormBuilder\Submissions;

use Illuminate\Http\Request;

/**
 * Where a submission came from.
 */
final class SubmissionContext
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly ?string $ip = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $sourceUrl = null,
        public readonly int|string|null $userId = null,
        public readonly string $channel = 'web',
        public readonly array $meta = [],
        public readonly bool $trusted = false,
        public readonly ?string $origin = null,
    ) {}

    public static function fromRequest(Request $request, string $channel = 'web'): self
    {
        return new self(
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            sourceUrl: self::sourceUrl($request),
            userId: $request->user()?->getAuthIdentifier(),
            channel: $channel,
            origin: $request->headers->get('origin') ?: $request->headers->get('referer'),
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function withMeta(array $meta): self
    {
        return new self($this->ip, $this->userAgent, $this->sourceUrl, $this->userId, $this->channel, [...$this->meta, ...$meta], $this->trusted, $this->origin);
    }

    public function withChannel(string $channel): self
    {
        return new self($this->ip, $this->userAgent, $this->sourceUrl, $this->userId, $channel, $this->meta, $this->trusted, $this->origin);
    }

    /**
     * Skip the honeypot and time-trap checks (submissions made from code).
     */
    public function trusted(bool $trusted = true): self
    {
        return new self($this->ip, $this->userAgent, $this->sourceUrl, $this->userId, $this->channel, $this->meta, $trusted, $this->origin);
    }

    /**
     * A stable, anonymous id of the visitor: the user id when signed in,
     * else a hash of the IP address and the user agent. Used for the
     * "one submission per person" limit.
     */
    public function fingerprint(): ?string
    {
        if ($this->userId !== null) {
            return 'user:'.$this->userId;
        }

        if ($this->ip === null && $this->userAgent === null) {
            return null;
        }

        return hash('xxh128', ($this->ip ?? '').'|'.($this->userAgent ?? ''));
    }

    /**
     * The page the form was submitted from: the hidden return field, else the referer.
     */
    public static function sourceUrl(Request $request): ?string
    {
        $url = $request->input('_fb_return') ?: $request->headers->get('referer');

        return is_string($url) && $url !== '' ? mb_substr($url, 0, 2048) : null;
    }
}
