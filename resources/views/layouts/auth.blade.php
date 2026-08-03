<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-black antialiased m-0">
       <main>
       {{ $slot }}
       </main>
        @fluxScripts
    </body>
</html>
