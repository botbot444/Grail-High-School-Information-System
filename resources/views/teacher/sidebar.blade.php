<!-- Teacher Portal Navigation: admin visual system, teacher destinations -->
<aside id="sidebar"
    class="w-sidebar-width h-screen fixed left-0 top-0 bg-[#001a41] border-r border-[#2d476f] z-50 flex flex-col overflow-y-auto custom-scrollbar sidebar-transition">
    <div class="px-6 py-8 flex items-center gap-3 border-b border-white/10">
        <div class="w-10 h-10 bg-[#0059bb] rounded-lg flex items-center justify-center text-white shadow-sm">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1">school</span>
        </div>
        <div>
            <h1 class="text-title-sm font-title-sm font-bold text-white">Grail SIS</h1>
            <p class="text-[10px] uppercase tracking-[0.2em] text-[#bfc8d0] opacity-90">Teacher Portal</p>
        </div>
    </div>

    <nav class="flex-1 px-4 py-4 space-y-1">
        @php
            // Route map for the teacher portal — keeps the sidebar's visual markup
            // intact (icons / structure) while pointing each link at its own page route.
            $teacherNav = [
                'dashboard'    => ['route' => 'teacher.dashboard',    'icon' => 'dashboard',          'label' => 'Dashboard'],
                'classes'      => ['route' => 'teacher.classes',      'icon' => 'school',             'label' => 'My Classes'],
                'timetable'    => ['route' => 'teacher.timetable',    'icon' => 'calendar_view_day',  'label' => 'My Timetable'],
                'attendance'   => ['route' => 'teacher.attendance',   'icon' => 'how_to_reg',         'label' => 'Record Attendance'],
                'marks'        => ['route' => 'teacher.marks',        'icon' => 'edit_note',          'label' => 'Enter Marks'],
                'performance'  => ['route' => 'teacher.performance',  'icon' => 'query_stats',        'label' => 'Class Performance'],
                'announcements'=> ['route' => 'teacher.announcements','icon' => 'campaign',           'label' => 'Announcements'],
            ];
        @endphp

        @foreach ($teacherNav as $item)
            @php
                $routeName = $item['route'];
                $isActive  = request()->routeIs($routeName)
                    // Keep "Enter Marks" highlighted for the marks.store POST as well.
                    || ($routeName === 'teacher.marks' && request()->routeIs('teacher.marks.*'));
            @endphp
            <a class="{{ $isActive ? 'flex items-center gap-3 px-3 py-2.5 bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
                href="{{ route($routeName) }}">
                <span class="material-symbols-outlined"
                    style="{{ $isActive ? 'font-variation-settings: \"FILL\" 1' : '' }}">{{ $item['icon'] }}</span>
                <span class="font-label-sm text-label-sm">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="p-4 mt-auto">
        <div class="space-y-2">
            <p class="px-3 pt-1 pb-1 text-[10px] uppercase tracking-[0.2em] text-[#9fb2c6] opacity-80">Account</p>

            @php $settingsActive = request()->routeIs('teacher.settings'); @endphp
            <a class="{{ $settingsActive ? 'flex items-center gap-3 px-3 py-2.5 bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80' }} transition-colors duration-200 rounded-lg group"
                href="{{ route('teacher.settings') }}">
                <span class="material-symbols-outlined" style="{{ $settingsActive ? 'font-variation-settings: \"FILL\" 1' : '' }}">settings</span>
                <span class="font-label-sm text-label-sm">Settings</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" id="teacher-logout-form">
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
