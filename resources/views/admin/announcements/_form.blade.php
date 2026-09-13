@csrf
@if ($mode === 'edit')
    @method('PUT')
@endif

{{--
    Audience selection drives which target list is shown, and the reach preview
    re-queries as the selection changes so the admin can check the aim before
    publishing. Alpine is already loaded globally.
--}}
<div
    x-data="announcementForm({
        audience: @js(old('audience', $announcement->audience ?? 'all')),
        classIds: @js(array_map('strval', old('class_ids', $selectedClasses))),
        levelIds: @js(array_map('strval', old('grade_level_ids', $selectedLevels))),
        previewUrl: @js(route('admin.announcements.preview')),
    })"
    x-init="init()"
>
    <div class="grid grid-cols-1 gap-5">
        <div>
            <label for="title" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Title</label>
            <input type="text" name="title" id="title" required maxlength="200"
                value="{{ old('title', $announcement->title) }}"
                class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
        </div>

        <div>
            <label for="body" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Message</label>
            <textarea name="body" id="body" rows="7" required maxlength="10000"
                class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40"
                placeholder="What do families need to know?">{{ old('body', $announcement->body) }}</textarea>
        </div>

        {{-- Audience --}}
        <fieldset>
            <legend class="font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-2">Audience</legend>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                @foreach ($audiences as $value => $label)
                    <label class="flex items-start gap-2.5 rounded-lg border border-outline-variant/60 px-3 py-2.5 cursor-pointer hover:bg-surface-container transition-colors"
                           :class="audience === '{{ $value }}' ? 'border-secondary bg-secondary-fixed' : ''">
                        <input type="radio" name="audience" value="{{ $value }}" x-model="audience"
                            class="mt-0.5 border-outline-variant text-secondary focus:ring-secondary/40">
                        <span class="font-body-md text-body-md text-on-surface">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        {{-- Class targets --}}
        <div x-show="audience === 'class'" x-cloak>
            <label class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">
                Classes &mdash; choose one or more
            </label>
            <div class="max-h-52 overflow-y-auto rounded-lg border border-outline-variant/60 p-3 grid grid-cols-1 sm:grid-cols-3 gap-1.5">
                @forelse ($classes as $class)
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-surface-container cursor-pointer">
                        <input type="checkbox" name="class_ids[]" value="{{ $class->class_id }}"
                            x-model="classIds"
                            class="rounded border-outline-variant text-secondary focus:ring-secondary/40">
                        <span class="font-body-md text-body-md text-on-surface">{{ $class->class_name }}</span>
                        @if ($class->gradeLevel)
                            <span class="font-body-sm text-body-sm text-on-surface-variant">· {{ $class->gradeLevel->name }}</span>
                        @endif
                    </label>
                @empty
                    <p class="font-body-sm text-body-sm text-on-surface-variant">No classes have been created yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Grade level targets --}}
        <div x-show="audience === 'grade_level'" x-cloak>
            <label class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">
                Grade levels &mdash; reaches every class in the level
            </label>
            <div class="rounded-lg border border-outline-variant/60 p-3 grid grid-cols-1 sm:grid-cols-3 gap-1.5">
                @forelse ($gradeLevels as $level)
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-surface-container cursor-pointer">
                        <input type="checkbox" name="grade_level_ids[]" value="{{ $level->grade_level_id }}"
                            x-model="levelIds"
                            class="rounded border-outline-variant text-secondary focus:ring-secondary/40">
                        <span class="font-body-md text-body-md text-on-surface">{{ $level->name }}</span>
                    </label>
                @empty
                    <p class="font-body-sm text-body-sm text-on-surface-variant">No grade levels have been created yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Reach preview --}}
        <div class="rounded-lg border border-outline-variant/60 bg-surface-container px-4 py-3">
            <p class="font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1">Who will see this?</p>
            <p class="font-body-md text-body-md text-on-surface" x-show="!loading" x-text="reachText"></p>
            <p class="font-body-md text-body-md text-on-surface-variant" x-show="loading" x-cloak>Checking…</p>
            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1" x-show="!loading && classList" x-cloak x-text="classList"></p>
        </div>

        {{-- Dates --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="published_at" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Publish at</label>
                <input type="datetime-local" name="published_at" id="published_at"
                    value="{{ old('published_at', $announcement->published_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                <p class="mt-1 font-body-sm text-body-sm text-on-surface-variant">Leave empty to keep it as a draft.</p>
            </div>
            <div>
                <label for="expires_at" class="block font-label-form text-label-form uppercase tracking-wider text-on-surface-variant mb-1.5">Expires</label>
                <input type="datetime-local" name="expires_at" id="expires_at"
                    value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-lg border-outline-variant/60 bg-surface-container-lowest font-body-md text-body-md focus:border-secondary focus:ring-secondary/40">
                <p class="mt-1 font-body-sm text-body-sm text-on-surface-variant">Optional. After this it drops off every feed.</p>
            </div>
        </div>
    </div>

    <div class="mt-7 flex items-center gap-3">
        <button type="submit"
            class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
            <span class="material-symbols-outlined text-[18px]">send</span>
            <span>{{ $mode === 'edit' ? 'Save changes' : 'Save announcement' }}</span>
        </button>
        <a href="{{ route('admin.announcements.index') }}"
            class="px-space-md py-2 rounded-lg font-title-sm text-title-sm text-on-surface-variant hover:bg-surface-container transition-colors">Cancel</a>
    </div>
</div>

@push('scripts')
<script>
function announcementForm(config) {
    return {
        audience: config.audience,
        classIds: config.classIds,
        levelIds: config.levelIds,
        loading: false,
        reachText: '',
        classList: '',
        timer: null,

        init() {
            this.refresh();
            // One watcher per input that changes the audience calculation.
            this.$watch('audience', () => this.refresh());
            this.$watch('classIds', () => this.refresh());
            this.$watch('levelIds', () => this.refresh());
        },

        refresh() {
            // Debounced: ticking five class boxes should cost one request, not five.
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.fetchReach(), 250);
        },

        async fetchReach() {
            const params = new URLSearchParams();
            params.set('audience', this.audience);
            if (this.audience === 'class') {
                this.classIds.forEach(id => params.append('class_ids[]', id));
            }
            if (this.audience === 'grade_level') {
                this.levelIds.forEach(id => params.append('grade_level_ids[]', id));
            }

            this.loading = true;
            try {
                const response = await fetch(config.previewUrl + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error(response.status);
                const data = await response.json();

                if (data.students === 0) {
                    this.reachText = 'Nobody yet — choose at least one target.';
                    this.classList = '';
                } else {
                    this.reachText = data.students + ' student'
                        + (data.students === 1 ? '' : 's')
                        + ' and ' + data.parents + ' parent account'
                        + (data.parents === 1 ? '' : 's') + '.';
                    this.classList = data.classes.length ? 'Classes: ' + data.classes.join(', ') : '';
                }
            } catch (e) {
                this.reachText = 'Could not work out the audience just now.';
                this.classList = '';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
