@extends('layouts.teacher')

@section('title', 'Assignments – Teacher Portal')

@section('page')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-md text-headline-md text-primary font-bold">Assignments</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Work you have set for your classes.</p>
        </div>
        <a href="{{ route('teacher.assignments.create') }}"
            class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
            <span class="material-symbols-outlined text-[18px]">add</span>
            <span>New assignment</span>
        </a>
    </div>


    @if ($assignments->isEmpty())
        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest py-16 text-center">
            <span class="material-symbols-outlined text-[40px] text-outline">assignment</span>
            <p class="mt-3 font-headline-sm text-headline-sm text-on-surface">No assignments yet</p>
            <p class="mt-1 font-body-md text-body-md text-on-surface-variant">Create one to give your classes work to hand in.</p>
        </div>
    @else
        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full font-body-md text-body-md">
                    <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                        <tr>
                            <th class="text-left font-medium px-4 py-3">Assignment</th>
                            <th class="text-left font-medium px-4 py-3">Class · Subject</th>
                            <th class="text-left font-medium px-4 py-3">Due</th>
                            <th class="text-center font-medium px-4 py-3">Status</th>
                            <th class="text-center font-medium px-4 py-3">Handed in</th>
                            <th class="text-right font-medium px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @foreach ($assignments as $assignment)
                            <tr class="hover:bg-surface-container-low/60 transition-colors">
                                <td class="px-4 py-3 font-medium text-on-surface">{{ $assignment->title }}</td>
                                <td class="px-4 py-3 text-on-surface-variant">
                                    {{ $assignment->classSubject?->schoolClass?->class_name }} ·
                                    {{ $assignment->classSubject?->subject?->subject_name }}
                                </td>
                                <td class="px-4 py-3 {{ $assignment->isPastDue() ? 'text-error' : 'text-on-surface-variant' }}">
                                    {{ $assignment->due_at->format('j M Y, H:i') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-1 rounded-lg font-label-sm text-label-sm font-semibold',
                                        'bg-tertiary-fixed text-on-surface-variant' => ! $assignment->isPublished(),
                                        'bg-secondary-fixed text-on-secondary-container' => $assignment->isPublished(),
                                    ])>{{ $assignment->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-center font-data-mono text-data-mono">
                                    {{ $assignment->submissions_count }}
                                    <span class="text-outline">({{ $assignment->graded_count }} marked)</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('teacher.assignments.submissions', $assignment->assignment_id) }}"
                                            title="Submissions" class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">fact_check</span>
                                        </a>
                                        <a href="{{ route('teacher.assignments.edit', $assignment->assignment_id) }}"
                                            title="Edit" class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('teacher.assignments.destroy', $assignment->assignment_id) }}"
                                              onsubmit="return confirm('Remove “{{ $assignment->title }}”? Submissions are kept in the audit trail.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Remove"
                                                class="p-2 rounded-lg text-on-surface-variant hover:bg-error-container hover:text-error transition-colors">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
