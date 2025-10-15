<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />

    <meta name="application-name" content="{{ config('app.name') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ config('app.name') }}</title>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body class="antialiased">
    <div class="flex bg-gray-100 min-h-screen gap-4">
        <x-sidebar />
        <main class="flex-1 px-4 lg:py-4 max-w-6xl">
            <flux:header class="lg:hidden !px-2">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                <flux:spacer />

                <flux:dropdown position="top" align="start">
                    <flux:sidebar.profile name="{{ auth()->user()->nama ?? '' }}" avatar:color="amber" />

                    <flux:menu>
                        <flux:menu.item icon="cog-6-tooth" href="/akun">Pengaturan Akun</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </flux:header>
            {{ $slot }}
        </main>
    </div>

    @livewire('notifications')

    @filamentScripts
    @fluxScripts
    @vite('resources/js/app.js')
</body>

</html>
