<!-- Sidebar Navigation -->
<aside id="sidebar"
    class="w-sidebar-width h-screen fixed left-0 top-0 bg-[#001a41] border-r border-[#2d476f] z-50 flex flex-col overflow-y-auto custom-scrollbar sidebar-transition">
    <div class="px-6 py-8 flex items-center gap-3 border-b border-white/10">
        <div class="w-10 h-10 bg-[#0059bb] rounded-lg flex items-center justify-center text-white shadow-sm">
            <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">school</span>
        </div>
        <div>
            <h1 class="text-title-sm font-title-sm font-bold text-white">
                Grail SIS
            </h1>
            <p class="text-[10px] uppercase tracking-[0.2em] text-[#bfc8d0] opacity-90">
                Admin Portal
            </p>
        </div>
    </div>
    <nav class="flex-1 px-4 py-4 space-y-1">
        <!-- Dashboard -->
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('admin.dashboard') }}">
            <span class="material-symbols-outlined"
                style="{{ request()->routeIs('admin.dashboard') ? 'font-variation-settings: "FILL" 1' : '' }}">dashboard</span>
            <span class="font-label-sm text-label-sm">Dashboard</span>
        </a>

        <!-- User Management -->
        <a class="flex items-center gap-3 px-3 py-2.5 text-tertiary-fixed-dim hover:bg-on-primary-fixed-variant transition-colors duration-200 rounded-lg group"
            href="#">
            <span class="material-symbols-outlined">group</span>
            <span class="font-label-sm text-label-sm">User Management</span>
        </a>

        <!-- Students -->
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.students.*') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('admin.students.index') }}">
            <span class="material-symbols-outlined"
                style="{{ request()->routeIs('admin.students.*') ? 'font-variation-settings: "FILL" 1' : '' }}">school</span>
            <span class="font-label-sm text-label-sm">Students</span>
        </a>

        <!-- Teachers -->
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.teachers.*') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('admin.teachers.index') }}">
            <span class="material-symbols-outlined"
                style="{{ request()->routeIs('admin.teachers.*') ? 'font-variation-settings: "FILL" 1' : '' }}">person_pin</span>
            <span class="font-label-sm text-label-sm">Teachers</span>
        </a>

        <!-- Parents -->
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.parents.*') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('admin.parents.index') }}">
            <span class="material-symbols-outlined"
                style="{{ request()->routeIs('admin.parents.*') ? 'font-variation-settings: "FILL" 1' : '' }}">family_restroom</span>
            <span class="font-label-sm text-label-sm">Parents</span>
        </a>

        <!-- Classes -->
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.classes.*') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('admin.classes.index') }}">
            <span class="material-symbols-outlined"
                style="{{ request()->routeIs('admin.classes.*') ? 'font-variation-settings: "FILL" 1' : '' }}">groups</span>
            <span class="font-label-sm text-label-sm">Classes</span>
        </a>

        <!-- Subjects -->
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.subjects.*') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('admin.subjects.index') }}">
            <span class="material-symbols-outlined"
                style="{{ request()->routeIs('admin.subjects.*') ? 'font-variation-settings: "FILL" 1' : '' }}">book</span>
            <span class="font-label-sm text-label-sm">Subjects</span>
        </a>

        <!-- Placeholder items -->
        <a class="flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 transition-colors duration-200 rounded-lg group"
            href="#">
            <span class="material-symbols-outlined">calendar_month</span>
            <span class="font-label-sm text-label-sm">Timetables</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 transition-colors duration-200 rounded-lg group"
            href="#">
            <span class="material-symbols-outlined">how_to_reg</span>
            <span class="font-label-sm text-label-sm">Attendance</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.examinations') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('admin.examinations') }}">
            <span class="material-symbols-outlined"
                style="{{ request()->routeIs('admin.examinations') ? 'font-variation-settings: "FILL" 1' : '' }}">assignment_turned_in</span>
            <span class="font-label-sm text-label-sm">Examinations</span>
        </a>

        <!-- Fees -->
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.fees.*') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('admin.fees.index') }}">
            <span class="material-symbols-outlined"
                style="{{ request()->routeIs('admin.fees.*') ? 'font-variation-settings: "FILL" 1' : '' }}">payments</span>
            <span class="font-label-sm text-label-sm">Fees</span>
        </a>

        <!-- Audit Logs -->
        <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
            href="{{ route('admin.audit-logs.index') }}">
            <span class="material-symbols-outlined"
                style="{{ request()->routeIs('admin.audit-logs.*') ? 'font-variation-settings: "FILL" 1' : '' }}">history</span>
            <span class="font-label-sm text-label-sm">Audit Logs</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 transition-colors duration-200 rounded-lg group"
            href="#">
            <span class="material-symbols-outlined">analytics</span>
            <span class="font-label-sm text-label-sm">Reports</span>
        </a>
    </nav>
    <div class="p-4 mt-auto">
        <div class="space-y-2">
            <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.settings') ? 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm' : 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg' }} transition-colors duration-200 group"
                href="{{ route('admin.settings') }}">
                <span class="material-symbols-outlined"
                    style="{{ request()->routeIs('admin.settings') ? 'font-variation-settings: "FILL" 1' : '' }}">settings</span>
                <span class="font-label-sm text-label-sm">Settings</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" id="logout-form" style="display: none;">
                @csrf
            </form>
            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="w-full flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 transition-colors duration-200 rounded-lg group">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-label-sm text-label-sm">Sign Out</span>
            </button>
        </div>
    </div>
</aside>
