<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Grail - Registration Submitted</title>

        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css'])
    </head>

    <body class="bg-surface">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="max-w-md w-full bg-white rounded-2xl shadow-sm p-8 text-center">
                <span class="material-symbols-outlined text-[#177aa4]" style="font-size:48px;">mark_email_read</span>
                <h1 class="text-xl font-extrabold text-on-surface mt-4">Submitted for review</h1>
                <p class="text-sm text-on-surface-variant mt-2">
                    Thanks — an admin will review your registration. You'll get an email once it's been approved or if
                    we need more information. Nothing is active until then.
                </p>
                <a href="{{ route('login') }}"
                    class="inline-block mt-6 px-6 py-3 rounded-xl text-white font-semibold shadow-sm hover:opacity-90 transition-opacity"
                    style="background:#177aa4;">
                    Back to Sign In
                </a>
            </div>
        </div>
    </body>

</html>
