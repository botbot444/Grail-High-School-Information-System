{{-- resources/views/admin/classes/show.blade.php --}}
@extends('layouts.app')

@section('title', $class->class_name)

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
            <a href="{{ route('admin.classes.index') }}" class="text-label-sm font-label-sm hover:text-primary transition-colors">Classes</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-label-sm font-label-sm text-primary font-bold">{{ $class->class_name }}</span>
        </nav>

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-center">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shadow-sm">
                    <span class="material-symbols-outlined text-[28px]">meeting_room</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">{{ $class->class_name }}</h1>
                        @if ($class->teacher_id)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-label-sm text-label-sm font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container font-label-sm text-label-sm font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                                Needs Teacher
                            </span>
                        @endif
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant">{{ $class->grade_level_name }} &middot; Class details, homeroom teacher, and assigned subjects.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.classes.edit', $class) }}"
                    class="flex items-center gap-2 px-4 py-2 bg-surface-container-low border border-outline-variant text-on-surface font-label-sm text-label-sm rounded-lg hover:bg-surface-container transition-colors">
                    <span class="material-symbols-outlined text-lg">edit</span>
                    Edit
                </a>
                <a href="{{ route('admin.classes.index') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-surface-container-low border border-outline-variant text-on-surface font-label-sm text-label-sm rounded-lg hover:bg-surface-container transition-colors">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Back
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mb-6">
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-secondary-fixed flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">groups</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Students Enrolled</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $class->students_count }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-tertiary-fixed flex items-center justify-center text-tertiary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">menu_book</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Subjects Offered</p>
                    <p class="font-headline-md text-headline-md font-bold">{{ $class->subjects->count() }}</p>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary-fixed flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">badge</span>
                </div>
                <div>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Homeroom Teacher</p>
                    <p class="font-headline-sm text-headline-sm font-bold">{{ $class->teacher->full_name ?? 'Unassigned' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter">
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-6">
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="font-label-sm text-label-sm text-on-surface-variant font-semibold">Class Name</dt>
                        <dd class="font-body-md text-body-md text-on-surface mt-0.5">{{ $class->class_name }}</dd>
                    </div>
                    <div>
                        <dt class="font-label-sm text-label-sm text-on-surface-variant font-semibold">Grade Level</dt>
                        <dd class="font-body-md text-body-md text-on-surface mt-0.5">{{ $class->grade_level_name }}</dd>
                    </div>
                    <div>
                        <dt class="font-label-sm text-label-sm text-on-surface-variant font-semibold">Homeroom Teacher</dt>
                        <dd class="font-body-md text-body-md text-on-surface mt-0.5">
                            @if ($class->teacher)
                                {{ $class->teacher->full_name }} &middot; {{ $class->teacher->email }}
                            @else
                                <span class="text-error italic">Unassigned</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="font-label-sm text-label-sm text-on-surface-variant font-semibold">Last Updated</dt>
                        <dd class="font-body-md text-body-md text-on-surface mt-0.5">{{ $class->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
                <a href="{{ route('admin.students.index', ['class_id' => $class->class_id]) }}"
                    class="mt-5 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-surface-container-low text-primary hover:bg-surface-container font-label-md text-label-md font-semibold transition-colors">
                    <span class="material-symbols-outlined text-[18px]">group</span>
                    View Class Roster
                </a>
            </div>

            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary text-[20px]">menu_book</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">Assigned Subjects</h2>
                </div>
                @forelse ($class->subjects as $subject)
                    <a href="{{ route('admin.subjects.show', $subject) }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-surface-container-low transition-colors">
                        <span class="font-body-md text-body-md text-on-surface">{{ $subject->subject_name }}</span>
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">chevron_right</span>
                    </a>
                @empty
                    <p class="text-body-sm font-body-sm text-on-surface-variant py-2">No subjects assigned yet.</p>
                @endforelse
            </div>
        </div>
    </main>
@endsection
