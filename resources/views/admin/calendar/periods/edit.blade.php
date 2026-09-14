{{-- resources/views/admin/calendar/periods/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Period')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')


        <nav class="flex items-center gap-2 mb-4 font-label-md text-label-md text-on-surface-variant">
            <a href="{{ route('admin.periods.index') }}" class="hover:text-primary transition-colors">Periods</a>
            <span class="text-outline-variant">/</span>
            <span class="text-on-surface font-semibold">{{ $period->name }}</span>
            <span class="text-outline-variant">/</span>
            <span class="text-primary font-semibold">Edit</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div class="flex flex-col gap-1.5">
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Edit Period — {{ $period->name }}</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Update {{ $period->name }}.
                </p>
            </div>
            <div class="flex items-center gap-3 self-start md:self-auto">
                <a href="{{ route('admin.periods.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-container-high transition-colors font-label-md text-label-md shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    <span>Back to Periods</span>
                </a>
            </div>
        </div>

        @include('admin.calendar.periods._form', [
            'action' => route('admin.periods.update', $period),
            'method' => 'PUT',
        ])
    </main>
@endsection
