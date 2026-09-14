@extends('layouts.app')

@section('title', 'New Announcement')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')

        <div class="max-w-3xl">
            <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                <a href="{{ route('admin.announcements.index') }}" class="text-label-sm font-label-sm hover:text-primary">Announcements</a>
                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                <span class="text-label-sm font-label-sm text-primary font-bold">New Announcement</span>
            </nav>
            <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface mb-6">New Announcement</h1>

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-error/30 bg-error-container px-4 py-3">
                    <ul class="list-disc ml-5 font-body-sm text-body-sm text-on-error-container space-y-1">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.announcements.store') }}"
                  class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm p-6">
                @include('admin.announcements._form', ['mode' => 'create'])
            </form>
        </div>
    </div>
@endsection
