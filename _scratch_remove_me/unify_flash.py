import pathlib, re, sys

ok = True
def patch(path, old, new, label, count=1):
    global ok
    p = pathlib.Path(path); t = p.read_text()
    if old not in t:
        print(f'  ! {label}: anchor not found'); ok = False; return
    p.write_text(t.replace(old, new, count))
    print(f'  + {label}')

V = 'resources/views/'

# ── 1. Admin layout: bare div with a legacy CSS class ────────────────────────
patch(V + 'layouts/app.blade.php',
"""        @if (session('notification'))
            <div class="notification" id="flash-notification">
                {{ session('notification') }}
            </div>
        @endif""",
"""        <x-flash />""",
'admin layout')

# ── 2. Teacher layout: toast at the bottom that hid itself after 1.5s ───────
patch(V + 'layouts/teacher.blade.php',
"""            @yield('page')
        </main>""",
"""            <x-flash />
            @yield('page')
        </main>""",
'teacher layout — flash above the content')

patch(V + 'layouts/teacher.blade.php',
"""    @if (session('notification'))
        <div id="teacher-toast"
             class="fixed bottom-6 right-6 hidden bg-surface-container-lowest border border-outline-variant text-on-surface px-space-md py-3 rounded-xl shadow-lg z-50 toast-show">
            <span class="material-symbols-outlined text-primary mr-2 align-middle">info</span>
            {{ session('notification') }}
        </div>
        <script>
            const t_toast = document.getElementById('teacher-toast');
            if (t_toast) {
                t_toast.classList.remove('hidden');
                setTimeout(() => t_toast.classList.add('toast-hide'), 1500);
            }
        </script>
    @endif
""",
"",
'teacher layout — remove the auto-hiding toast')

# ── 3. Parent layout: the same toast, duplicated ────────────────────────────
patch(V + 'layouts/parent.blade.php',
"""            @yield('page')
        </main>""",
"""            <x-flash />
            @yield('page')
        </main>""",
'parent layout — flash above the content')

patch(V + 'layouts/parent.blade.php',
"""    {{-- Toast (notification) --}}
    @if (session('notification'))
        <div id="parent-toast"
             class="fixed bottom-6 right-6 hidden bg-surface-container-lowest border border-outline-variant text-on-surface px-space-md py-3 rounded-xl shadow-lg z-50 toast-show">
            <span class="material-symbols-outlined text-primary mr-2 align-middle">info</span>
            {{ session('notification') }}
        </div>
        <script>
            const _toast = document.getElementById('parent-toast');
            if (_toast) {
                _toast.classList.remove('hidden');
                setTimeout(() => _toast.classList.add('toast-hide'), 1500);
            }
        </script>
    @endif
""",
"",
'parent layout — remove the duplicated toast')

# ── 4. Student layout: inline banner + separate error block ─────────────────
p = pathlib.Path(V + 'layouts/student.blade.php')
t = p.read_text()
m = re.search(r"            @if \(session\('notification'\)\).*?@endif\n\n            @if \(\$errors->any\(\)\).*?@endif\n", t, re.S)
if '<x-flash />' in t:
    print('  = student layout: already done')
elif m:
    p.write_text(t[:m.start()] + "            <x-flash />\n" + t[m.end():])
    print('  + student layout')
else:
    print('  ! student layout: block not matched'); ok = False

# ── 5. Views that render their own copy — now duplicates of the layout's ────
UNIFORM = [
    """        @if (session('notification'))
            <div class="mb-6 rounded-lg border border-secondary/30 bg-secondary-fixed px-4 py-3 font-body-md text-body-md text-on-surface">
                {{ session('notification') }}
            </div>
        @endif
""",
    """    @if (session('notification'))
        <div class="mb-6 rounded-lg border border-secondary/30 bg-secondary-fixed px-4 py-3 font-body-md text-body-md text-on-surface">
            {{ session('notification') }}
        </div>
    @endif
""",
]
removed = 0
for f in sorted(pathlib.Path(V).rglob('*.blade.php')):
    if f.name == 'flash.blade.php' or 'layouts/' in str(f):
        continue
    t = f.read_text(); before = t
    for block in UNIFORM:
        t = t.replace(block, '')
    # the matching error block, same two indentations
    t = re.sub(r"[ ]{4,8}@if \(\$errors->any\(\)\)\n[ ]{8,12}<div class=\"mb-6 rounded-lg border border-error/30 bg-error-container px-4 py-3\">.*?@endif\n", '', t, flags=re.S)
    if t != before:
        f.write_text(t); removed += 1
        print(f'      · de-duplicated {f.relative_to(V)}')
print(f'  + removed the per-view copies from {removed} views')

sys.exit(0 if ok else 1)
