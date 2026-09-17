<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Grail - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
    <link href="{{ asset('fonts/material-symbols/material-symbols.css') }}" rel="stylesheet" />
    <style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}}::-webkit-scrollbar{display:none;}</style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = { theme: { extend: {
            colors: {"surface":"#f8f9ff","surface-container-lowest":"#ffffff","surface-container-low":"#eff4ff","surface-container":"#e5eeff","surface-container-high":"#dce9ff","surface-container-highest":"#d3e4fe","on-surface":"#0b1c30","on-surface-variant":"#444651","outline":"#757682","outline-variant":"#c5c5d3","primary":"#00236f","on-primary":"#ffffff","primary-container":"#1e3a8a","primary-fixed":"#dce1ff","primary-fixed-dim":"#b6c4ff","on-primary-fixed":"#00164e","secondary":"#0051d5","on-secondary":"#ffffff","secondary-fixed":"#dbe1ff","on-secondary-fixed-variant":"#003ea8","error":"#ba1a1a","on-error":"#ffffff","error-container":"#ffdad6","on-error-container":"#93000a"},
            borderRadius: {DEFAULT:"0.25rem",lg:"0.5rem",xl:"0.75rem",full:"9999px"},
            spacing: {"gutter-sm":"1rem","space-md":"1rem",gutter:"1.5rem","space-lg":"1.5rem","margin-sm":"1rem","space-xl":"2.5rem","space-sm":"0.5rem","space-xs":"0.25rem",margin:"2rem"},
            fontFamily: {"headline-md":["Plus Jakarta Sans"],"headline-sm":["Plus Jakarta Sans"],"body-md":["Plus Jakarta Sans"],"body-sm":["Plus Jakarta Sans"],"label-lg":["Plus Jakarta Sans"],"label-md":["Plus Jakarta Sans"],"label-sm":["Plus Jakarta Sans"]},
            fontSize: {"headline-md":["22px",{lineHeight:"28px",letterSpacing:"-0.01em",fontWeight:"600"}],"headline-sm":["18px",{lineHeight:"24px",fontWeight:"600"}],"body-md":["14px",{lineHeight:"20px",fontWeight:"400"}],"body-sm":["12px",{lineHeight:"16px",fontWeight:"400"}],"label-lg":["14px",{lineHeight:"20px",letterSpacing:"0.01em",fontWeight:"600"}],"label-md":["12px",{lineHeight:"16px",letterSpacing:"0.02em",fontWeight:"600"}],"label-sm":["11px",{lineHeight:"14px",letterSpacing:"0.04em",fontWeight:"700"}]},
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

    <main class="w-full flex-1 bg-surface flex flex-col items-center justify-center relative overflow-hidden py-space-xl px-gutter-sm">
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-primary/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-[30rem] h-[30rem] rounded-full bg-secondary/5 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 w-full max-w-[480px] mb-space-md">
            <a href="{{ route('welcome') }}" class="inline-flex items-center gap-space-xs font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors py-space-xs px-space-sm rounded-lg hover:bg-surface-container-low">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Back to Home</span>
            </a>
        </div>

        <div class="relative z-10 w-full max-w-[480px] bg-surface-container-lowest rounded-xl shadow-xl shadow-primary/5">
            <div class="h-1.5 w-full bg-gradient-to-r from-primary via-secondary to-primary-fixed-dim rounded-t-xl"></div>
            <div class="p-space-lg sm:p-space-xl flex flex-col">

                <div class="flex flex-col items-center text-center mb-space-lg">
                    <div class="relative mb-space-md p-space-xs rounded-xl bg-surface-container-low shadow-sm">
                        <div class="h-14 w-14 rounded-lg bg-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-on-primary text-[32px]">school</span>
                        </div>
                    </div>
                    <span class="font-headline-md text-headline-md text-primary tracking-tight">Grail SIS</span>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-space-xs max-w-xs leading-snug">
                        Manages student attendance, grades, finance, and communication.
                    </p>
                </div>

                <div class="mb-space-md">
                    <h1 class="font-headline-sm text-headline-sm text-on-surface">Sign In to Your Account</h1>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Enter your email and password.</p>
                </div>

                @if ($errors->any())
                    <div class="flex items-start gap-space-sm p-space-md rounded-lg bg-error-container/60 mb-space-md">
                        <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5" style="font-variation-settings: 'FILL' 1;">error</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-label-sm text-label-sm text-error font-bold uppercase tracking-wider">There is a problem</p>
                            <p class="font-body-sm text-body-sm text-on-error-container mt-0.5">
                                {{ $errors->first() }}
                            </p>
                        </div>
                    </div>
                @endif

                @if (session('status'))
                    <div class="flex items-start gap-space-sm p-space-md rounded-lg bg-secondary-fixed/60 mb-space-md">
                        <p class="font-body-sm text-body-sm text-on-surface">{{ session('status') }}</p>
                    </div>
                @endif

                <form class="flex flex-col gap-space-md" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="flex flex-col gap-space-xs">
                        <label class="font-label-md text-label-md text-on-surface" for="email">Email Address</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-space-md text-on-surface-variant text-[20px] pointer-events-none">badge</span>
                            <input autocomplete="username" class="w-full h-11 pl-11 pr-space-md bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface placeholder:text-outline/70 focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="email" name="email" placeholder="admin@grail.school" required type="email" value="{{ old('email') }}" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-space-xs">
                        <div class="flex items-center justify-between">
                            <label class="font-label-md text-label-md text-on-surface" for="password">Password</label>
                            <a class="font-label-sm text-label-sm text-secondary hover:text-primary transition-colors" href="{{ route('password.request') }}">
                                Forgot Password?
                            </a>
                        </div>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-space-md text-on-surface-variant text-[20px] pointer-events-none">lock</span>
                            <input autocomplete="current-password" class="w-full h-11 pl-11 pr-11 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface placeholder:text-outline/70 focus:outline-none focus:ring-2 focus:ring-secondary transition-all" id="password" name="password" placeholder="••••••••••••" required type="password" />
                            <button aria-label="Toggle password visibility" class="absolute right-space-sm p-space-xs text-on-surface-variant hover:text-on-surface transition-colors rounded" id="password-toggle-btn" type="button">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-start gap-space-sm mt-0.5">
                        <div class="flex items-center h-5">
                            <input class="w-4 h-4 rounded text-secondary focus:ring-0 focus:outline-none cursor-pointer" id="remember" name="remember" type="checkbox" />
                        </div>
                        <label class="font-body-sm text-body-sm text-on-surface-variant cursor-pointer select-none" for="remember">
                            Remember me
                        </label>
                    </div>

                    <button class="w-full h-11 bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg rounded-lg shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-space-xs mt-space-xs active:scale-[0.99]" type="submit">
                        <span>Sign In</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-space-lg p-space-md rounded-xl bg-surface-container-low/80 flex items-center justify-between gap-space-sm">
                    <div class="flex items-center gap-space-sm">
                        <div class="w-8 h-8 rounded-lg bg-primary-fixed flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-primary text-[18px]">family_restroom</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-label-md text-label-md text-on-surface">New parent or guardian?</span>
                            <span class="font-body-sm text-body-sm text-on-surface-variant">Register you and your child</span>
                        </div>
                    </div>
                    <a class="shrink-0 px-space-md py-1.5 rounded-lg bg-secondary text-on-secondary hover:bg-primary font-label-md text-label-md transition-colors shadow-sm" href="{{ route('register') }}">
                        Register
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('password-toggle-btn').addEventListener('click', function () {
            const p = document.getElementById('password');
            const i = this.querySelector('span');
            if (p.type === 'password') {
                p.type = 'text';
                i.textContent = 'visibility_off';
            } else {
                p.type = 'password';
                i.textContent = 'visibility';
            }
        });
    </script>

</body>
</html>
