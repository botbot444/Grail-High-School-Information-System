{{-- resources/views/admin/calendar/grade-levels/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Grade Level')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <nav class="flex items-center gap-2 mb-4 font-label-md text-label-md text-on-surface-variant">
            <a href="{{ route('admin.grade-levels.index') }}" class="hover:text-primary transition-colors">Grade Levels</a>
            <span class="text-outline-variant">/</span>
            <span class="text-primary font-semibold">Add New</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div class="flex flex-col gap-1.5">
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Add New Grade Level</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Create a canonical grade level that classes can be grouped by.
                </p>
            </div>
            <div class="flex items-center gap-3 self-start md:self-auto">
                <a href="{{ route('admin.grade-levels.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    <span>Back to Grade Levels</span>
                </a>
            </div>
        </div>

        @include('admin.partials.flash')

        <form method="POST" action="{{ route('admin.grade-levels.store') }}">
            @csrf

            <div class="max-w-2xl">
                <section class="rounded-xl bg-surface-container-lowest border border-outline-variant shadow-sm p-space-lg flex flex-col gap-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-outline-variant">
                        <div class="w-10 h-10 rounded-lg bg-surface-container-low text-primary flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-[22px]">stairs</span>
                        </div>
                        <div>
                            <h2 class="font-title-md text-title-md text-on-surface font-semibold">Grade Level Details</h2>
                            <span class="font-body-sm text-body-sm text-on-surface-variant">Name and sort order</span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="name">
                            Name <span class="text-error">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            placeholder="e.g. Grade 10"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('name') ring-2 ring-error @enderror">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="font-label-micro text-label-micro text-on-surface-variant uppercase tracking-wider font-semibold" for="order">
                            Order <span class="text-error">*</span>
                        </label>
                        <input type="number" id="order" name="order" value="{{ old('order') }}" required min="1"
                            placeholder="e.g. 10"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-surface text-on-surface font-body-md text-body-md focus:outline-none focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 shadow-sm @error('order') ring-2 ring-error @enderror">
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Controls sort order across lists and dropdowns. Must be unique.</p>
                    </div>
                </section>
            </div>

            <div class="flex items-center justify-end gap-3 mt-8 max-w-2xl">
                <a href="{{ route('admin.grade-levels.index') }}"
                    class="px-5 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-on-primary font-label-md text-label-md font-bold hover:bg-primary-container transition-all shadow-md">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    <span>Create Grade Level</span>
                </button>
            </div>
        </form>
    </main>
@endsection
