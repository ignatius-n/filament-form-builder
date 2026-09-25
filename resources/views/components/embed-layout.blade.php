<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        html, body { margin: 0; background: transparent; }
        body { font-family: var(--fb-font, ui-sans-serif, system-ui, sans-serif); color: var(--fb-color-text, #0f172a); }
        .fb-embed { padding: 0.25rem; }
    </style>
</head>
<body>
    <main class="fb-embed">
        {{ $slot }}
    </main>
    <script>
        (function () {
            var send = function () {
                var height = document.documentElement.scrollHeight;
                if (window.parent && window.parent !== window) window.parent.postMessage({ type: 'form-builder:resize', height: height, slug: @json($slug ?? null) }, '*');
            };
            send();
            if (window.ResizeObserver) new ResizeObserver(send).observe(document.body); else setInterval(send, 500);
            window.addEventListener('load', send);
        })();
    </script>
</body>
</html>
