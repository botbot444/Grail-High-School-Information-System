@extends('layouts.teacher')

@section('title', 'Settings - Teacher Portal')

@section('page')
    @php
        $homeroom = $teacher?->homeroomClasses->first();
        $classesTaught = $teacher ? $teacher->classSubjects->pluck('class_id')->unique()->count() : 0;
        $subjectsTaught = $teacher ? $teacher->classSubjects->pluck('subject_id')->unique()->count() : 0;
    @endphp

    <div class="max-w-3xl mx-auto w-full flex flex-col gap-space-lg pb-16">
        <div class="flex flex-col gap-1">
            <div class="flex items-center gap-2 font-label-md text-label-md text-on-surface-variant mb-1">
                <span>Teacher Portal</span>
                <span>/</span>
                <span>Account</span>
                <span>/</span>
                <span class="text-secondary font-title-sm text-title-sm">Settings</span>
            </div>
            <div class="flex items-baseline justify-between">
                <div>
                    <h1 class="font-headline-md text-headline-md text-on-surface">Settings</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-0.5">Manage your account details and
                        password.</p>
                </div>
            </div>
        </div>

        {{-- Profile Information --}}
        <section class="rounded-xl bg-surface-container-lowest shadow-sm overflow-hidden">
            <div class="px-space-lg py-space-md">
                <div class="flex items-center gap-space-sm">
                    <div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-[24px]">badge</span>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="font-title-md text-title-md text-on-surface">Profile Information</h2>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Your name and institutional email
                            address.</p>
                    </div>
                </div>
            </div>

            <div class="px-space-lg py-space-md bg-surface-container-low">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="relative w-14 h-14 rounded-full bg-primary flex items-center justify-center text-on-primary shadow-sm ring-4 ring-surface-container-lowest">
                            <span class="font-title-md text-title-md">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="font-title-md text-title-md text-on-surface">{{ $user->name }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-surface-container-highest font-label-sm text-label-sm text-on-surface-variant">Teacher</span>
                            </div>
                            <span class="font-body-sm text-body-sm text-on-surface-variant">
                                {{ $homeroom ? 'Homeroom teacher · ' . $homeroom->display_name : ($teacher ? $subjectsTaught . ' subject(s) taught' : 'No teacher profile linked') }}
                            </span>
                        </div>
                    </div>
                    @if ($teacher)
                        <div class="flex items-center gap-3">
                            <div class="px-3 py-1.5 rounded-lg bg-surface-container-lowest shadow-xs text-left">
                                <span class="block font-label-sm text-label-sm text-outline uppercase tracking-wider">Staff ID</span>
                                <span class="font-data-md text-data-md text-on-surface">#{{ $teacher->teacher_id }}</span>
                            </div>
                            <div class="px-3 py-1.5 rounded-lg bg-surface-container-lowest shadow-xs text-left">
                                <span class="block font-label-sm text-label-sm text-outline uppercase tracking-wider">Homeroom</span>
                                <span class="font-data-md text-data-md text-on-surface">{{ $homeroom?->display_name ?? '—' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('teacher.settings.update') }}" class="p-space-lg space-y-space-md">
                @csrf
                @method('patch')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                    <div class="flex flex-col">
                        <label class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1.5" for="name">Full
                            Name</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-outline text-[20px] pointer-events-none">person</span>
                            <input class="w-full h-10 pl-10 pr-3 rounded bg-surface-container-lowest font-body-md text-body-md text-on-surface shadow-[0_0_0_1px_#c4c6ce] focus:shadow-[0_0_0_2px_#085bbd] focus:outline-none transition-shadow"
                                id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
                                autocomplete="name">
                        </div>
                        @error('name')
                            <span class="mt-1 font-body-sm text-body-sm text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col">
                        <label class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1.5" for="email">Institutional
                            Email</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-outline text-[20px] pointer-events-none">mail</span>
                            <input class="w-full h-10 pl-10 pr-3 rounded bg-surface-container-lowest font-body-md text-body-md text-on-surface shadow-[0_0_0_1px_#c4c6ce] focus:shadow-[0_0_0_2px_#085bbd] focus:outline-none transition-shadow"
                                id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                                autocomplete="username">
                        </div>
                        @error('email')
                            <span class="mt-1 font-body-sm text-body-sm text-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                @if ($teacher)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div class="p-3 rounded-lg bg-surface-container-low flex flex-col gap-0.5">
                            <span class="font-label-sm text-label-sm text-outline">CLASSES TAUGHT</span>
                            <span class="font-body-md text-body-md text-on-surface">{{ $classesTaught }}</span>
                        </div>
                        <div class="p-3 rounded-lg bg-surface-container-low flex flex-col gap-0.5">
                            <span class="font-label-sm text-label-sm text-outline">SUBJECTS TAUGHT</span>
                            <span class="font-body-md text-body-md text-on-surface">{{ $subjectsTaught }}</span>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-space-xs">
                    <span class="font-body-sm text-body-sm text-outline">
                        @if ($user->updated_at)
                            Last modified: {{ $user->updated_at->format('M j, Y \a\t g:i A') }}
                        @endif
                    </span>
                    <button class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary text-on-secondary hover:bg-primary-container font-title-sm text-title-sm shadow-sm transition-colors"
                        type="submit">
                        <span class="material-symbols-outlined text-[18px]">check</span>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- Password & Security --}}
        <section class="rounded-xl bg-surface-container-lowest shadow-sm overflow-hidden">
            <div class="px-space-lg py-space-md">
                <div class="flex items-center gap-space-sm">
                    <div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-[24px]">lock</span>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="font-title-md text-title-md text-on-surface">Password &amp; Security</h2>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Use a strong password to protect your
                            account.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="p-space-lg space-y-space-md">
                @csrf
                @method('put')

                <div class="flex flex-col">
                    <label class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1.5"
                        for="current_password">Current Password</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-outline text-[20px] pointer-events-none">vpn_key</span>
                        <input class="w-full h-10 pl-10 pr-10 rounded bg-surface-container-lowest font-body-md text-body-md text-on-surface shadow-[0_0_0_1px_#c4c6ce] focus:shadow-[0_0_0_2px_#085bbd] focus:outline-none transition-shadow"
                            id="current_password" name="current_password" type="password" placeholder="••••••••••••"
                            autocomplete="current-password">
                        <button aria-label="Show password" class="absolute right-2.5 p-1 rounded text-outline hover:text-on-surface transition-colors"
                            onclick="togglePasswordVisibility('current_password', this)" type="button">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                        </button>
                    </div>
                    @error('current_password', 'updatePassword')
                        <span class="mt-1 font-body-sm text-body-sm text-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-md">
                    <div class="flex flex-col">
                        <label class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1.5" for="password">New
                            Password</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-outline text-[20px] pointer-events-none">lock</span>
                            <input class="w-full h-10 pl-10 pr-10 rounded bg-surface-container-lowest font-body-md text-body-md text-on-surface shadow-[0_0_0_1px_#c4c6ce] focus:shadow-[0_0_0_2px_#085bbd] focus:outline-none transition-shadow"
                                id="password" name="password" type="password" placeholder="Enter new password"
                                autocomplete="new-password" oninput="checkStrength(this.value)">
                            <button aria-label="Show password" class="absolute right-2.5 p-1 rounded text-outline hover:text-on-surface transition-colors"
                                onclick="togglePasswordVisibility('password', this)" type="button">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                            </button>
                        </div>
                        @error('password', 'updatePassword')
                            <span class="mt-1 font-body-sm text-body-sm text-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex flex-col">
                        <label class="font-label-sm text-label-sm text-outline uppercase tracking-wider mb-1.5"
                            for="password_confirmation">Confirm New Password</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-outline text-[20px] pointer-events-none">lock_reset</span>
                            <input class="w-full h-10 pl-10 pr-10 rounded bg-surface-container-lowest font-body-md text-body-md text-on-surface shadow-[0_0_0_1px_#c4c6ce] focus:shadow-[0_0_0_2px_#085bbd] focus:outline-none transition-shadow"
                                id="password_confirmation" name="password_confirmation" type="password"
                                placeholder="Confirm new password" autocomplete="new-password">
                            <button aria-label="Show password" class="absolute right-2.5 p-1 rounded text-outline hover:text-on-surface transition-colors"
                                onclick="togglePasswordVisibility('password_confirmation', this)" type="button">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                            </button>
                        </div>
                        @error('password_confirmation', 'updatePassword')
                            <span class="mt-1 font-body-sm text-body-sm text-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="p-3.5 rounded-lg bg-surface-container-low space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Complexity Meter</span>
                        <span class="font-label-sm text-label-sm text-secondary font-semibold" id="strength-label">Requirement
                            Check</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-surface-container-highest overflow-hidden flex gap-1">
                        <div class="h-full w-1/4 rounded-full bg-surface-container-highest transition-all" id="bar-1"></div>
                        <div class="h-full w-1/4 rounded-full bg-surface-container-highest transition-all" id="bar-2"></div>
                        <div class="h-full w-1/4 rounded-full bg-surface-container-highest transition-all" id="bar-3"></div>
                        <div class="h-full w-1/4 rounded-full bg-surface-container-highest transition-all" id="bar-4"></div>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 pt-1 font-body-sm text-body-sm text-on-surface-variant">
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px] text-secondary">check</span>Min
                            8 characters</span>
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px] text-secondary">check</span>At
                            least one number</span>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-space-xs">
                    <button class="inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary text-on-secondary hover:bg-primary-container font-title-sm text-title-sm shadow-sm transition-colors"
                        type="submit">
                        <span class="material-symbols-outlined text-[18px]">key</span>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-xl bg-surface-container-low p-space-md flex items-start gap-3.5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-surface-container-highest flex items-center justify-center text-on-surface-variant shrink-0">
                <span class="material-symbols-outlined text-[22px]">contact_support</span>
            </div>
            <div class="flex flex-col gap-1">
                <h3 class="font-title-sm text-title-sm text-on-surface">Need account help?</h3>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Contact the school administrator for account activation, role, or profile corrections.
                </p>
            </div>
        </section>
    </div>

    <script>
        function togglePasswordVisibility(inputId, triggerBtn) {
            const input = document.getElementById(inputId);
            const icon = triggerBtn.querySelector('.material-symbols-outlined');
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        function checkStrength(value) {
            const bars = ['bar-1', 'bar-2', 'bar-3', 'bar-4'].map(id => document.getElementById(id));
            const label = document.getElementById('strength-label');
            if (bars.some(b => !b) || !label) return;

            let score = 0;
            if (value.length >= 8) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;

            bars.forEach(b => b.className = 'h-full w-1/4 rounded-full bg-surface-container-highest transition-all');

            const fillColor = score <= 1 ? 'bg-error' : (score <= 3 ? 'bg-secondary-container' : 'bg-secondary');
            const labels = ['Requirement Check', 'Weak', 'Moderate', 'Good', 'Strong'];
            label.textContent = value.length === 0 ? labels[0] : labels[score];

            for (let i = 0; i < Math.max(score, value.length ? 1 : 0); i++) {
                if (bars[i]) bars[i].className = `h-full w-1/4 rounded-full ${fillColor} transition-all`;
            }
        }
    </script>
@endsection
