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

        @foreach ([
            ['children', 'family_restroom', 'My Children'],
            ['attendance', 'how_to_reg', 'Attendance'],
            ['performance', 'assignment_turned_in', 'Performance'],
            ['reports', 'description', 'Reports'],
            ['assignments', 'assignment', 'Assignments'],
        ] as [$tab, $icon, $label])
            <a class="parent-tab-link flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 transition-colors duration-200 rounded-lg group"
                href="#tab-{{ $tab }}" data-parent-tab="{{ $tab }}">
                <span class="material-symbols-outlined">{{ $icon }}</span>
                <span class="font-label-sm text-label-sm">{{ $label }}</span>
            </a>
        @endforeach

        <p class="px-3 pt-4 pb-1 text-[10px] uppercase tracking-[0.2em] text-[#9fb2c6] opacity-80">Account</p>
        <a class="parent-tab-link flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 transition-colors duration-200 rounded-lg group"
            href="#tab-settings" data-parent-tab="settings">
            <span class="material-symbols-outlined">settings</span>
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
