{{-- resources/views/admin/calendar/terms/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Term')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <nav class="flex items-center gap-2 mb-4 font-label-md text-label-md text-on-surface-variant">
            <a href="{{ route('admin.terms.index') }}" class="hover:text-primary transition-colors">Terms</a>
            <span class="text-outline-variant">/</span>
            <span class="text-on-surface font-semibold">{{ $term->name }}</span>
            <span class="text-outline-variant">/</span>
            <span class="text-primary font-semibold">Edit</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div class="flex flex-col gap-1.5">
                <div class="flex items-center gap-3">
                    <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Edit Term — {{ $term->name }}</h1>
                    @if ($term->is_current)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-label-sm font-semibold">
                            <span class="w-2 h-2 rounded-full bg-tertiary-container"></span>
                            CURRENT
                        </span>
                    @endif
                </div>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Update {{ $term->name }} ({{ $term->school_days }} school days).
                </p>
            </div>
            <div class="flex items-center gap-3 self-start md:self-auto">
                <a href="{{ route('admin.terms.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    <span>Back to Terms</span>
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <form method="POST" action="{{ route('admin.terms.update', $term) }}">
            @csrf
            @method('PUT')

            <div class="max-w-2xl">
                <section class="rounded-xl bg-surface-container-lowest border border-outline-variant shadow-sm p-space-lg flex flex-col gap-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-outline-variant">
                        <div class="w-10 h-10 rounded-lg bg-surface-container-low text-primary flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-[22px]">view_agenda</span>
                        </div>
                        <div>
                            <h2 class="font-title-md text-title-md text-on-surface font-semibold">Term Details</h2>
                            <span class="font-body-sm text-body-sm text-on-surface-variant">Academic year, name and date range</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="academic_year_id">
                            Academic Year <span class="text-error">*</span>
                        </label>
                        <select id="academic_year_id" name="academic_year_id" required
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md appearance-none focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm cursor-pointer @error('academic_year_id') ring-2 ring-error @enderror">
                            @foreach ($academicYears as $id => $label)
                                <option value="{{ $id }}"
                                    {{ old('academic_year_id', $term->academic_year_id) == $id ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="name">
                            Term Name <span class="text-error">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $term->name) }}" required
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('name') ring-2 ring-error @enderror">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="start_date">
                                Start Date <span class="text-error">*</span>
                            </label>
                            <input type="date" id="start_date" name="start_date"
                                value="{{ old('start_date', $term->start_date->format('Y-m-d')) }}" required
                                class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('start_date') ring-2 ring-error @enderror">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="end_date">
                                End Date <span class="text-error">*</span>
                            </label>
                            <input type="date" id="end_date" name="end_date"
                                value="{{ old('end_date', $term->end_date->format('Y-m-d')) }}" required
                                class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('end_date') ring-2 ring-error @enderror">
                        </div>
                    </div>
                </section>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8 max-w-2xl">
                <a href="{{ route('admin.terms.index') }}"
                    class="px-5 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-bold hover:bg-primary-container transition-all shadow-md">
                    <span class="material-symbols-outlined text-[20px]">save</span>
                    <span>Update Term</span>
                </button>
            </div>
        </form>
    </main>
@endsection
