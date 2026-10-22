@props([
    'loadJs' => true,
])

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />

        <meta name="application-name" content="{{ config('app.name') }}" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <!-- Favicons -->
        <link rel="icon" type="image/x-icon" href="/images/favicon.ico" />

        <!-- PAGE TITLE -->
        <title>SRAFES - Education for all</title>

        <meta
            name="description"
            content="Discover SRAFES - where nurturing educators and innovative learning come together to spark your child's curiosity, creativity, and confidence."
        />

        @stack('before.head.end')

        @vite(['resources/css/app.css'])
    </head>

    <body>
        <x-banner />

        <div {{ $hero->attributes }}>
            <x-header />

            {{ $hero }}
        </div>

        {{ $slot }}

        <x-footer />

        @stack('before.body.end')

        @if ($loadJs)
            @vite(['resources/js/app.js'])
        @endif
    </body>
</html>
