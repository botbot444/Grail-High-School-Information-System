@extends('layouts.app')

@section('title', 'Promote ' . $schoolClass->class_name)

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')


        <a href="{{ route('admin.promotions.index') }}"
           class="inline-flex items-center gap-1.5 mb-4 font-label-md text-label-md text-primary hover:underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span> All classes
        </a>

        <div class="mb-6">
            <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                Promote {{ $schoolClass->class_name }}
            </h1>
            <p class="font-body-md text-body-md text-on-surface-variant">
                {{ $schoolClass->gradeLevel?->name ?? 'No grade level' }} ·
                {{ $students->count() }} enrolled student{{ $students->count() === 1 ? '' : 's' }}
                @if ($targetYear) · moving into {{ $targetYear->label }} @endif
            </p>
        </div>


        @if (! empty($blockers))
            <div class="rounded-xl border border-error/30 bg-error-container px-5 py-4">
                <p class="font-title-sm text-title-sm text-on-error-container">Promotion cannot run yet</p>
                <ul class="mt-2 ml-5 list-disc font-body-sm text-body-sm text-on-error-container space-y-1">
                    @foreach ($blockers as $blocker)<li>{{ $blocker }}</li>@endforeach
                </ul>
            </div>
        @elseif ($students->isEmpty())
            <div class="rounded-xl border border-outline-variant bg-surface-container-lowest py-16 text-center">
                <span class="material-symbols-outlined text-[40px] text-outline">groups</span>
                <p class="mt-3 font-headline-sm text-headline-sm text-on-surface">Nobody to promote</p>
                <p class="mt-1 font-body-md text-body-md text-on-surface-variant">
                    This class has no enrolled students.
                </p>
            </div>
        @else
            <form method="POST" action="{{ route('admin.promotions.store', $schoolClass->class_id) }}"
                  x-data="promotionRun()"
                  onsubmit="return confirm('Commit this promotion? Every student below moves as selected.');">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $targetYear?->year_id }}">

                {{-- Bulk controls --}}
                <div class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm p-5 mb-5">
                    <p class="font-title-sm text-title-sm text-on-surface mb-1">Apply to everyone</p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mb-3">
                        Set the whole class at once, then change individuals below. Students staying behind
                        should be marked <strong>Retained</strong> — they keep their current class.
                    </p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="setAll('promoted')"
                            class="px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container hover:text-primary font-label-md text-label-md transition-colors">
                            All promoted
                        </button>
                        <button type="button" @click="setAll('retained')"
                            class="px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container hover:text-primary font-label-md text-label-md transition-colors">
                            All retained
                        </button>
                        <button type="button" @click="setAll('graduated')"
                            class="px-3 py-2 rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container hover:text-primary font-label-md text-label-md transition-colors">
                            All graduating
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full font-body-md text-body-md">
                            <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                                <tr>
                                    <th class="text-left font-medium px-4 py-3">Student</th>
                                    <th class="text-left font-medium px-4 py-3">Outcome</th>
                                    <th class="text-left font-medium px-4 py-3">Moves to</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/40">
                                @foreach ($students as $student)
                                    @php $sid = $student->student_id; @endphp
                                    <tr class="hover:bg-surface-container-low/60 transition-colors align-top"
                                        x-data="{ outcome: @js(old('students.'.$sid.'.outcome', $default['outcome'])) }"
                                        x-init="register({{ $sid }}, () => outcome, v => outcome = v)">
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-on-surface">{{ $student->full_name }}</p>
                                            <p class="font-data-mono text-code-md text-on-surface-variant">{{ $student->student_number }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-3">
                                                @foreach ([
                                                    'promoted'  => 'Promote',
                                                    'retained'  => 'Retain',
                                                    'graduated' => 'Graduate',
                                                ] as $value => $label)
                                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                                        <input type="radio" name="students[{{ $sid }}][outcome]"
                                                            value="{{ $value }}" x-model="outcome"
                                                            class="border-outline-variant text-secondary focus:ring-secondary/40">
                                                        <span class="font-body-md text-body-md text-on-surface">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div x-show="outcome === 'promoted'">
                                                <select name="students[{{ $sid }}][to_class_id]"
                                                    class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                                                    <option value="">Choose a class…</option>
                                                    @foreach ($classes as $option)
                                                        <option value="{{ $option->class_id }}"
                                                            @selected(old('students.'.$sid.'.to_class_id', $default['to_class_id']) == $option->class_id)>
                                                            {{ $option->class_name }}@if ($option->gradeLevel) — {{ $option->gradeLevel->name }}@endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <p x-show="outcome === 'retained'" x-cloak class="font-body-sm text-body-sm text-on-surface-variant">
                                                Stays in {{ $schoolClass->class_name }}
                                            </p>
                                            <p x-show="outcome === 'graduated'" x-cloak class="font-body-sm text-body-sm text-on-surface-variant">
                                                Leaves the school — record kept, portal access removed
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                        <span class="material-symbols-outlined text-[18px]">arrow_upward</span>
                        <span>Run promotion</span>
                    </button>
                    <a href="{{ route('admin.promotions.index') }}"
                        class="px-space-md py-2 rounded-lg font-title-sm text-title-sm text-on-surface-variant hover:bg-surface-container transition-colors">Cancel</a>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function promotionRun() {
    return {
        rows: [],
        // Each student row registers a setter so the bulk buttons can drive them
        // without the parent needing to know the row structure.
        register(id, getter, setter) {
            this.rows.push({ id, getter, setter });
        },
        setAll(outcome) {
            this.rows.forEach(row => row.setter(outcome));
        },
    };
}
</script>
@endpush
