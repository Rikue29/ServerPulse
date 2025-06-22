<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @if(app()->environment('local'))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            @php
                $manifestPath = public_path('build/.vite/manifest.json');
                $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;
                $cssFile = $manifest['resources/css/app.css']['file'] ?? 'app-CUZS7Njb.css';
                $jsFile = $manifest['resources/js/app.js']['file'] ?? 'app-BWj3x0W0.js';
            @endphp
            <link rel="stylesheet" href="{{ asset('build/assets/' . $cssFile) }}">
            <script type="module" src="{{ asset('build/assets/' . $jsFile) }}"></script>
        @endif

        <!--<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>-->
        
        <!-- Fallback for Alpine.js if CDN fails -->
        <!--<script>
            if (typeof Alpine === 'undefined') {
                document.write('<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"><\/script>');
            }
        </script>-->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        @livewireStyles
    </head>
    <body class="font-sans antialiased h-full">
        @include('layouts.navigation')

        <main class="p-6">
            @yield('content')
        </main>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
