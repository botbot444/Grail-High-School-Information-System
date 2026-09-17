<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Grail - Registration Submitted</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="{{ asset('fonts/material-symbols/material-symbols.css') }}" rel="stylesheet" />
    <style>@layer base{html,body{margin:0;padding:0;}}::-webkit-scrollbar{display:none;}</style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = { theme: { extend: {
            colors: {"surface":"#f8f9ff","surface-container-lowest":"#ffffff","surface-container-low":"#eff4ff","on-surface":"#0b1c30","on-surface-variant":"#444651","primary":"#00236f","on-primary":"#ffffff","secondary":"#0051d5"},
            borderRadius: {DEFAULT:"0.25rem",lg:"0.5rem",xl:"0.75rem",full:"9999px"},
            fontFamily: {"headline-md":["Plus Jakarta Sans"],"body-md":["Plus Jakarta Sans"],"label-lg":["Plus Jakarta Sans"]},
            fontSize: {"headline-md":["22px",{lineHeight:"28px",letterSpacing:"-0.01em",fontWeight:"600"}],"body-md":["14px",{lineHeight:"20px",fontWeight:"400"}],"label-lg":["14px",{lineHeight:"20px",letterSpacing:"0.01em",fontWeight:"600"}]},
        } } };
    </script>
</head>

<body class="bg-surface font-body-md text-body-md text-on-surface antialiased min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-surface-container-lowest rounded-xl shadow-md p-8 text-center">
        <div class="w-14 h-14 rounded-full bg-surface-container-low flex items-center justify-center text-primary mx-auto">
            <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">mark_email_read</span>
        </div>
        <h1 class="font-headline-md text-headline-md text-primary mt-4">Submitted for review</h1>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">
            Thanks — an admin will review your registration. You'll get an email once it's been approved or if
            we need more information. Nothing is active until then.
        </p>
        <a href="{{ route('login') }}"
            class="inline-flex items-center justify-center gap-2 mt-6 h-11 px-6 rounded-lg bg-primary text-on-primary font-label-lg text-label-lg shadow-sm hover:opacity-90 transition-opacity">
            Back to Sign In
        </a>
    </div>
</body>
</html>
