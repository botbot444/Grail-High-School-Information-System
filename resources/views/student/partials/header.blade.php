{{--
    Fixed top bar. Shifts with the sidebar on desktop; on mobile it spans the full
    width and carries the hamburger that opens the sidebar drawer.
--}}
<header
    class="header-transition fixed top-0 right-0 left-0 h-header-height bg-surface-container-lowest border-b border-outline-variant/60 flex items-center justify-between gap-4 px-space-md md:px-space-lg z-30"
    :class="collapsed ? 'md:left-sidebar-rail' : 'md:left-sidebar-width'"
>
    <div class="flex items-center gap-3 min-w-0">
        <button type="button" @click="mobileOpen = !mobileOpen"
            class="md:hidden p-2 -ml-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-low rounded-xl transition-colors"
            aria-label="Open navigation">
            <span class="material-symbols-outlined text-[22px]">menu</span>
        </button>

        <div class="min-w-0">
            <h1 class="text-headline-sm font-headline-sm text-primary truncate">@yield('page-title', 'Dashboard')</h1>
            @hasSection('page-subtitle')
                <p class="text-body-sm text-on-surface-variant truncate">@yield('page-subtitle')</p>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3 shrink-0">
        @if (!empty($navTerm))
            <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-primary/5 border border-primary/15 text-primary text-label-sm font-medium">
                <span class="material-symbols-outlined text-sm text-secondary">date_range</span>
                <span>{{ $navTerm->academicYear?->label ? 'AY ' . $navTerm->academicYear->label . ' • ' : '' }}{{ $navTerm->name }}</span>
            </div>
        @endif

        @if (!empty($navStudent))
            <div class="flex items-center gap-2.5 pl-3 border-l border-outline-variant/60">
                <div class="w-9 h-9 rounded-[50%] bg-primary text-on-primary font-semibold flex items-center justify-center text-label-sm">
                    {{ $navStudentInitials ?? 'S' }}
                </div>
                <div class="hidden sm:block leading-tight">
                    <p class="text-label-md font-semibold text-on-surface truncate max-w-[160px]">{{ $navStudent->full_name }}</p>
                    <p class="text-label-sm text-on-surface-variant">Student</p>
                </div>
            </div>
        @endif
    </div>
</header>
