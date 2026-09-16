<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Grail SIS - Welcome</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}}::-webkit-scrollbar{display:none;}</style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = { theme: { extend: {
            colors: {"surface":"#f8f9ff","surface-container-lowest":"#ffffff","surface-container-low":"#eff4ff","surface-container":"#e5eeff","surface-container-high":"#dce9ff","surface-container-highest":"#d3e4fe","on-surface":"#0b1c30","on-surface-variant":"#444651","outline":"#757682","outline-variant":"#c5c5d3","primary":"#00236f","on-primary":"#ffffff","primary-container":"#1e3a8a","on-primary-container":"#90a8ff","primary-fixed":"#dce1ff","primary-fixed-dim":"#b6c4ff","on-primary-fixed":"#00164e","secondary":"#0051d5","on-secondary":"#ffffff","secondary-container":"#316bf3","secondary-fixed":"#dbe1ff","on-secondary-fixed-variant":"#003ea8"},
            borderRadius: {DEFAULT:"0.25rem",lg:"0.5rem",xl:"0.75rem",full:"9999px"},
            spacing: {"gutter-sm":"1rem","space-md":"1rem",gutter:"1.5rem","space-lg":"1.5rem","margin-sm":"1rem","space-xl":"2.5rem","space-sm":"0.5rem","space-xs":"0.25rem",margin:"2rem"},
            fontFamily: {"headline-xl":["Plus Jakarta Sans"],"headline-lg":["Plus Jakarta Sans"],"headline-md":["Plus Jakarta Sans"],"headline-sm":["Plus Jakarta Sans"],"body-lg":["Plus Jakarta Sans"],"body-md":["Plus Jakarta Sans"],"body-sm":["Plus Jakarta Sans"],"label-lg":["Plus Jakarta Sans"],"label-md":["Plus Jakarta Sans"],"label-sm":["Plus Jakarta Sans"]},
            fontSize: {"headline-xl":["36px",{lineHeight:"44px",letterSpacing:"-0.02em",fontWeight:"700"}],"headline-lg":["30px",{lineHeight:"38px",letterSpacing:"-0.015em",fontWeight:"700"}],"headline-md":["22px",{lineHeight:"28px",letterSpacing:"-0.01em",fontWeight:"600"}],"headline-sm":["18px",{lineHeight:"24px",fontWeight:"600"}],"body-lg":["16px",{lineHeight:"24px",fontWeight:"400"}],"body-md":["14px",{lineHeight:"20px",fontWeight:"400"}],"body-sm":["12px",{lineHeight:"16px",fontWeight:"400"}],"label-lg":["14px",{lineHeight:"20px",letterSpacing:"0.01em",fontWeight:"600"}],"label-md":["12px",{lineHeight:"16px",letterSpacing:"0.02em",fontWeight:"600"}],"label-sm":["11px",{lineHeight:"14px",letterSpacing:"0.04em",fontWeight:"700"}]},
        } } };
    </script>
</head>

