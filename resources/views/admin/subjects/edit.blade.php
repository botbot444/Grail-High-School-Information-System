{{-- resources/views/admin/subjects/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Subject')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <nav aria-label="Breadcrumb" class="flex items-center gap-space-xs text-on-surface-variant mb-space-md">
            <a href="{{ route('admin.subjects.index') }}" class="flex items-center gap-1 font-label-sm text-label-sm text-outline hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[16px]">grid_view</span>
                <span>Dashboard</span>
            </a>
            <span class="material-symbols-outlined text-[14px] text-outline-variant">chevron_right</span>
            <a href="{{ route('admin.subjects.index') }}" class="font-label-sm text-label-sm text-outline hover:text-primary transition-colors">Subjects</a>
            <span class="material-symbols-outlined text-[14px] text-outline-variant">chevron_right</span>
            <span class="font-label-sm text-label-sm text-primary font-semibold">Edit</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-md pb-space-lg">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Edit Subject</h1>
                <p class="font-body-md text-body-md text-on-surface-variant mt-0.5">Update this curriculum entity's title.</p>
            </div>
            <div class="flex items-center gap-space-sm self-start sm:self-auto">
                <a href="{{ route('admin.subjects.show', $subject) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface font-label-md text-label-md shadow-sm hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined text-[18px] text-outline">arrow_back</span>
                    <span>Back to Subject</span>
                </a>
                <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
                    onsubmit="return confirm('Delete this subject? It will be removed from any classes offering it.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-error hover:bg-error-container/30 transition-colors font-label-md text-label-md">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                        <span class="hidden sm:inline">Delete</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="w-full max-w-[500px] mx-auto flex flex-col gap-space-md mt-space-xs">
            @include('admin.partials.flash')

            <div class="bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant p-6 sm:p-7 relative overflow-hidden">
                <div class="flex items-start justify-between mb-6 pb-5 bg-gradient-to-b from-surface-container-low/50 to-transparent -mx-6 sm:-mx-7 -mt-6 sm:-mt-7 p-6 sm:p-7">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-[26px]">menu_book</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-headline-sm text-headline-sm text-on-surface">{{ $subject->subject_name }}</span>
                            <span class="font-label-micro text-label-micro text-outline uppercase tracking-wider mt-0.5">Curriculum Entity</span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="font-label-micro text-label-micro text-outline uppercase tracking-wider font-semibold" for="subject_name">
                                Subject Name <span class="text-error">*</span>
                            </label>
                            <span class="font-label-micro text-label-micro text-outline font-medium" id="charCounter">0 / 255</span>
                        </div>
                        <input type="text" id="subject_name" name="subject_name"
                            value="{{ old('subject_name', $subject->subject_name) }}" maxlength="255" required
                            class="w-full h-11 px-3.5 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface font-body-md text-body-md focus:outline-none shadow-sm transition-all focus:ring-2 focus:ring-primary-container/20 @error('subject_name') ring-2 ring-error @enderror">
                        <p class="font-body-sm text-body-sm text-outline mt-1.5 flex items-start gap-1">
                            <span class="material-symbols-outlined text-[14px] mt-0.5 shrink-0 text-outline">info</span>
                            <span>Renaming updates every class, teacher assignment, and report card that references this subject.</span>
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-outline-variant">
                        <a href="{{ route('admin.subjects.show', $subject) }}"
                            class="px-4 py-2.5 rounded-lg bg-surface-container-low text-on-surface font-label-md text-label-md hover:bg-surface-container transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary-container text-on-primary font-label-md text-label-md font-medium shadow-sm hover:bg-primary transition-all">
                            <span class="material-symbols-outlined text-[18px]">check</span>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant p-5 relative">
                <div class="flex items-center gap-1.5 mb-3.5">
                    <span class="material-symbols-outlined text-[16px] text-primary">hub</span>
                    <span class="font-label-micro text-label-micro uppercase tracking-wider text-outline font-semibold">Live Usage</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-surface-container-low/70">
                        <div class="w-9 h-9 rounded-lg bg-surface-container-lowest text-primary flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-[20px]">groups</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-headline-sm text-headline-sm text-on-surface leading-tight">{{ $subject->classes_count }}</span>
                            <span class="font-label-micro text-label-micro text-outline truncate">{{ $subject->classes_count === 1 ? 'Class offering it' : 'Classes offering it' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-surface-container-low/70">
                        <div class="w-9 h-9 rounded-lg bg-surface-container-lowest text-secondary flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-[20px]">badge</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-headline-sm text-headline-sm text-on-surface leading-tight">{{ $subject->teachers_count }}</span>
                            <span class="font-label-micro text-label-micro text-outline truncate">{{ $subject->teachers_count === 1 ? 'Teacher assigned' : 'Teachers assigned' }}</span>
                        </div>
                    </div>
                </div>
            </div>
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
