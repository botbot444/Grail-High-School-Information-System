import pathlib, sys

ok = True

def patch(path, old, new, label, count=1):
    global ok
    p = pathlib.Path(path); t = p.read_text()
    if old not in t:
        print(f'  ! {label}: anchor not found'); ok = False; return
    n = t.count(old) if count == 0 else count
    p.write_text(t.replace(old, new) if count == 0 else t.replace(old, new, count))
    print(f'  + {label}: {n} replacement(s)')

FALLBACK = """        [x-cloak] { display: none !important; }

        /*
            Fallback for when Alpine has not run.

            Every width, offset and label in this shell is an Alpine binding, so
            if the bundle does not load — dev server down, a stale public/hot
            pointing at an old LAN IP, a blocked request, a JS error earlier on
            the page — the sidebar ends up with no width and every x-cloak'd
            label stays hidden. That looks exactly like a collapsed sidebar that
            refuses to expand, which is the worst possible failure mode: the
            control that would fix it is the thing that disappeared.

            :where() keeps these at zero specificity, so the instant Alpine
            applies a real class it wins.
        */
        @media (min-width: 768px) {
            :where(.student-shell aside)  { width: 260px; }
            :where(.student-shell main)   { margin-left: 260px; }
            :where(.student-shell header) { left: 260px; }
        }
        @media (max-width: 767.98px) {
            :where(.student-shell aside)  { transform: translateX(-100%); }
        }"""

patch('resources/views/layouts/student.blade.php',
      '        [x-cloak] { display: none !important; }',
      FALLBACK,
      'layout: no-JS fallback CSS')

# The header's static left-0 would out-specify the fallback on desktop, so scope
# it to mobile. Alpine's md:left-* bindings still beat the fallback.
patch('resources/views/student/partials/header.blade.php',
      'class="header-transition fixed top-0 right-0 left-0 h-header-height',
      'class="header-transition fixed top-0 right-0 max-md:left-0 h-header-height',
      'header: scope left-0 to mobile')

# Labels keep x-show (Alpine hides them when collapsed) but lose x-cloak, so
# with no Alpine they simply stay visible rather than vanishing forever.
patch('resources/views/student/partials/sidebar.blade.php',
      'x-show="!collapsed || mobileOpen" x-cloak',
      'x-show="!collapsed || mobileOpen"',
      'sidebar: labels visible without Alpine', count=0)

patch('resources/views/student/partials/sidebar.blade.php',
      'x-show="!collapsed" x-cloak',
      'x-show="!collapsed"',
      'sidebar: collapse-button label', count=0)

sys.exit(0 if ok else 1)
