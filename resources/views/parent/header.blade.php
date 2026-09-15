<!-- Parent Portal Header: admin visual system without admin-only actions -->
@php
    // Normalize the child list: controllers pass children as [['student' => ...], ...]
    $__students = collect();
    if (isset($children) && $children instanceof \Illuminate\Support\Collection) {
        // Accepts both shapes: summary arrays ([['student' => ...], ...]) and
        // raw Student collections passed straight from the controllers.
        $__students = $children
            ->map(fn ($c) => $c instanceof \App\Models\Student ? $c : ($c['student'] ?? null))
            ->filter()
            ->values();
    } elseif (isset($students) && $students instanceof \Illuminate\Support\Collection) {
        $__students = $students;
    }
    $__selectedChild = $selectedChild ?? null;
    $__studentCount  = $__students->count();
@endphp
<header id="header"
    class="h-header-height fixed top-0 right-0 w-[calc(100%-260px)] z-40 bg-surface-container-lowest border-b border-outline-variant shadow-sm flex justify-between items-center px-container-padding header-transition">
    <div class="flex items-center gap-4 flex-1">
        @if ($__studentCount > 1)
            @php
                $selectedId = $__selectedChild ? ($__selectedChild->student_id ?? null) : ($__students->first()->student_id ?? null);
                $selectedName = $__selectedChild ? $__selectedChild->full_name : ($__students->first()->full_name ?? 'Child');
            @endphp
            <div class="relative" id="childSwitcher">
                <button type="button"
                    class="flex items-center gap-2 px-3 py-1.5 bg-surface-container border border-outline-variant rounded-lg text-body-sm text-on-surface hover:bg-surface-container-high focus:ring-2 focus:ring-primary transition-all"
                    onclick="toggleChildSwitcher(event)">
                    <span class="material-symbols-outlined text-primary">group</span>
                    <span class="font-medium max-w-[140px] truncate">{{ $selectedName }}</span>
                    <span class="material-symbols-outlined text-xs text-on-surface-variant">arrow_downward</span>
                </button>
                <div id="childSwitcherMenu"
                    class="hidden absolute left-0 top-full mt-2 w-56 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg py-1 z-40">
                    <form method="POST" action="{{ route('parent.switch-child') }}">
                        @csrf
                        @foreach ($__students as $s)
                            <button type="submit" name="child_id" value="{{ $s->student_id }}"
                                class="{{ ($s->student_id == $selectedId ? 'bg-primary/10 text-primary font-semibold' : 'text-on-surface') }} block w-full text-left px-3 py-2 hover:bg-surface-container-high text-sm flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center text-[9px] font-bold">{{ strtoupper(substr($s->first_name ?? ($s->full_name ?? 'S'), 0, 2)) }}</span>
                                {{ $s->full_name }}
                            </button>
                        @endforeach
                    </form>
                </div>
            </div>
        @endif
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
                    <p class="text-[10px] text-on-surface-variant">Parent Account</p>
                </div>
            </div>
        </div>
    </div>
</header>