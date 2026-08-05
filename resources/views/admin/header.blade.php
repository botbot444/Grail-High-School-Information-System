<!-- Top Bar -->
<header id="header"
    class="h-header-height fixed top-0 right-0 w-[calc(100%-260px)] z-40 bg-white border-b border-surface-container-high shadow-sm flex justify-between items-center px-container-padding header-transition">
    <div class="flex items-center gap-4 flex-1">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarToggle"
            class="toggle-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-all flex items-center justify-center"
            aria-label="Toggle sidebar">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <div class="relative w-full max-w-md">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
            <input
                class="w-full pl-10 pr-4 py-2 bg-surface-container border-none rounded-full text-body-md focus:ring-2 focus:ring-primary focus:bg-white transition-all"
                placeholder="Search student records, classes, or reports..." type="text" />
        </div>
    </div>
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.students.create') }}"
            class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg font-label-sm text-label-sm hover:opacity-80 transition-opacity active:scale-95">
            <span class="material-symbols-outlined">add_circle</span>
            Add New
        </a>
        <div class="flex items-center gap-2 border-l border-outline-variant pl-4">
            <button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-all">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-all">
                <span class="material-symbols-outlined">mail</span>
            </button>
            <div class="flex items-center gap-3 ml-2 cursor-pointer group">
                <div
                    class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold border-2 border-primary/20 group-hover:border-primary transition-all">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div class="hidden lg:block text-right">
                    <p class="font-label-sm text-label-sm font-bold text-on-surface leading-tight">
                        {{ Auth::user()->name }}
                    </p>
                    <p class="text-[10px] text-on-surface-variant">
                        Registrar Office
                    </p>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById("sidebar");
        const mainContent = document.getElementById("mainContent");
        const header = document.getElementById("header");
        const toggleBtn = document.getElementById("sidebarToggle");

        if (sidebar && mainContent && header && toggleBtn) {
            let sidebarVisible = true;

            function updateLayout() {
                if (sidebarVisible) {
                    sidebar.classList.remove("sidebar-collapsed");
                    mainContent.classList.remove("main-expanded");
                    header.classList.remove("header-expanded");
                    toggleBtn.querySelector(".material-symbols-outlined").textContent = "menu";
                } else {
                    sidebar.classList.add("sidebar-collapsed");
                    mainContent.classList.add("main-expanded");
                    header.classList.add("header-expanded");
                    toggleBtn.querySelector(".material-symbols-outlined").textContent = "menu_open";
                }
            }

            toggleBtn.addEventListener("click", function(e) {
                e.stopPropagation();
                sidebarVisible = !sidebarVisible;
                updateLayout();
            });

            updateLayout();
        }
    });
</script>
