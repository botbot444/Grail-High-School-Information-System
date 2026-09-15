@extends('layouts.student')

@section('title', 'Settings')
@section('page-title', 'Account Settings')
@section('page-subtitle', 'Your profile and sign-in details')

@section('content')
    @if ($student->user?->must_change_password)
        <div class="mb-space-md rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 flex items-start gap-2.5">
            <span class="material-symbols-outlined text-amber-600" style="font-size:20px">lock_reset</span>
            <div class="text-sm text-amber-900">
                <p class="font-semibold">Please set a new password</p>
                <p class="text-amber-800/90 mt-0.5">You're currently signed in with a temporary password. Set your own below to continue using the portal.</p>
            </div>
        </div>
    @endif

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
            <p class="text-body-sm text-on-surface-variant mt-0.5">Change the password you use to sign in to Grail SIS.</p>
        </header>

        <form method="POST" action="{{ route('password.update') }}" class="px-space-md py-space-md space-y-4">
            @csrf
            @method('put')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="current_password" class="block text-label-sm text-on-surface-variant mb-1">Current Password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                        class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary">
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="block text-label-sm text-on-surface-variant mb-1">New Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password"
                        class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary">
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-label-sm text-on-surface-variant mb-1">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                        class="w-full rounded-lg border border-outline-variant bg-surface px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary">
                    @error('password_confirmation', 'updatePassword')
                        <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-on-primary text-label-md font-semibold hover:bg-on-primary-fixed-variant transition-colors">
                    <span class="material-symbols-outlined text-lg">key</span>
                    Update Password
                </button>
                @if (session('status') === 'password-updated')
                    <span class="text-body-sm text-secondary font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:16px">check_circle</span>
                        Saved.
                    </span>
                @endif
            </div>
        </form>
    </section>
@endsection
