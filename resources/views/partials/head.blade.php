<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? null ? $title . " - " . config('app.name') : config('app.name') }}</title>

{{-- <link rel="icon" href="/favicon.ico" sizes="any"> --}}
<link rel="icon" href="{{ asset('icon-512.png') }}" type="image/png">
{{-- <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}"> --}}
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
{{-- <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" /> --}}
<link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
