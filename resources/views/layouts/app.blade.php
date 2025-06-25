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
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

        <!--<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>-->
        
        <!-- Fallback for Alpine.js if CDN fails -->
        <!--<script>
            if (typeof Alpine === 'undefined') {
                document.write('<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"><\/script>');
            }
        </script>-->
        <!--<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>-->
        @livewireStyles
    </head>
    <body class="font-sans antialiased h-full">
        <!-- Critical Alert Banner -->
        @include('components.critical-alert-banner')
        
        @include('layouts.navigation')
        
        <!-- Toast Notifications -->
        @include('components.toast')
        
        @livewireScripts
        @stack('scripts')
    </body>
</html>
