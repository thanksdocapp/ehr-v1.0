<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
		
		@if(app()->environment('production') && config('services.clarity.project_id'))
<script type="text/javascript">
(function(c,l,a,r,i,t,y){
  c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
  t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
  y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
})(window, document, "clarity", "script", "{{ config('services.clarity.project_id') }}");
</script>
@endif


        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <!-- AOS Animation Library -->
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <!-- Custom Dynamic Theme CSS -->
        <link rel="stylesheet" href="{{ route('theme.css') }}?v={{ time() }}">
        
        <!-- Scripts -->
        @if(app()->environment('local') && file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <link rel="stylesheet" href="{{ asset('build/assets/app-Bke7m33k.css') }}">
            <script src="{{ asset('build/assets/app-DaBYqt0m.js') }}" defer></script>
        @endif
        
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
        <!-- AOS Animation JS -->
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
        
        <!-- Initialize AOS -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 800,
                    easing: 'ease-in-out',
                    once: true,
                    mirror: false
                });
            }
        });
        </script>
    </head>
    <body class="font-sans antialiased">
        @if(Route::currentRouteName() === 'homepage' || request()->is('/'))
            <!-- Full-width layout for homepage -->
            @include('layouts.navigation')
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        @else
            <!-- Standard layout for other pages -->
            <div class="min-h-screen bg-gray-100">
                @include('layouts.navigation')

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif
                @hasSection('header')
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            @yield('header')
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main>
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>
        @endif
    </body>
</html>
