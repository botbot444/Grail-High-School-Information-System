@extends('layouts.app')

@section('title', 'Student Profile - ' . $student->full_name)

@section('content')

    <div id="view-admin" class="app-view" style="display:flex;">

        @include('admin.sidebar')

        @include('admin.header')

        <!-- Main Content Area -->
        <main id="mainContent"
            class="ml-[260px] pt-[72px] min-h-screen bg-background p-container-padding main-transition flex-1">
            <!-- Breadcrumbs & Actions -->
            <div class="flex justify-between items-center mb-8">
                <nav class="flex items-center gap-2 text-on-surface-variant text-label-sm">
                    <a class="hover:text-primary transition-colors" href="{{ route('admin.students.index') }}">Students</a>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-on-surface font-semibold">{{ $student->full_name }}</span>
                </nav>
                <div class="flex gap-3">
                    <button onclick="window.print()"
                        class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                        <span class="material-symbols-outlined">print</span>
                        Print Profile
                    </button>
                    <a href="{{ route('admin.students.edit', $student->student_id) }}"
                        class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                        <span class="material-symbols-outlined">edit</span>
                        Edit Records
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-gutter">
                <div class="col-span-12 lg:col-span-12">
                    @include('students.profile-content', ['student' => $student, 'showFinancials' => true])
                </div>
            </div>
        </main>
    </div>
@endsection
