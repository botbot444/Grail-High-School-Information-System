@extends('layouts.teacher')

@section('title', $placeholder . ' – Teacher Portal')

@section('page')
    <div class="max-w-4xl mx-auto py-16 flex flex-col items-center text-center">
        <div class="w-20 h-20 rounded-2xl bg-secondary-fixed flex items-center justify-center text-secondary mb-6">
            <span class="material-symbols-outlined text-[40px]">construction</span>
        </div>
        <h1 class="font-headline-md text-headline-md text-primary font-bold mb-2">{{ $placeholder }}</h1>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-md mx-auto mt-2">
            This module is being integrated from the teacher portal. It will be
            available here shortly.
        </p>
        <a href="{{ route('teacher.dashboard') }}"
            class="mt-8 inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            <span>Back to Dashboard</span>
        </a>
    </div>
@endsection