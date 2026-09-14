<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Portal') — Grail SIS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        /*
            Fallback for when Alpine has not run.

            Every width, offset and label in this shell is an Alpine binding, so
            if the bundle does not load — dev server down, a stale public/hot
            pointing at an old LAN IP, a blocked request, a JS error earlier on
            the page — the sidebar ends up with no width and every x-cloak'd
            label stays hidden. That looks exactly like a collapsed sidebar that
            refuses to expand, which is the worst possible failure mode: the
            control that would fix it is the thing that disappeared.

            :where() keeps these at zero specificity, so the instant Alpine
            applies a real class it wins.
        */
        @media (min-width: 768px) {
            :where(.student-shell aside)  { width: 260px; }
            :where(.student-shell main)   { margin-left: 260px; }
            :where(.student-shell header) { left: 260px; }
        }
        @media (max-width: 767.98px) {
            :where(.student-shell aside)  { transform: translateX(-100%); }
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .material-symbols-outlined.filled { font-variation-settings: 'FILL' 1; }
        .student-shell ::-webkit-scrollbar { width: 6px; height: 6px; }
        .student-shell ::-webkit-scrollbar-track { background: #f1f5f9; }
        .student-shell ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .student-shell ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="student-shell bg-background font-sans text-on-surface antialiased min-h-full">

{{--
    Retractable sidebar.
    `collapsed` narrows the sidebar to an icon rail on desktop and is remembered
    per browser. `mobileOpen` drives the off-canvas drawer below the md breakpoint,
    where a 76px rail would still crowd the viewport.
--}}
<div
    x-data="studentShell()"
    x-init="init()"
    @keydown.window.escape="mobileOpen = false"
    class="min-h-screen"
>
    @include('student.partials.sidebar')

    {{-- Scrim behind the mobile drawer --}}
    <div
        x-cloak
        x-show="mobileOpen"
        x-transition.opacity
        @click="mobileOpen = false"
        class="fixed inset-0 bg-on-surface/40 z-40 md:hidden"
        aria-hidden="true"
    ></div>

    @include('student.partials.header')

    <main
        class="main-transition pt-[96px] px-space-md md:px-space-lg pb-space-2xl"
        :class="collapsed ? 'md:ml-sidebar-rail' : 'md:ml-sidebar-width'"
    >
        <div class="max-w-[1400px] mx-auto">
            @if (session('notification'))
                <div class="mb-space-lg flex items-start gap-3 rounded-xl border border-secondary/30 bg-secondary-container/40 px-4 py-3 text-body-md text-on-surface">
                    <span class="material-symbols-outlined text-secondary text-xl">info</span>
                    <p>{{ session('notification') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-space-lg rounded-xl border border-error/30 bg-error-container/60 px-4 py-3">
                    <p class="flex items-center gap-2 text-body-md font-semibold text-on-error-container">
                        <span class="material-symbols-outlined text-xl">error</span>
                        Please fix the following:
                    </p>
                    <ul class="mt-2 ml-8 list-disc space-y-1 text-body-sm text-on-error-container">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script>
    function studentShell() {
        return {
            collapsed: false,
            mobileOpen: false,
            init() {
                try {
                    this.collapsed = localStorage.getItem('grail.student.sidebar') === 'collapsed';
                } catch (e) {
                    // Private browsing or blocked storage — fall back to expanded.
                    this.collapsed = false;
                }
            },
            toggle() {
                this.collapsed = !this.collapsed;
                try {
                    localStorage.setItem('grail.student.sidebar', this.collapsed ? 'collapsed' : 'expanded');
                } catch (e) {
                    // Preference simply won't persist; the toggle still works this session.
                }
            },
        };
    }
</script>

@stack('scripts')
</body>
</html>
