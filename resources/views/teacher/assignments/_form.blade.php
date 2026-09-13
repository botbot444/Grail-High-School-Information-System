@csrf
@if ($mode === 'edit')
    @method('PUT')
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div class="md:col-span-2">
        <label for="title" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Title</label>
        <input type="text" name="title" id="title" required
            value="{{ old('title', $assignment->title) }}"
            class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
    </div>

    <div>
        <label for="class_subject_id" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Class &amp; subject</label>
        <select name="class_subject_id" id="class_subject_id" required
            class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
            <option value="">Choose…</option>
            @foreach ($classSubjects as $cs)
                <option value="{{ $cs->class_subject_id }}" @selected(old('class_subject_id', $assignment->class_subject_id) == $cs->class_subject_id)>
                    {{ $cs->schoolClass?->class_name }} — {{ $cs->subject?->subject_name }}
                </option>
            @endforeach
        </select>
        @if ($classSubjects->isEmpty())
            <p class="mt-1.5 font-body-sm text-body-sm text-error">You are not assigned to any class subjects yet.</p>
        @endif
    </div>

    <div>
        <label for="term_id" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Term</label>
        <select name="term_id" id="term_id"
            class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
            <option value="">Not term-specific</option>
            @foreach ($terms as $term)
                <option value="{{ $term->term_id }}" @selected(old('term_id', $assignment->term_id) == $term->term_id)>
                    {{ $term->name }} ({{ $term->academicYear?->label }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="due_at" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Due</label>
        <input type="datetime-local" name="due_at" id="due_at" required
            value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\TH:i')) }}"
            class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
    </div>

    <div>
        <label for="max_score" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Maximum marks</label>
        <input type="number" name="max_score" id="max_score" step="0.5" min="1" max="1000" required
            value="{{ old('max_score', $assignment->max_score ?? 100) }}"
            class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
    </div>

    <div class="md:col-span-2">
        <label for="instructions" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Instructions</label>
        <textarea name="instructions" id="instructions" rows="8"
            class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40"
            placeholder="What the students need to do, and how it will be marked.">{{ old('instructions', $assignment->instructions) }}</textarea>
    </div>

    <div class="md:col-span-2 flex flex-wrap items-center gap-6">
        <label class="inline-flex items-center gap-2.5 cursor-pointer">
            <input type="hidden" name="allows_file_upload" value="0">
            <input type="checkbox" name="allows_file_upload" value="1"
                @checked(old('allows_file_upload', $assignment->allows_file_upload ?? true))
                class="rounded border-outline-variant text-secondary focus:ring-secondary/40">
            <span class="font-body-md text-body-md text-on-surface">Allow file uploads</span>
        </label>

        <div class="flex items-center gap-2.5">
            <span class="font-label-form text-label-form uppercase tracking-wider text-on-surface-variant">Visibility</span>
            @foreach ([\App\Models\Assignment::STATUS_DRAFT => 'Draft', \App\Models\Assignment::STATUS_PUBLISHED => 'Published'] as $value => $label)
                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" name="status" value="{{ $value }}"
                        @checked(old('status', $assignment->status ?? \App\Models\Assignment::STATUS_DRAFT) === $value)
                        class="border-outline-variant text-secondary focus:ring-secondary/40">
                    <span class="font-body-md text-body-md text-on-surface">{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>

<p class="mt-3 font-body-sm text-body-sm text-on-surface-variant">
    Drafts are invisible to students. Publishing makes the assignment appear in their portal immediately.
</p>

<div class="mt-8 flex items-center gap-3">
    <button type="submit"
        class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
        <span class="material-symbols-outlined text-[18px]">save</span>
        <span>{{ $mode === 'edit' ? 'Save changes' : 'Create assignment' }}</span>
    </button>
    <a href="{{ route('teacher.assignments.index') }}"
        class="px-space-md py-2 rounded-lg font-title-sm text-title-sm text-on-surface-variant hover:bg-surface-container transition-colors">Cancel</a>
</div>
