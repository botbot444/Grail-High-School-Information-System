@extends('layouts.student')

@section('title', 'Settings')
@section('page-title', 'Account Settings')
@section('page-subtitle', 'Your profile and sign-in details')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-space-md">
        {{-- Profile card --}}
        <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow p-space-lg text-center">
            <div class="w-20 h-20 mx-auto rounded-[50%] bg-primary text-on-primary flex items-center justify-center text-display-md font-display-md">
                {{ $navStudentInitials ?? 'S' }}
            </div>
            <h2 class="mt-space-md text-headline-sm font-headline-sm text-on-surface">{{ $student->full_name }}</h2>
            <p class="text-body-md text-on-surface-variant">{{ $student->schoolClass?->class_name ?? 'No class assigned' }}</p>
            <p class="mt-1 font-data-mono text-code-md text-outline">{{ $student->student_number }}</p>
        </section>

        {{-- Details --}}
        <section class="lg:col-span-2 bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
            <header class="px-space-md py-space-sm border-b border-outline-variant/50">
                <h2 class="text-headline-sm font-headline-sm text-on-surface">My Details</h2>
            </header>

            @php
                $details = [
                    ['Full name',     $student->full_name],
                    ['Student number',$student->student_number],
                    ['Email',         $student->user?->email],
                    ['Class',         $student->schoolClass?->class_name],
                    ['Grade level',   $student->schoolClass?->gradeLevel?->name],
                    ['Date of birth', $student->date_of_birth?->format('j F Y')],
                    ['Enrolled',      $student->enrolment_date?->format('j F Y')],
                    ['Guardian',      $student->guardian_name],
                    ['Guardian phone',$student->guardian_phone],
                ];
            @endphp

            <dl class="divide-y divide-outline-variant/40">
                @foreach ($details as [$label, $value])
                    <div class="flex flex-wrap items-baseline gap-2 px-space-md py-3">
                        <dt class="w-40 shrink-0 text-label-md text-on-surface-variant">{{ $label }}</dt>
                        <dd class="flex-1 min-w-0 text-body-md text-on-surface">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="px-space-md py-space-sm border-t border-outline-variant/40 bg-surface-container-low/50">
                <p class="flex items-start gap-2 text-body-sm text-on-surface-variant">
                    <span class="material-symbols-outlined text-sm text-secondary mt-0.5">info</span>
                    <span>
                        These details are maintained by the school. If something is wrong, contact the
                        office — students cannot edit their own record.
                    </span>
                </p>
            </div>
        </section>
    </div>

    {{-- Password --}}
    <section class="mt-space-md bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow overflow-hidden">
        <header class="px-space-md py-space-sm border-b border-outline-variant/50">
            <h2 class="text-headline-sm font-headline-sm text-on-surface">Sign-in</h2>
        </header>
        <div class="px-space-md py-space-md flex flex-wrap items-center justify-between gap-space-md">
            <div>
                <p class="text-body-md text-on-surface">Password</p>
                <p class="text-body-sm text-on-surface-variant">Change the password you use to sign in to Grail SIS.</p>
            </div>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-on-primary text-label-md font-semibold hover:bg-on-primary-fixed-variant transition-colors">
                    <span class="material-symbols-outlined text-lg">lock_reset</span>
                    Change password
                </a>
            @endif
        </div>
    </section>
@endsection
