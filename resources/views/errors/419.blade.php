<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Page Expired</title>
        @vite(['resources/css/app.css'])
    </head>

    <body class="min-h-screen bg-surface-container flex items-center justify-center p-6">
        <div class="max-w-md rounded-2xl border border-outline-variant bg-white p-8 text-center shadow-sm">
            <div
                class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                <span class="material-symbols-outlined text-3xl">warning</span>
            </div>
            <h1 class="text-2xl font-bold text-on-surface">Page expired</h1>
            <p class="mt-3 text-sm text-on-surface-variant">
                {{ $message ?? 'Your session has expired. Please refresh the page and try again.' }}</p>
            <a href="{{ route('login') }}"
                class="mt-6 inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary">
                Go to login
            </a>
        </div>
    </body>

</html>
