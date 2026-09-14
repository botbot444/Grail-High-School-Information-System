{{-- resources/views/admin/subjects/show.blade.php --}}
@extends('layouts.app')

@section('title', $subject->subject_name)

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
            <a href="{{ route('admin.subjects.index') }}" class="text-label-sm font-label-sm hover:text-primary transition-colors">Subjects</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="text-label-sm font-label-sm text-primary font-bold">{{ $subject->subject_name }}</span>
        </nav>

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-center">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shadow-sm">
                    <span class="material-symbols-outlined text-[28px]">menu_book</span>
                </div>
                <div>
                    <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">{{ $subject->subject_name }}</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant">Curriculum subject details and where it's taught.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.subjects.edit', $subject) }}"
                    class="flex items-center gap-2 px-4 py-2 bg-surface-container-low border border-outline-variant text-on-surface font-label-sm text-label-sm rounded-lg hover:bg-surface-container transition-colors">
                    <span class="material-symbols-outlined text-lg">edit</span>
                    Edit
                </a>
                <a href="{{ route('admin.subjects.index') }}"
                    class="flex items-center gap-2 px-4 py-2 bg-surface-container-low border border-outline-variant text-on-surface font-label-sm text-label-sm rounded-lg hover:bg-surface-container transition-colors">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Back
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter">
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary text-[20px]">groups</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">Classes Offering This Subject</h2>
                    <span class="ml-auto px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-label-sm font-semibold">
                        {{ $subject->classes_count }}
                    </span>
                </div>
                @forelse ($subject->classes as $class)
                    <a href="{{ route('admin.classes.show', $class) }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-surface-container-low transition-colors">
                        <span class="font-body-md text-body-md text-on-surface">{{ $class->class_name }}</span>
                        <span class="material-symbols-outlined text-[18px] text-on-surface-variant">chevron_right</span>
                    </a>
                @empty
                    <p class="text-body-sm font-body-sm text-on-surface-variant py-2">Not offered by any class yet.</p>
                @endforelse
            </div>

            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-secondary text-[20px]">badge</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">Teachers Assigned</h2>
                    <span class="ml-auto px-2.5 py-0.5 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-label-sm font-semibold">
                        {{ $subject->teachers_count }}
                    </span>
                </div>
                @forelse ($subject->teachers as $teacher)
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-surface-container-low transition-colors">
                        <div class="w-8 h-8 rounded-full bg-primary-fixed flex items-center justify-center text-on-primary-fixed-variant font-bold text-xs">
                            {{ strtoupper(substr($teacher->first_name, 0, 1) . substr($teacher->last_name, 0, 1)) }}
                        </div>
                        <span class="font-body-md text-body-md text-on-surface">{{ $teacher->full_name }}</span>
                    </div>
                @empty
                    <p class="text-body-sm font-body-sm text-on-surface-variant py-2">No teachers assigned to this subject yet.</p>
                @endforelse
            </div>
        </div>
    </main>
@endsection
