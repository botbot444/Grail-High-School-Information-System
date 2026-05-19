<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Grail - @yield('title', 'School System')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans text-gray-900 antialiased">
        @if (session('notification'))
            <div class="notification" id="flash-notification">
                {{ session('notification') }}
            </div>
        @endif

        @isset($header)
            <header class="page-header">
                {{ $header }}
            </header>
        @endisset

        {{ $slot ?? '' }}
        @yield('content')

        @stack('scripts')
    </body>

</html>
