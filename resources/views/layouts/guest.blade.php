<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Event Pulse') }}</title>

        <link rel="icon" href="{{ asset('images/brand/mark.svg') }}" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,600,700|inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white">
        <div class="relative min-h-screen flex flex-col justify-center items-center overflow-hidden px-4 py-12">
            <div class="pointer-events-none absolute -top-20 left-1/2 -translate-x-1/2 blur-3xl" aria-hidden="true">
                <div class="h-[250px] w-[min(600px,92vw)] bg-gradient-to-tr from-coral-soft/20 to-coral/20 opacity-60 [clip-path:ellipse(50%_50%_at_50%_50%)]"></div>
            </div>

            <div class="relative z-10 flex flex-col items-center w-full max-w-md">
                <a href="{{ route('events.index') }}" class="mb-10 inline-flex items-center gap-3 no-underline">
                    <img src="{{ asset('images/brand/mark.svg') }}" alt="" class="h-11 w-11">
                    <span class="ep-logo-text notranslate" translate="no"><span class="text-frost">Event</span> <span class="text-charcoal">Pulse</span></span>
                </a>

                <div class="w-full px-6 sm:px-8 py-8 sm:py-10 ep-card shadow-lift">
                    {{ $slot }}
                </div>

                <p class="mt-8 text-xs text-frost">&copy; {{ now()->year }} Event Pulse</p>
            </div>
        </div>
    </body>
</html>
