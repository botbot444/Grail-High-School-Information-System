@extends('layouts.teacher')

@section('title', 'Teacher Settings')

@section('page')
    <div class="mx-auto flex max-w-5xl flex-col gap-space-lg">
        <div>
            <p class="font-label-sm text-label-sm uppercase tracking-wider text-secondary">Teacher Portal / Account</p>
            <h1 class="font-headline-lg text-headline-lg text-on-surface">Settings</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Manage your teacher account details and password.</p>
        </div>

        @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
            <div class="rounded-lg bg-green-100 px-4 py-3 text-green-800">Your settings were updated.</div>
        @endif
        @if ($errors->any())
            <div class="rounded-lg bg-error-container px-4 py-3 text-error"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="rounded-xl bg-surface-container-lowest p-space-lg shadow-sm">
            <div class="mb-space-md flex items-center gap-3 border-b border-outline-variant pb-space-md"><span class="material-symbols-outlined text-secondary">account_circle</span><div><h2 class="font-title-md text-title-md text-on-surface">Profile Information</h2><p class="font-body-sm text-body-sm text-on-surface-variant">Your name and institutional email address.</p></div></div>
            @include('profile.partials.update-profile-information-form', ['user' => $user])
        </section>

        <section class="rounded-xl bg-surface-container-lowest p-space-lg shadow-sm">
            <div class="mb-space-md flex items-center gap-3 border-b border-outline-variant pb-space-md"><span class="material-symbols-outlined text-secondary">lock</span><div><h2 class="font-title-md text-title-md text-on-surface">Password</h2><p class="font-body-sm text-body-sm text-on-surface-variant">Use a strong password to protect your account.</p></div></div>
            @include('profile.partials.update-password-form')
        </section>

        <section class="rounded-xl bg-surface-container-low p-space-md"><div class="flex items-start gap-3"><span class="material-symbols-outlined text-secondary">support_agent</span><div><h2 class="font-title-sm text-title-sm text-on-surface">Need account help?</h2><p class="font-body-sm text-body-sm text-on-surface-variant">Contact the school administrator for account activation, role, or profile corrections.</p></div></div></section>
    </div>
@endsection