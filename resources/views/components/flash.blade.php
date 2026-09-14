{{--
    resources/views/components/flash.blade.php

    The one flash message in the application.

    Before this there were four treatments for the same idea: a bare
    <div class="notification"> in the admin layout, an inline banner in the
    student layout, and a bottom-right toast duplicated in the teacher and
    parent layouts that hid itself after 1.5 seconds — too fast to finish
    reading, and hopeless for anything you need to act on, like a one-time
    password. On top of that, twenty-odd views rendered their own copy.

    This component reads every session key the codebase actually flashes,
    picks a severity from the key rather than from the caller, and is built
    from the same design tokens as the rest of the UI, so it looks native in
    all four portals without any of them restyling it.

    Usage:  <x-flash />          anywhere inside a layout's content area.
--}}

@props(['class' => ''])

@php
    // Severity comes from the key, so a controller does not have to remember
    // which colour to ask for — flashing 'error' is red because it is an error.
    $messages = collect([
        ['key' => 'error',        'level' => 'error'],
        ['key' => 'notification', 'level' => 'success'],
        ['key' => 'success',      'level' => 'success'],
        ['key' => 'message',      'level' => 'info'],
        ['key' => 'status',       'level' => 'info'],
    ])->filter(fn ($m) => filled(session($m['key'])))
      ->map(fn ($m) => $m + ['text' => session($m['key'])]);

    $styles = [
        'success' => [
            'icon'   => 'check_circle',
            'shell'  => 'border-secondary/30 bg-secondary-fixed',
            'badge'  => 'bg-primary-container text-on-primary',
            'text'   => 'text-on-surface',
        ],
        'error' => [
            'icon'   => 'error',
            'shell'  => 'border-error/30 bg-error-container',
            'badge'  => 'bg-error text-on-error',
            'text'   => 'text-on-error-container',
        ],
        'info' => [
            'icon'   => 'info',
            'shell'  => 'border-outline-variant bg-surface-container-low',
            'badge'  => 'bg-surface-container-high text-on-surface-variant',
            'text'   => 'text-on-surface',
        ],
    ];

    $password = session('temporary_password');
    $summary  = session('assignment_summary');
@endphp

@if ($messages->isNotEmpty() || $errors->any())
    <div class="flex flex-col gap-3 mb-6 {{ $class }}">

        @foreach ($messages as $message)
            @php $s = $styles[$message['level']]; @endphp
            <div role="{{ $message['level'] === 'error' ? 'alert' : 'status' }}"
                 class="grail-flash rounded-xl border px-4 py-3.5 flex items-start gap-3 {{ $s['shell'] }}">

                <span class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center {{ $s['badge'] }}">
                    <span class="material-symbols-outlined text-[18px]">{{ $password && $message['level'] === 'success' ? 'key' : $s['icon'] }}</span>
                </span>

                <div class="flex-1 min-w-0">
                    <p class="font-title-sm text-title-sm {{ $s['text'] }}">{{ $message['text'] }}</p>

                    {{-- A one-time password is shown once and must be readable and
                         copyable, which is precisely what an auto-hiding toast
                         could never do. --}}
                    @if ($password && $message['level'] === 'success')
                        <div class="mt-2.5 flex flex-wrap items-center gap-2">
                            <code class="font-data-mono text-data-mono bg-surface-container-lowest border border-outline-variant text-on-surface px-3 py-1.5 rounded-lg tracking-wider select-all">{{ $password }}</code>
                            <button type="button" onclick="grailCopyFlash(this)" data-password="{{ $password }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:text-primary hover:border-primary font-label-sm text-label-sm transition-colors">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                                <span>Copy</span>
                            </button>
                        </div>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-2">
                            Write this down or share it now — it is shown once and cannot be retrieved later.
                        </p>
                    @endif

                    @if ($summary && $message['level'] !== 'error')
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1.5">{{ $summary }}</p>
                    @endif
                </div>

                <button type="button" onclick="this.closest('.grail-flash').remove()" aria-label="Dismiss"
                    class="shrink-0 -mr-1 -mt-0.5 p-1 rounded-lg text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-colors">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        @endforeach

        @if ($errors->any())
            <div role="alert" class="grail-flash rounded-xl border border-error/30 bg-error-container px-4 py-3.5 flex items-start gap-3">
                <span class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center bg-error text-on-error">
                    <span class="material-symbols-outlined text-[18px]">error</span>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="font-title-sm text-title-sm text-on-error-container">
                        {{ $errors->count() === 1 ? 'There is a problem' : 'There are ' . $errors->count() . ' problems' }}
                    </p>
                    <ul class="mt-1.5 ml-4 list-disc space-y-1 font-body-sm text-body-sm text-on-error-container">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" onclick="this.closest('.grail-flash').remove()" aria-label="Dismiss"
                    class="shrink-0 -mr-1 -mt-0.5 p-1 rounded-lg text-on-error-container/70 hover:text-on-error-container transition-colors">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        @endif
    </div>

    @once
        <script>
            function grailCopyFlash(button) {
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
                    try { done(document.execCommand('copy')); } catch (e) { done(false); }
                    document.body.removeChild(tmp);
                }
            }
        </script>
    @endonce
@endif
