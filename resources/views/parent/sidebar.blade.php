<!-- Parent Portal Navigation: admin visual system, parent-safe destinations only -->
<aside id="sidebar"
    class="w-sidebar-width h-screen fixed left-0 top-0 bg-[#001a41] border-r border-[#2d476f] z-50 flex flex-col overflow-y-auto custom-scrollbar sidebar-transition">
    <div class="px-6 py-8 flex items-center gap-3 border-b border-white/10">
        <div class="w-10 h-10 bg-[#0059bb] rounded-lg flex items-center justify-center text-white shadow-sm">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1">school</span>
        </div>
        <div>
            <h1 class="text-title-sm font-title-sm font-bold text-white">Grail SIS</h1>
            <p class="text-[10px] uppercase tracking-[0.2em] text-[#bfc8d0] opacity-90">Parent Portal</p>
        </div>
    </div>

    <nav class="flex-1 px-4 py-4 space-y-1">
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('parent.dashboard') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('parent.dashboard') }}">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="font-label-sm text-label-sm">Dashboard</span>
        </a>

        <p class="px-3 pt-4 pb-1 text-[10px] uppercase tracking-[0.2em] text-[#9fb2c6] opacity-80">My Family</p>

        @php
            // Route map for the "My Family" tabs — keeps the sidebar's visual markup
// intact (icons / structure) while pointing each link at its own page route.
$tabRoutes = [
    'children' => 'parent.children',
    'attendance' => 'parent.attendance',
    'performance' => 'parent.performance',
    'reports' => 'parent.reports',
    'assignments' => 'parent.assignments',
            ];
        @endphp

        @foreach (['children', 'attendance', 'performance', 'reports', 'assignments'] as $tab)
            @php
                $routeName = $tabRoutes[$tab] ?? 'parent.' . $tab;
                $icon = match ($tab) {
                    'children' => 'family_restroom',
                    'attendance' => 'how_to_reg',
                    'performance' => 'assignment_turned_in',
                    'reports' => 'description',
                    'assignments' => 'assignment',
                    default => 'label',
                };
                $labels = [
                    'children' => 'My Children',
                    'attendance' => 'Attendance',
                    'performance' => 'Performance',
                    'reports' => 'Reports',
                    'assignments' => 'Assignments',
                ];
                $label = $labels[$tab];
                $isActive = request()->routeIs($routeName);
            @endphp

            <a class="{{ $isActive ? 'flex items-center gap-3 px-3 py-2.5 bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
                href="{{ route($routeName) }}">
                <span class="material-symbols-outlined"
                    style="{{ $isActive ? 'font-variation-settings: \"FILL\" 1' : '' }}">{{ $icon }}</span>
                <span class="font-label-sm text-label-sm">{{ $label }}</span>
            </a>
        @endforeach

        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('parent.timetable') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('parent.timetable') }}"><span class="material-symbols-outlined">calendar_view_day</span><span
                class="font-label-sm text-label-sm">Timetable</span></a>

        <p class="px-3 pt-4 pb-1 text-[10px] uppercase tracking-[0.2em] text-[#9fb2c6] opacity-80">Account</p>
        @php
            $settingsActive = request()->routeIs('parent.settings');
        @endphp
        <a class="{{ $settingsActive ? 'flex items-center gap-3 px-3 py-2.5 bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80' }} transition-colors duration-200 rounded-lg group"
            href="{{ route('parent.settings') }}">
            <span class="material-symbols-outlined"
                style="{{ $settingsActive ? 'font-variation-settings: "FILL" 1' : '' }}">settings</span>
            <span class="font-label-sm text-label-sm">Settings</span>
        </a>
    </nav>

    <div class="p-4 mt-auto">
        <div class="space-y-2">
            <form method="POST" action="{{ route('logout') }}" id="parent-logout-form">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 transition-colors duration-200 rounded-lg group">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="font-label-sm text-label-sm">Sign Out</span>
                </button>
            </form>
        </div>
    </div>
</aside>
