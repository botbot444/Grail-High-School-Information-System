{{-- resources/views/admin/calendar/periods/_form.blade.php --}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="max-w-2xl">
        <section class="rounded-xl bg-surface-container-lowest border border-outline-variant shadow-sm p-space-lg flex flex-col gap-6">
            <div class="flex items-center gap-3 pb-4 border-b border-outline-variant">
                <div class="w-10 h-10 rounded-lg bg-surface-container-low text-primary flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-[22px]">schedule</span>
                </div>
                <div>
                    <h2 class="font-title-md text-title-md text-on-surface font-semibold">Period Details</h2>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Grade level, name, time window and sort order</span>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="grade_level_id">
                    Grade Level <span class="text-error">*</span>
                </label>
                <select id="grade_level_id" name="grade_level_id" required
                    class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md appearance-none focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm cursor-pointer @error('grade_level_id') ring-2 ring-error @enderror">
                    @foreach ($gradeLevels as $level)
                        <option value="{{ $level->grade_level_id }}"
                            @selected((int) old('grade_level_id', $period?->grade_level_id ?? ($selectedGradeLevel ?? 0)) === $level->grade_level_id)>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="name">
                    Name <span class="text-error">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $period?->name) }}" required
                    placeholder="e.g. Period 1, Lunch Break"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('name') ring-2 ring-error @enderror">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="start_time">
                        Start Time <span class="text-error">*</span>
                    </label>
                    <input type="time" id="start_time" name="start_time"
                        value="{{ old('start_time', $period?->start_time?->format('H:i')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('start_time') ring-2 ring-error @enderror">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="end_time">
                        End Time <span class="text-error">*</span>
                    </label>
                    <input type="time" id="end_time" name="end_time"
                        value="{{ old('end_time', $period?->end_time?->format('H:i')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('end_time') ring-2 ring-error @enderror">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="order">
                    Order <span class="text-error">*</span>
                </label>
                <input type="number" id="order" name="order" min="1" value="{{ old('order', $period?->order) }}" required
                    class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('order') ring-2 ring-error @enderror">
                <p class="font-body-sm text-body-sm text-on-surface-variant">Position within the day for this grade level. Must be unique per grade level.</p>
            </div>

            <label class="flex items-center gap-3 p-4 rounded-xl bg-surface-container-low cursor-pointer">
                <input type="checkbox" name="is_break" value="1" @checked(old('is_break', $period?->is_break))
                    class="w-4 h-4 rounded accent-primary cursor-pointer">
                <span class="font-body-md text-body-md text-on-surface font-semibold">This is a break period (no subject/teacher assignment)</span>
            </label>
        </section>
    </div>

    <div class="flex items-center justify-end gap-3 mt-8 max-w-2xl">
        <a href="{{ route('admin.periods.index') }}"
            class="px-5 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md">
            Cancel
        </a>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-bold hover:bg-primary-container transition-all shadow-md">
            <span class="material-symbols-outlined text-[20px]">save</span>
            <span>Save Period</span>
        </button>
    </div>
</form>
