{{--
    Student portal navigation.

    Order follows the Stitch designs. "Messages" is deliberately absent — direct
    messaging is Part 2 / A5 in the implementation plan and is not being built.

    When `collapsed` is true the sidebar narrows to an icon rail: labels and
    trailing badges are hidden, and each link keeps a title attribute so the
    icons stay identifiable.
--}}
@php
    $navItems = [
        ['route' => 'student.dashboard',     'icon' => 'dashboard',      'label' => 'Dashboard'],
        ['route' => 'student.results',       'icon' => 'grade',          'label' => 'My Results'],
        ['route' => 'student.attendance',    'icon' => 'how_to_reg',     'label' => 'Attendance'],
        ['route' => 'student.timetable',     'icon' => 'calendar_month', 'label' => 'Timetable'],
        ['route' => 'student.assignments.index', 'icon' => 'assignment', 'label' => 'Assignments', 'badge' => $navDueAssignments ?? null],
        ['route' => 'student.report-cards',  'icon' => 'description',    'label' => 'Report Cards'],
        ['route' => 'student.announcements', 'icon' => 'campaign',       'label' => 'Announcements'],
        ['route' => 'student.settings',      'icon' => 'settings',       'label' => 'Settings'],
    ];
@endphp

<aside
    class="sidebar-transition fixed top-0 left-0 bottom-0 z-50 bg-surface-container-lowest border-r border-outline-variant/60 flex flex-col select-none
           md:translate-x-0"
    :class="[
        collapsed ? 'w-sidebar-rail' : 'w-sidebar-width',
        mobileOpen ? 'translate-x-0 w-sidebar-width' : '-translate-x-full',
    ]"
>
    {{-- Brand --}}
    <div class="h-header-height px-space-md flex items-center gap-3 border-b border-outline-variant/50 shrink-0">
        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-on-primary shadow-sm shrink-0">
            <span class="material-symbols-outlined filled">school</span>
        </div>
        <div class="leading-tight overflow-hidden" x-show="!collapsed || mobileOpen" x-cloak>
            <span class="font-bold text-title-sm text-primary tracking-tight block whitespace-nowrap">Grail SIS</span>
            <span class="text-label-sm text-on-surface-variant whitespace-nowrap">Student Portal</span>
        </div>
    </div>

    {{-- Mini profile --}}
    @if (!empty($navStudent))
        <div class="px-space-sm py-space-sm mt-2 shrink-0">
            <div class="bg-surface-container-low rounded-xl p-3 flex items-center gap-3 border border-outline-variant/30"
                 :class="(collapsed && !mobileOpen) ? 'justify-center' : ''">
                <div class="w-9 h-9 rounded-[50%] bg-secondary text-on-secondary font-semibold flex items-center justify-center text-label-sm shrink-0">
                    {{ $navStudentInitials ?? 'S' }}
                </div>
                <div class="overflow-hidden" x-show="!collapsed || mobileOpen" x-cloak>
                    <p class="text-label-sm font-semibold text-primary truncate leading-tight">{{ $navStudent->full_name }}</p>
                    <p class="text-code-sm font-data-mono text-on-surface-variant truncate">
                        {{ $navStudent->student_number }} @if($navStudent->schoolClass) • {{ $navStudent->schoolClass->class_name }} @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-space-sm py-2 space-y-1">
        @foreach ($navItems as $item)
            @php $isActive = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])); @endphp
            <a
                href="{{ route($item['route']) }}"
                title="{{ $item['label'] }}"
                @class([
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-body-md transition-colors group',
                    'bg-primary-container text-on-primary font-semibold shadow-sm' => $isActive,
                    'text-on-surface-variant hover:bg-surface-container-low hover:text-primary font-medium' => !$isActive,
                ])
                :class="(collapsed && !mobileOpen) ? 'justify-center px-0' : ''"
            >
                <span @class([
                    'material-symbols-outlined text-xl shrink-0',
                    'text-on-primary' => $isActive,
                    'text-outline group-hover:text-primary transition-colors' => !$isActive,
                ])>{{ $item['icon'] }}</span>

                <span class="whitespace-nowrap" x-show="!collapsed || mobileOpen" x-cloak>{{ $item['label'] }}</span>

                @if (!empty($item['badge']))
                    <span class="ml-auto text-code-sm font-data-mono font-semibold px-2 py-0.5 rounded-full bg-error/10 text-error"
                          x-show="!collapsed || mobileOpen" x-cloak>{{ $item['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Footer: collapse toggle + sign out --}}
    <div class="border-t border-outline-variant/40 p-space-sm space-y-1 shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Sign out"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-body-md font-medium text-on-surface-variant hover:bg-error-container/60 hover:text-on-error-container transition-colors group"
                :class="(collapsed && !mobileOpen) ? 'justify-center px-0' : ''">
                <span class="material-symbols-outlined text-xl text-outline group-hover:text-on-error-container shrink-0">logout</span>
                <span class="whitespace-nowrap" x-show="!collapsed || mobileOpen" x-cloak>Sign Out</span>
            </button>
        </form>

        <button type="button" @click="toggle()"
            class="hidden md:flex w-full items-center gap-3 px-3 py-2.5 rounded-lg text-body-md font-medium text-on-surface-variant hover:bg-surface-container-low hover:text-primary transition-colors group"
            :class="collapsed ? 'justify-center px-0' : ''"
            :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            :aria-expanded="(!collapsed).toString()">
            <span class="material-symbols-outlined text-xl text-outline group-hover:text-primary shrink-0"
                  x-text="collapsed ? 'chevron_right' : 'chevron_left'">chevron_left</span>
            <span class="whitespace-nowrap" x-show="!collapsed" x-cloak>Collapse</span>
        </button>
    </div>
</aside>
