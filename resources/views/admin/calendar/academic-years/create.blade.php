{{-- resources/views/admin/calendar/academic-years/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Academic Year')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <nav class="flex items-center gap-2 mb-4 font-label-md text-label-md text-on-surface-variant">
            <a href="{{ route('admin.academic-years.index') }}" class="hover:text-primary transition-colors">Academic Years</a>
            <span class="text-outline-variant">/</span>
            <span class="text-primary font-semibold">Add New</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div class="flex flex-col gap-1.5">
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Add New Academic Year</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Define the date window that organizes terms, holidays, grades and fees.
                </p>
            </div>
            <div class="flex items-center gap-3 self-start md:self-auto">
                <a href="{{ route('admin.academic-years.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    <span>Back to Academic Years</span>
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <form method="POST" action="{{ route('admin.academic-years.store') }}">
            @csrf

            <div class="max-w-2xl">
                <section class="rounded-xl bg-surface-container-lowest border border-outline-variant shadow-sm p-space-lg flex flex-col gap-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-outline-variant">
                        <div class="w-10 h-10 rounded-lg bg-surface-container-low text-primary flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-[22px]">calendar_month</span>
                        </div>
                        <div>
                            <h2 class="font-title-md text-title-md text-on-surface font-semibold">Academic Year Details</h2>
                            <span class="font-body-sm text-body-sm text-on-surface-variant">Label and date range</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="label">
                            Label <span class="text-error">*</span>
                        </label>
                        <input type="text" id="label" name="label" value="{{ old('label') }}" required
                            placeholder="e.g. 2026 or 2026-2027"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('label') ring-2 ring-error @enderror">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="start_date">
                                Start Date <span class="text-error">*</span>
                            </label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                                class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('start_date') ring-2 ring-error @enderror">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="end_date">
                                End Date <span class="text-error">*</span>
                            </label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                                class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('end_date') ring-2 ring-error @enderror">
                        </div>
                    </div>

                    <label class="flex items-center gap-3 p-4 rounded-xl bg-surface-container-low cursor-pointer">
                        <input type="checkbox" name="is_current" value="1" {{ old('is_current') ? 'checked' : '' }}
                            class="w-4 h-4 rounded accent-primary cursor-pointer">
                        <span class="font-body-md text-body-md text-on-surface font-semibold">Set as current academic year</span>
                    </label>
                </section>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8 max-w-2xl">
                <a href="{{ route('admin.academic-years.index') }}"
                    class="px-5 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-bold hover:bg-primary-container transition-all shadow-md">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    <span>Create Academic Year</span>
                </button>
            </div>
        </form>
    </main>
@endsection
