{{-- resources/views/admin/parents/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Parent Profile - ' . $parent->full_name)

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">

        @include('admin.sidebar')
        @include('admin.header')

        <main id="mainContent"
            class="ml-[260px] pt-[72px] min-h-screen bg-background p-container-padding main-transition flex-1">
            <div class="pb-12 max-w-[1400px] mx-auto">

                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider mb-6">
                    <a class="hover:text-primary transition-colors" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <a class="hover:text-primary transition-colors"
                        href="{{ route('admin.parents.index') }}">Parents &amp; Guardians</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-primary font-bold">{{ $parent->full_name }}</span>
                </nav>

                @php
                    $isActive = (bool) $parent->user?->is_active;
                    $mustReset = (bool) $parent->user?->must_change_password;
                    $childrenCount = $parent->students->count();
                @endphp

                @include('admin.partials.flash')

                <!-- Profile Header Card -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
                    <div class="h-24 bg-primary relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent"></div>
                    </div>
                    <div class="px-8 pb-8 flex flex-col md:flex-row md:items-end -mt-10 relative z-10 gap-6">
                        <div class="w-24 h-24 rounded-2xl border-4 border-surface-container-lowest shadow-xl bg-primary-container text-on-primary font-headline-md text-headline-md font-bold flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($parent->first_name, 0, 1) . substr($parent->last_name, 0, 1)) }}
                        </div>
                        <div class="flex-1 pb-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="font-display-md text-display-md text-on-surface">{{ $parent->full_name }}</h1>
                                @if ($mustReset)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 font-label-sm text-label-sm font-semibold uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                        Password Reset Required
                                    </span>
                                @elseif ($isActive)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-label-sm text-label-sm font-semibold uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm font-semibold uppercase">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                                Guardian &middot; {{ $childrenCount }} linked
                                {{ $childrenCount === 1 ? 'child' : 'children' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <a href="{{ route('admin.parents.index') }}"
                                class="px-4 h-9 rounded-lg bg-surface-container-low text-on-surface font-label-sm text-label-sm hover:bg-surface-container transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                                Back to Parents
                            </a>
                            <a href="{{ route('admin.parents.edit', $parent) }}"
                                class="px-4 h-9 rounded-lg bg-primary text-on-primary font-label-sm text-label-sm shadow-sm hover:bg-primary-container transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                Edit
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tabbed Content Area -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm min-h-[420px] flex flex-col mt-gutter">
                    <div class="flex px-4 border-b border-outline-variant bg-surface-container-low/30 overflow-x-auto whitespace-nowrap">
                        <button class="tab-btn active px-6 py-4 font-label-sm text-label-sm text-primary border-b-2 border-primary transition-all"
                            onclick="switchTab('overview')">
                            Overview
                        </button>
                        <button class="tab-btn px-6 py-4 font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-all"
                            onclick="switchTab('children')">
                            Linked Children ({{ $childrenCount }})
                        </button>
                        <button class="tab-btn px-6 py-4 font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-all"
                            onclick="switchTab('account')">
                            Account &amp; Security
                        </button>
                    </div>

                    <div class="p-8 flex-1">
                        <!-- Overview Panel -->
                        <div class="tab-panel" id="panel-overview">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low/60">
                                    <span class="material-symbols-outlined text-primary shrink-0 mt-0.5">mail</span>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-label-form text-label-form text-on-surface-variant uppercase">Email</span>
                                        <span class="font-label-md text-label-md text-on-surface font-semibold mt-0.5 truncate">{{ $parent->email }}</span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low/60">
                                    <span class="material-symbols-outlined text-primary shrink-0 mt-0.5">call</span>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-label-form text-label-form text-on-surface-variant uppercase">Phone</span>
                                        <span class="font-label-md text-label-md text-on-surface font-semibold mt-0.5">{{ $parent->phone ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="md:col-span-2 flex items-start gap-3 p-3 rounded-lg bg-surface-container-low/60">
                                    <span class="material-symbols-outlined text-primary shrink-0 mt-0.5">home_pin</span>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-label-form text-label-form text-on-surface-variant uppercase">Address</span>
                                        <span class="font-label-md text-label-md text-on-surface font-semibold mt-0.5">{{ $parent->address ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low/60">
                                    <span class="material-symbols-outlined text-primary shrink-0 mt-0.5">work</span>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-label-form text-label-form text-on-surface-variant uppercase">Occupation</span>
                                        <span class="font-label-md text-label-md text-on-surface font-semibold mt-0.5">{{ $parent->occupation ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low/60">
                                    <span class="material-symbols-outlined text-primary shrink-0 mt-0.5">badge</span>
                                    <div class="flex flex-col min-w-0">
                                        <span class="font-label-form text-label-form text-on-surface-variant uppercase">National ID</span>
                                        <span class="font-label-md text-label-md text-on-surface font-semibold mt-0.5 font-data-mono">{{ $parent->national_id ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Linked Children Panel -->
                        <div class="tab-panel hidden" id="panel-children">
                            @if ($childrenCount > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach ($parent->students as $child)
                                        <a href="{{ route('admin.students.show', $child->student_id) }}"
                                            class="group p-4 rounded-xl bg-surface-container-low hover:bg-surface-container transition-all flex items-center justify-between">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-11 h-11 rounded-full bg-primary text-on-primary flex items-center justify-center font-headline-sm text-headline-sm font-bold shrink-0">
                                                    {{ strtoupper(substr($child->first_name, 0, 1) . substr($child->last_name, 0, 1)) }}
                                                </div>
                                                <div class="flex flex-col min-w-0">
                                                    <span class="font-label-md text-label-md font-semibold text-on-surface group-hover:text-primary transition-colors truncate">
                                                        {{ $child->full_name }}
                                                    </span>
                                                    <span class="font-body-sm text-body-sm text-on-surface-variant">
                                                        {{ $child->schoolClass?->class_name ?? 'No Class' }} &middot;
                                                        <span class="font-data-mono">{{ $child->student_number }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors shrink-0">arrow_forward</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-20">
                                    <span class="material-symbols-outlined text-6xl text-surface-variant mb-4">family_restroom</span>
                                    <p class="text-on-surface-variant font-label-sm">No linked students.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Account & Security Panel -->
                        <div class="tab-panel hidden" id="panel-account">
                            <div class="max-w-md flex flex-col gap-4">
                                <div class="flex flex-col gap-2 bg-surface-container-low/60 p-4 rounded-xl">
                                    <div class="flex items-center justify-between">
                                        <span class="font-label-form text-label-form uppercase text-on-surface-variant">Linked Login</span>
                                        <span class="font-body-sm text-body-sm text-on-surface font-medium">{{ $parent->user?->email ?? 'No linked user' }}</span>
                                    </div>
                                    <div class="h-px bg-surface-container"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-label-form text-label-form uppercase text-on-surface-variant">Account Status</span>
                                        @if ($mustReset)
                                            <span class="font-label-sm text-label-sm text-amber-700 font-semibold">Password Reset Required</span>
                                        @elseif ($isActive)
                                            <span class="font-label-sm text-label-sm text-emerald-700 font-semibold">Active</span>
                                        @else
                                            <span class="font-label-sm text-label-sm text-on-surface-variant font-semibold">Inactive</span>
                                        @endif
                                    </div>
                                    <div class="h-px bg-surface-container"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="font-label-form text-label-form uppercase text-on-surface-variant">Member Since</span>
                                        <span class="font-body-sm text-body-sm text-on-surface">{{ $parent->user?->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                @if ($parent->user)
                                    <form action="{{ route('admin.users.reset-password', $parent->user_id) }}" method="POST"
                                        onsubmit="return confirm('Issue a new temporary password for {{ $parent->full_name }}? Their current password will stop working immediately.');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit"
                                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100/80 text-amber-900 font-label-md text-label-md transition-colors border border-amber-200">
                                            <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                                            <span>Reset Password</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-panel').forEach((panel) => panel.classList.add('hidden'));
            document.getElementById('panel-' + tabId)?.classList.remove('hidden');

            document.querySelectorAll('.tab-btn').forEach((btn) => {
                btn.classList.remove('text-primary', 'border-b-2', 'border-primary', 'active');
                btn.classList.add('text-on-surface-variant');
            });

            const activeBtn = Array.from(document.querySelectorAll('.tab-btn'))
                .find((btn) => btn.getAttribute('onclick')?.includes(tabId));
            if (activeBtn) {
                activeBtn.classList.remove('text-on-surface-variant');
                activeBtn.classList.add('text-primary', 'border-b-2', 'border-primary', 'active');
            }
        }
    </script>
@endpush