<body class="bg-surface font-body-md text-body-md text-on-surface antialiased min-h-screen flex flex-col">

    <header class="w-full bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(15,23,42,0.05)]">
        <div class="h-20 w-full px-margin flex items-center">
            <div class="flex items-center gap-space-md">
                <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-on-primary text-[22px]">school</span>
                </div>
                <div class="flex flex-col">
                    <span class="font-headline-sm text-headline-sm text-primary tracking-tight">Grail SIS</span>
                    <span class="font-label-sm text-label-sm text-on-surface-variant">School Information System</span>
                </div>
            </div>
        </div>
    </header>

    <main class="w-full flex-1 bg-surface">
        <div class="relative w-full overflow-hidden">
            <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[900px] h-[360px] bg-gradient-to-b from-primary-fixed-dim/30 via-surface-container-high/40 to-transparent blur-3xl pointer-events-none -z-10"></div>

            <section class="w-full px-margin pt-12 pb-8 flex flex-col items-center text-center max-w-5xl mx-auto">
                <h1 class="font-headline-xl text-headline-xl text-primary tracking-tight max-w-3xl mb-space-sm">
                    Welcome to Grail School Information System
                </h1>
                <p class="font-body-lg text-body-lg text-primary-container font-semibold tracking-wide mb-space-xs">
                    Manages student attendance, grades, finance, and communication.
                </p>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-2xl leading-relaxed">
                    Please choose your pathway below to continue.
                </p>
            </section>

            <section class="w-full px-margin py-6 max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-space-lg items-stretch">

                    {{-- Card 1: Login --}}
                    <div class="group relative rounded-xl bg-surface-container-lowest p-space-lg flex flex-col justify-between shadow-md hover:shadow-xl transition-all duration-300">
                        <div class="absolute top-0 left-6 right-6 h-1 bg-primary rounded-t-full"></div>
                        <div>
                            <span class="inline-flex items-center gap-space-xs px-space-md py-space-xs rounded-full bg-surface-container-high text-primary font-label-md text-label-md font-semibold mb-space-md">
                                <span class="material-symbols-outlined text-[16px]">lock</span>
                                Existing Accounts
                            </span>
                            <div class="flex items-start gap-space-md mb-space-md">
                                <div class="w-14 h-14 rounded-xl bg-primary flex items-center justify-center text-on-primary shadow-sm flex-shrink-0">
                                    <span class="material-symbols-outlined text-[30px]">badge</span>
                                </div>
                                <div>
                                    <h2 class="font-headline-lg text-headline-lg text-primary leading-tight">Portal Login</h2>
                                    <p class="font-body-md text-body-md text-on-surface-variant mt-space-xs">
                                        For admin staff, teachers, parents and students who already have an account.
                                    </p>
                                </div>
                            </div>
                            <div class="mb-space-lg">
                                <div class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant mb-space-xs">Designed for:</div>
                                <div class="grid grid-cols-2 gap-space-xs">
                                    <div class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-lg bg-surface-container-low text-on-surface">
                                        <span class="material-symbols-outlined text-[16px] text-secondary">admin_panel_settings</span>
                                        <span class="font-label-md text-label-md font-semibold truncate">Admin</span>
                                    </div>
                                    <div class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-lg bg-surface-container-low text-on-surface">
                                        <span class="material-symbols-outlined text-[16px] text-secondary">school</span>
                                        <span class="font-label-md text-label-md font-semibold truncate">Teachers</span>
                                    </div>
                                    <div class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-lg bg-surface-container-low text-on-surface">
                                        <span class="material-symbols-outlined text-[16px] text-secondary">escalator_warning</span>
                                        <span class="font-label-md text-label-md font-semibold truncate">Parents</span>
                                    </div>
                                    <div class="flex items-center gap-space-xs px-space-sm py-space-xs rounded-lg bg-surface-container-low text-on-surface">
                                        <span class="material-symbols-outlined text-[16px] text-secondary">menu_book</span>
                                        <span class="font-label-md text-label-md font-semibold truncate">Students</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pt-space-sm">
                            <a href="{{ route('login') }}" class="w-full h-11 px-space-lg rounded-lg bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg flex items-center justify-center gap-space-sm shadow-md transition-all">
                                <span>Sign In to Your Account</span>
                                <span class="material-symbols-outlined text-[20px] transition-transform group-hover:translate-x-1">arrow_forward</span>
                            </a>
                        </div>
                    </div>

                    {{-- Card 2: Register --}}
                    <div class="group relative rounded-xl bg-surface-container-lowest p-space-lg flex flex-col justify-between shadow-md hover:shadow-xl transition-all duration-300">
                        <div class="absolute top-0 left-6 right-6 h-1 bg-secondary rounded-t-full"></div>
                        <div>
                            <span class="inline-flex items-center gap-space-xs px-space-md py-space-xs rounded-full bg-secondary-fixed text-on-secondary-fixed-variant font-label-md text-label-md font-semibold mb-space-md">
                                <span class="material-symbols-outlined text-[16px]">how_to_reg</span>
                                New Family Registration
                            </span>
                            <div class="flex items-start gap-space-md mb-space-md">
                                <div class="w-14 h-14 rounded-xl bg-secondary flex items-center justify-center text-on-secondary shadow-sm flex-shrink-0">
                                    <span class="material-symbols-outlined text-[30px]">family_restroom</span>
                                </div>
                                <div>
                                    <h2 class="font-headline-lg text-headline-lg text-on-surface leading-tight">New Parent Enrollment</h2>
                                    <p class="font-body-md text-body-md text-on-surface-variant mt-space-xs">
                                        For parents and guardians registering themselves and enrolling a new child.
                                    </p>
                                </div>
                            </div>
                            <div class="mb-space-lg p-space-md rounded-lg bg-surface-container-high text-primary flex items-start gap-space-sm shadow-sm">
                                <span class="material-symbols-outlined text-secondary text-[22px] flex-shrink-0 mt-0.5">info</span>
                                <div>
                                    <div class="font-label-md text-label-md font-bold uppercase tracking-wider text-primary">Administrative Review</div>
                                    <p class="font-body-sm text-body-sm text-on-surface leading-snug mt-0.5">
                                        Submits a request for a school administrator to review — <strong class="font-semibold text-primary">does not create an account immediately</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="pt-space-sm">
                            <a href="{{ route('register') }}" class="w-full h-11 px-space-lg rounded-lg bg-secondary hover:bg-secondary-container text-on-secondary font-label-lg text-label-lg flex items-center justify-center gap-space-sm shadow-md transition-all">
                                <span>Start Registration</span>
                                <span class="material-symbols-outlined text-[20px] transition-transform group-hover:translate-x-1">arrow_forward</span>
                            </a>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </main>

</body>
</html>
