<!-- Teacher Portal Header: admin visual system, teacher-safe actions -->
<header id="header"
    class="h-header-height fixed top-0 right-0 w-[calc(100%-260px)] z-40 bg-surface-container-lowest border-b border-outline-variant shadow-sm flex justify-between items-center px-container-padding header-transition">
    <div class="flex items-center gap-4 flex-1">
        <button id="sidebarToggle"
            class="toggle-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-all flex items-center justify-center"
            aria-label="Toggle sidebar" onclick="toggleSidebar()">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 border-l border-outline-variant pl-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold border-2 border-primary/20">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="hidden lg:block text-right">
                    <p class="font-label-sm text-label-sm font-bold text-on-surface leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-on-surface-variant">Teacher Account</p>
                </div>
            </div>
        </div>
    </div>
</header>