<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @if (! empty($description))
        <meta name="description" content="{{ $description }}">
        <meta property="og:description" content="{{ $description }}">
    @endif
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if (! empty($image))
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif
    <style>
        body { margin: 0; font-family: var(--fb-font, ui-sans-serif, system-ui, sans-serif); background: var(--fb-color-background, #f8fafc); color: var(--fb-color-text, #0f172a); }
        .fb-page { max-width: 40rem; margin: 0 auto; padding: 3rem 1.25rem; }
        .fb-page__logo { max-height: 3rem; margin: 0 0 1.5rem; display: block; }
        .fb-page__title { font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem; }
        .fb-page__description { margin: 0 0 2rem; color: var(--fb-color-muted, #475569); }
    </style>
</head>
<body>
    <main class="fb-page">
        {{ $slot }}
    </main>
</body>
</html>
