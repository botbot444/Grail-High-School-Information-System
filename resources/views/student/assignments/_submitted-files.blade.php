@if ($submission->file_path || filled($submission->notes))
    <div class="space-y-2">
        <h3 class="text-label-form uppercase tracking-wider text-on-surface-variant">What you handed in</h3>

        @if ($submission->file_path)
            <a href="{{ Storage::disk('public')->url($submission->file_path) }}" target="_blank" rel="noopener"
               class="flex items-center gap-2.5 rounded-xl border border-outline-variant/60 px-3 py-2.5 hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined text-xl text-primary shrink-0">draft</span>
                <span class="min-w-0 flex-1 text-body-sm text-on-surface truncate">
                    {{ $submission->original_filename ?? basename($submission->file_path) }}
                </span>
                <span class="material-symbols-outlined text-lg text-outline shrink-0">open_in_new</span>
            </a>
        @endif

        @if (filled($submission->notes))
            <p class="rounded-xl bg-surface-container-low px-3 py-2.5 text-body-sm text-on-surface whitespace-pre-line">{{ $submission->notes }}</p>
        @endif
    </div>
@endif
