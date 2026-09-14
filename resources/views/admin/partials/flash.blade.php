{{--
    resources/views/admin/partials/flash.blade.php

    Shared flash-message banner for the admin portal: the session
    ('notification' / 'temporary_password' / 'temporary_password_for')
    and validation-error ($errors) states, styled with the app's actual
    design tokens instead of ad-hoc colors so it matches whichever admin
    page includes it. @include('admin.partials.flash') wherever a page
    redirects back with a flash message.
--}}
@if (session('notification'))
    <div class="mb-6 rounded-xl border border-secondary/30 bg-secondary-fixed px-4 py-3.5 flex items-start gap-3">
        <div class="w-9 h-9 rounded-full bg-primary-container text-on-primary flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[18px]">{{ session('temporary_password') ? 'key' : 'check_circle' }}</span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-title-sm text-title-sm text-on-surface">{{ session('notification') }}</p>

            @if (session('temporary_password'))
                <div class="mt-2.5 flex flex-wrap items-center gap-2">
                    <code class="font-data-mono text-data-mono bg-surface-container-lowest border border-outline-variant text-on-surface px-3 py-1.5 rounded-lg tracking-wider select-all">{{ session('temporary_password') }}</code>
                    <button type="button" onclick="grailCopyFlashPassword(this)"
                        data-password="{{ session('temporary_password') }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:text-primary hover:border-primary font-label-sm text-label-sm transition-colors">
                        <span class="material-symbols-outlined text-[16px]">content_copy</span>
                        <span>Copy</span>
                    </button>
                </div>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-2">
                    Write this down or share it now — it is shown once and cannot be retrieved later.
                </p>
            @endif
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                function grailCopyFlashPassword(button) {
                    const password = button.getAttribute('data-password');
                    const label = button.querySelector('span:last-child');
                    const restore = label.textContent;

                    const done = (ok) => {
                        label.textContent = ok ? 'Copied!' : 'Copy failed';
                        setTimeout(() => { label.textContent = restore; }, 2000);
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(password).then(() => done(true)).catch(() => done(false));
                    } else {
                        const tmp = document.createElement('textarea');
                        tmp.value = password;
                        tmp.style.position = 'fixed';
                        tmp.style.opacity = '0';
                        document.body.appendChild(tmp);
                        tmp.select();
                        try {
                            done(document.execCommand('copy'));
                        } catch (e) {
                            done(false);
                        }
                        document.body.removeChild(tmp);
                    }
                }
            </script>
        @endpush
    @endonce
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-error/30 bg-error-container px-4 py-3.5 flex items-start gap-3">
        <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">error</span>
        <ul class="list-disc ml-4 font-body-sm text-body-sm text-on-error-container space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
