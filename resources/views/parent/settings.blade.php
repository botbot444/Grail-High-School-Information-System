@extends('layouts.parent')

@section('title', 'Settings – Parent Portal')

@section('page')
    <div class="max-w-4xl mx-auto space-y-6">
        @php $children = $children ?? collect(); @endphp

        {{-- ── Page header ── --}}
        <div>
            <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Settings</h1>
            <p class="text-sm text-on-surface-variant mt-1">Manage your account and personal information</p>
        </div>

        @if ($parentProfile->user?->must_change_password)
            <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 flex items-start gap-2.5">
                <span class="material-symbols-outlined text-amber-600" style="font-size:20px">lock_reset</span>
                <div class="text-sm text-amber-900">
                    <p class="font-semibold">Please set a new password</p>
                    <p class="text-amber-800/90 mt-0.5">You're currently signed in with a temporary password. Set your own below to continue using the portal.</p>
                </div>
            </div>
        @endif

        {{-- ── Profile card ── --}}
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="h-16 bg-primary/10"></div>
            <div class="px-5 pb-5 -mt-8">
                <div class="w-16 h-16 rounded-full bg-primary text-on-primary flex items-center justify-center text-xl font-extrabold ring-4 ring-white">
                    {{ strtoupper(substr($parentProfile->user->name ?? 'P', 0, 1)) }}
                </div>
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface mt-2">{{ $parentProfile->user->name ?? '—' }}</h2>
                <p class="text-sm text-on-surface-variant">Guardian · {{ $children->count() }} linked {{ $children->count() === 1 ? 'child' : 'children' }}</p>
            </div>
        </div>

        {{-- ── Update form ── --}}
        <form method="POST" action="{{ route('parent.settings.update') }}" class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            @csrf
            @method('PATCH')
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Personal Information</h2>
                <p class="text-xs text-on-surface-variant mt-0.5">Keep your contact details up to date</p>
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-1.5">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $parentProfile->user->name ?? '') }}" required
                        class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary">
                </div>
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $parentProfile->user->email ?? '') }}" required
                        class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary">
                </div>
                <div>
                    <label for="phone" class="block text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-1.5">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $parentProfile->phone ?? '') }}"
                        class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary">
                </div>
                <div>
                    <label for="address" class="block text-xs font-semibold uppercase tracking-wide text-on-surface-variant mb-1.5">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $parentProfile->address ?? '') }}"
                        class="w-full rounded-lg border border-outline-variant bg-surface-container-lowest px-3 py-2 text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary">
                </div>
            </div>
            <div class="px-5 py-4 bg-surface-container border-t border-outline-variant flex items-center justify-end gap-3">
                @if (session('notification'))
                    <span class="text-xs font-semibold text-green-700 mr-auto flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size:14px">check_circle</span>
                        {{ session('notification') }}
                    </span>
                @endif
                <button type="submit"
                    class="text-sm font-bold bg-primary text-on-primary rounded-lg px-4 py-2 hover:bg-primary/90 transition-colors flex items-center gap-1.5">
                    <span class="material-symbols-outlined" style="font-size:16px">save</span> Save Changes
                </button>
            </div>
        </form>


        {{-- ── Password ── --}}
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant flex items-center gap-2.5">
                <span class="material-symbols-outlined text-on-surface-variant" style="font-size:20px">lock</span>
                <div>
                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Password</h2>
                    <p class="text-xs text-on-surface-variant mt-0.5">Use a strong password to protect your account</p>
                </div>
            </div>
            <div class="p-5">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- ── Account info ── --}}
        <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-outline-variant">
                <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">Account</h2>
            </div>
            <ul class="divide-y divide-outline-variant text-sm">
                <li class="px-5 py-3 flex items-center justify-between">
                    <span class="text-on-surface-variant">Role</span>
                    <span class="font-semibold text-on-surface">Parent / Guardian</span>
                </li>
                <li class="px-5 py-3 flex items-center justify-between">
                    <span class="text-on-surface-variant">Children Enrolled</span>
                    <span class="font-semibold text-on-surface">{{ $children->count() }}</span>
                </li>
                <li class="px-5 py-3 flex items-center justify-between">
                    <span class="text-on-surface-variant">Member Since</span>
                    <span class="font-semibold text-on-surface">{{ $parentProfile->user?->created_at?->format('M Y') ?? '—' }}</span>
                </li>
            </ul>
        </div>
    </div>
@endsection

