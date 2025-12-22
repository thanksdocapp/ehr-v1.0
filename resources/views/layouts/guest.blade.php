<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <!-- Custom Dynamic Theme CSS -->
        <link rel="stylesheet" href="{{ asset('css/dynamic-theme.css') }}">
        
        <!-- App Assets (Vite) -->
        @if(app()->environment('local') && file_exists(public_path('hot')))
            {{-- Vite dev server (local) --}}
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @elseif(file_exists(public_path('build/manifest.json')))
            {{-- Vite build (production/staging) --}}
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
        
        @stack('scripts')
    </head>
    <body class="bg-light">
        <div class="min-vh-100 d-flex flex-column py-4">
            <div class="mb-4 text-center">
                <a href="/" class="text-decoration-none">
                    <div class="d-flex align-items-center justify-content-center">
                        <x-application-logo style="height: 40px; width: 40px; fill: #6b7280;" />
                        <span class="ms-2 fs-4 fw-bold text-secondary">ThanksDoc</span>
                    </div>
                </a>
            </div>

            <div class="flex-grow-1">
                @yield('content')
            </div>
        </div>
        
        @stack('scripts')
    </body>
</html>
