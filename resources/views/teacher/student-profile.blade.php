@extends('layouts.teacher')

@section('title', $student->full_name . ' - Student Profile')

@section('page')
    <div class="flex flex-col gap-space-lg">
        <div class="flex items-center justify-between gap-space-md">
            <nav class="flex items-center gap-2 text-on-surface-variant font-label-sm text-label-sm">
                <a class="hover:text-primary transition-colors" href="{{ route('teacher.classes') }}">My Classes</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <a class="hover:text-primary transition-colors" href="{{ route('teacher.classes.roster', $student->schoolClass->class_id) }}">{{ $student->schoolClass->display_name }} Roster</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span class="text-on-surface font-semibold">{{ $student->full_name }}</span>
            </nav>
            <button type="button" onclick="window.print()"
                class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Print Profile
            </button>
        </div>

        @include('students.profile-content', ['student' => $student, 'showFinancials' => false])
    </div>
@endsection
