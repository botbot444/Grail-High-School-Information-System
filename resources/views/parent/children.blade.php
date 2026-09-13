@extends('layouts.parent')

@section('title', 'My Children – Parent Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Page header ── --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">My Children</h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    Academic progress, attendance and fee standing for each child
                    @if ($currentTerm)
                        · {{ $currentTerm->name ?? 'Current Term' }}@if ($currentTerm->academicYear)
                            · {{ $currentTerm->academicYear->label ?? '' }}
                        @endif
                    @endif
                </p>
            </div>
            <span class="text-xs font-semibold text-on-surface-variant bg-surface-container px-2 py-1 rounded flex items-center gap-1 self-start">
                <span class="material-symbols-outlined" style="font-size:14px">family_restroom</span>
                {{ $children->count() }} enrolled
            </span>
        </div>

        {{-- ── Children cards ── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($children as $child)
                @php $s = $child['student']; @endphp
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm hover:border-primary hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                    <div class="p-5 flex gap-4">
                        <div class="relative flex-shrink-0">
                            <div class="w-20 h-20 rounded-xl bg-primary/10 flex items-center justify-center">
                                <span class="text-primary text-2xl font-extrabold">{{ strtoupper(substr($s->full_name ?? 'S', 0, 2)) }}</span>
                            </div>
                            <div class="absolute -bottom-1 -right-1 bg-green-500 border-2 border-white w-6 h-6 rounded-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-white text-[14px]" style="font-variation-settings: 'FILL' 1">check</span>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface">{{ $s->full_name }}</h3>
                            <p class="text-sm text-on-surface-variant mt-0.5">
                                {{ $s->schoolClass?->display_name ?? ($s->schoolClass?->class_name ?? '—') }}
                                @if ($s->schoolClass?->gradeLevel)
                                    · Grade {{ $s->schoolClass->gradeLevel->level ?? $s->schoolClass->gradeLevel->name ?? '' }}
                                @endif
                            </p>
                            <p class="text-xs text-on-surface-variant mt-0.5">
                                Class teacher: {{ $s->schoolClass?->teacher?->name ?? 'Not assigned' }}
                            </p>
                            <div class="flex gap-2 mt-2">
                                <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded {{ ($child['fee_status'] ?? 'Cleared') === 'Cleared' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                    Fees {{ $child['fee_status'] ?? 'Cleared' }}
                                </span>
                                @if (($child['pending'] ?? 0) > 0)
                                    <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded bg-blue-50 text-primary">
                                        {{ $child['pending'] }} pending
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Stats row --}}
                    <div class="grid grid-cols-3 border-t border-outline-variant divide-x divide-outline-variant">
                        <div class="p-4 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Attendance</p>
                            <p class="text-lg font-extrabold text-on-surface mt-1">{{ $child['attendance_rate'] }}%</p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">GPA</p>
                            <p class="text-lg font-extrabold text-on-surface mt-1">{{ $child['gpa'] }}</p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Balance</p>
                            <p class="text-lg font-extrabold {{ ($child['fee_balance'] ?? 0) > 0 ? 'text-error' : 'text-green-700' }} mt-1">
                                ZMW {{ number_format($child['fee_balance'] ?? 0, 2) }}
                            </p>
                        </div>
                    </div>

                    {{-- Quick links --}}
                    <div class="flex flex-wrap gap-2 px-5 py-3 bg-surface-container border-t border-outline-variant">
                        <a href="{{ route('parent.attendance', ['child_id' => $s->student_id]) }}"
                            class="text-xs font-semibold text-primary hover:bg-primary/10 px-2.5 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:14px">how_to_reg</span> Attendance
                        </a>
                        <a href="{{ route('parent.performance', ['child_id' => $s->student_id]) }}"
                            class="text-xs font-semibold text-primary hover:bg-primary/10 px-2.5 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:14px">assignment_turned_in</span> Performance
                        </a>
                        <a href="{{ route('parent.reports', ['child_id' => $s->student_id]) }}"
                            class="text-xs font-semibold text-primary hover:bg-primary/10 px-2.5 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:14px">description</span> Reports
                        </a>
                        <a href="{{ route('parent.fees', ['child_id' => $s->student_id]) }}"
                            class="text-xs font-semibold text-primary hover:bg-primary/10 px-2.5 py-1.5 rounded flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:14px">payments</span> Fees
                        </a>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 bg-surface-container-lowest border border-outline-variant rounded-xl p-10 text-center">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant">family_restroom</span>
                    <p class="font-label-sm text-label-sm text-on-surface mt-2">No children linked to your account yet</p>
                    <p class="text-sm text-on-surface-variant mt-1">Please contact the school office to link your children.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
