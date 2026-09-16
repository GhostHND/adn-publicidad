<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @class([
        'dark' => ($appearance ?? 'system') === 'dark',
    ])
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="theme-color"
        content="#0fa7b4"
    >

    <meta
        name="application-name"
        content="ADN Publicidad"
    >

    <meta
        name="apple-mobile-web-app-capable"
        content="yes"
    >

    <meta
        name="apple-mobile-web-app-status-bar-style"
        content="black-translucent"
    >

    <meta
        name="apple-mobile-web-app-title"
        content="ADN Publicidad"
    >

    <!--
    |--------------------------------------------------------------------------
    | APARIENCIA
    |--------------------------------------------------------------------------
    -->

    <script>
        (() => {
            const appearance = @json($appearance ?? 'system');

            if (appearance === 'system') {
                const prefersDark = window.matchMedia(
                    '(prefers-color-scheme: dark)'
                ).matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }

                return;
            }

            document.documentElement.classList.toggle(
                'dark',
                appearance === 'dark'
            );
        })();
    </script>

    <!--
    |--------------------------------------------------------------------------
    | COLOR DE FONDO INICIAL
    |--------------------------------------------------------------------------
    |
    | Evita destello blanco mientras Vue y Tailwind terminan de cargar.
    |
    -->

    <style>
        html {
            background-color: #f7fbfc;
        }

        html.dark {
            background-color: #05090b;
        }
    </style>

    <!--
    |--------------------------------------------------------------------------
    | IDENTIDAD ADN
    |--------------------------------------------------------------------------
    -->

    <link
        rel="icon"
        href="/icons/logo_adn.svg"
        type="image/svg+xml"
    >

    <link
        rel="apple-touch-icon"
        href="/icons/logo_adn.svg"
    >

    <link
        rel="manifest"
        href="/manifest.webmanifest"
    >

    <!--
    |--------------------------------------------------------------------------
    | FUENTES
    |--------------------------------------------------------------------------
    -->

    @fonts

    <!--
    |--------------------------------------------------------------------------
    | VITE
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    | app.css contiene Tailwind.
    | app.ts inicia Vue/Inertia.
    | el componente actual permite a Vite resolver correctamente sus assets.
    |
    -->

    @vite([
        'resources/css/app.css',
        'resources/js/app.ts',
        "resources/js/pages/{$page['component']}.vue",
    ])

    <!--
    |--------------------------------------------------------------------------
    | INERTIA HEAD
    |--------------------------------------------------------------------------
    -->

    <x-inertia::head>
        <title>
            {{ config('app.name', 'ADN Publicidad') }}
        </title>
    </x-inertia::head>
</head>

<body
    class="font-sans antialiased"
>
    <x-inertia::app />
</body>
</html>