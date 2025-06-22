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
        
        <!-- Tailwind CSS CDN as fallback -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Scripts -->
        @if(file_exists(public_path('build/manifest.json')))
            @php
                $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
                $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
                $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
            @endphp
            @if($cssFile)
                <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
            @endif
            @if($jsFile)
                <script type="module" src="{{ asset('build/' . $jsFile) }}"></script>
            @endif
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        <!-- Fallback for Alpine.js if CDN fails -->
        <script>
            if (typeof Alpine === 'undefined') {
                document.write('<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"><\/script>');
            }
        </script>

        @livewireStyles
    </head>
    <body class="font-sans antialiased h-full">
        <div class="min-h-full">
            @include('layouts.navigation')
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
