{{-- resources/views/admin/subjects/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add Subject')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="flex items-center justify-between gap-space-md mb-6">
            <nav class="flex items-center gap-1.5 text-on-surface-variant font-label-sm text-label-sm">
                <a href="{{ route('admin.subjects.index') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">grid_view</span>
                    <span>Dashboard</span>
                </a>
                <span class="material-symbols-outlined text-[14px] text-outline">chevron_right</span>
                <a href="{{ route('admin.subjects.index') }}" class="hover:text-primary transition-colors">Subjects</a>
                <span class="material-symbols-outlined text-[14px] text-outline">chevron_right</span>
                <span class="text-on-surface font-semibold">Add New</span>
            </nav>
            <a href="{{ route('admin.subjects.index') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors font-label-sm text-label-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Back to Subjects</span>
            </a>
        </div>

        <div class="text-center max-w-lg mx-auto mb-6">
            <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Add New Subject</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                Subjects can be offered by classes and assigned to teachers once they're created here.
            </p>
        </div>

        <div class="w-full max-w-[500px] mx-auto">
            @include('admin.partials.flash')

            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant p-space-xl relative overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-primary via-primary-container to-secondary-container"></div>

                <div class="flex items-center justify-between mb-space-lg">
                    <div class="w-12 h-12 rounded-xl bg-primary-fixed flex items-center justify-center text-primary shadow-sm">
                        <span class="material-symbols-outlined text-[26px]">menu_book</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-surface-container-high text-on-surface-variant font-label-micro text-label-micro uppercase tracking-wider">
                        Curriculum Entity
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-space-lg">
                    @csrf
                    <div class="flex flex-col">
                        <label class="font-label-micro text-label-micro uppercase tracking-wider text-on-surface-variant mb-space-xs flex items-center justify-between" for="subject_name">
                            <span>Subject Name <span class="text-error">*</span></span>
                            <span class="text-outline lowercase font-normal" id="charCounter">0 / 255</span>
                        </label>
                        <input type="text" id="subject_name" name="subject_name" value="{{ old('subject_name') }}"
                            maxlength="255" required placeholder="e.g. Mathematics, English Literature"
                            class="w-full h-11 px-3.5 rounded-lg bg-surface-container-low text-on-surface font-body-md text-body-md placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 transition-all @error('subject_name') ring-2 ring-error @enderror">
                        <div class="flex items-center gap-1.5 mt-2 text-on-surface-variant font-body-sm text-body-sm">
                            <span class="material-symbols-outlined text-[16px] text-outline">info</span>
                            <span>Appears wherever this subject is offered — class rosters, teacher assignments, and report cards.</span>
                        </div>
                    </div>

                    <div class="pt-space-md flex items-center justify-end gap-space-sm">
                        <a href="{{ route('admin.subjects.index') }}"
                            class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors font-label-md text-label-md text-center">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-primary-container hover:bg-primary text-on-primary font-label-md text-label-md shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            <span>Create Subject</span>
                        </button>
                    </div>
                </form>
            </div>

            @if ($recentSubjects->isNotEmpty())
                <div class="mt-space-md bg-surface-container-lowest rounded-xl border border-outline-variant p-space-md shadow-sm">
                    <div class="flex items-center justify-between mb-space-sm pb-space-xs">
                        <span class="font-label-micro text-label-micro uppercase tracking-wider text-on-surface-variant">Recently Created Subjects</span>
                        <a href="{{ route('admin.subjects.index') }}" class="font-label-micro text-label-micro text-primary hover:underline">View All</a>
                    </div>
                    <div class="space-y-2">
                        @foreach ($recentSubjects as $recent)
                            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-surface-container-low transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded bg-surface-container-high flex items-center justify-center text-primary">
                                        <span class="material-symbols-outlined text-[14px]">menu_book</span>
                                    </div>
                                    <span class="font-label-sm text-label-sm text-on-surface font-medium">{{ $recent->subject_name }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </main>

    @push('scripts')
        <script>
            (function () {
                const input = document.getElementById('subject_name');
                const counter = document.getElementById('charCounter');
                if (input && counter) {
                    const update = () => { counter.textContent = `${input.value.length} / 255`; };
                    input.addEventListener('input', update);
                    update();
                }
            })();
        </script>
    @endpush
@endsection
