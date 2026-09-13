@extends('layouts.teacher')

@section('title', 'Subject Remarks – Teacher Portal')

@section('page')
    <a href="{{ route('teacher.marks') }}" class="inline-flex items-center gap-1.5 mb-4 font-label-md text-label-md text-primary hover:underline">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Enter Marks
    </a>

    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="font-headline-md text-headline-md text-primary font-bold">Subject Remarks</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                {{ $assignment->schoolClass?->class_name }} · {{ $assignment->subject?->subject_name }}
                · {{ $term->name }}
            </p>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <select name="term_id" onchange="this.form.submit()"
                class="rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                @foreach ($terms as $option)
                    <option value="{{ $option->term_id }}" @selected($term->term_id === $option->term_id)>
                        {{ $option->name }} ({{ $option->academicYear?->label }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if (session('notification'))
        <div class="mb-6 rounded-lg border border-secondary/30 bg-secondary-fixed px-4 py-3 font-body-md text-body-md text-on-surface">
            {{ session('notification') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-error/30 bg-error-container px-4 py-3">
            <ul class="list-disc ml-5 font-body-sm text-body-sm text-on-error-container space-y-1">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.report-cards.subjects.save', $assignment->class_subject_id) }}">
        @csrf
        <input type="hidden" name="term_id" value="{{ $term->term_id }}">

        <div class="rounded-xl border border-outline-variant/60 bg-surface-container-lowest overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant/50">
                <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">
                    {{ $students->count() }} student{{ $students->count() === 1 ? '' : 's' }}
                </h2>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                    One remark per student for this subject. Leave a box empty to remove an existing remark.
                </p>
            </div>

            <ul class="divide-y divide-outline-variant/40">
                @forelse ($students as $student)
                    <li class="px-5 py-3.5 flex flex-wrap items-start gap-4">
                        <div class="w-56 shrink-0">
                            <p class="font-title-sm text-title-sm text-on-surface">{{ $student->full_name }}</p>
                            <p class="font-data-mono text-code-md text-on-surface-variant mt-0.5">{{ $student->student_number }}</p>
                        </div>
                        <div class="flex-1 min-w-[260px]">
                            <label class="sr-only" for="comment-{{ $student->student_id }}">
                                Remark for {{ $student->full_name }}
                            </label>
                            <textarea name="comments[{{ $student->student_id }}]" id="comment-{{ $student->student_id }}" rows="2"
                                maxlength="1000"
                                class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40"
                                placeholder="e.g. Strong practical work; needs to show more working in tests.">{{ $comments->get($student->student_id)?->comment }}</textarea>
                        </div>
                    </li>
                @empty
                    <li class="px-5 py-12 text-center font-body-md text-body-md text-on-surface-variant">
                        No students are enrolled in this class.
                    </li>
                @endforelse
            </ul>
        </div>

        @if ($students->isNotEmpty())
            <div class="mt-5">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    <span>Save all remarks</span>
                </button>
            </div>
        @endif
    </form>
@endsection
