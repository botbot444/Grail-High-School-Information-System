{{--
    Admin navigation.

    Was a flat list of 25 links with one heading. Now grouped into collapsible
    sections built from the $nav array below — add a screen by adding a row,
    not by copying forty characters of Tailwind.

    Groups use native <details>/<summary> rather than Alpine, deliberately: the
    student sidebar taught us what happens when navigation depends on JavaScript
    that fails to load. A group containing the current page is rendered open by
    the server, so the menu is always usable even with no JS at all.
--}}
@php
    /** True when any of the given route patterns matches the current request. */
    $matches = fn (array $patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p));

    $nav = [
        ['type' => 'link', 'label' => 'Dashboard', 'icon' => 'dashboard',
         'route' => 'admin.dashboard', 'match' => ['admin.dashboard']],

        ['type' => 'group', 'label' => 'People', 'icon' => 'group', 'items' => [
            ['label' => 'Students',      'icon' => 'school',           'route' => 'admin.students.index',  'match' => ['admin.students.*']],
            ['label' => 'Teachers',      'icon' => 'person_pin',       'route' => 'admin.teachers.index',  'match' => ['admin.teachers.*']],
            ['label' => 'Parents',       'icon' => 'family_restroom',  'route' => 'admin.parents.index',   'match' => ['admin.parents.*']],
            ['label' => 'User Accounts', 'icon' => 'manage_accounts',  'route' => 'admin.users.index',     'match' => ['admin.users.*']],
        ]],

        ['type' => 'group', 'label' => 'Academics', 'icon' => 'menu_book', 'items' => [
            ['label' => 'Classes',      'icon' => 'groups',                'route' => 'admin.classes.index',      'match' => ['admin.classes.*']],
            ['label' => 'Subjects',     'icon' => 'book',                  'route' => 'admin.subjects.index',     'match' => ['admin.subjects.*']],
            ['label' => 'Timetables',   'icon' => 'calendar_month',        'route' => 'admin.timetable.index',    'match' => ['admin.timetable.*']],
            ['label' => 'Examinations', 'icon' => 'assignment_turned_in',  'route' => 'admin.examinations',       'match' => ['admin.examinations']],
            ['label' => 'Report Cards', 'icon' => 'description',           'route' => 'admin.report-cards.index', 'match' => ['admin.report-cards.*']],
            ['label' => 'Promotion',    'icon' => 'moving',                'route' => 'admin.promotions.index',   'match' => ['admin.promotions.*']],
        ]],

        ['type' => 'group', 'label' => 'Finance', 'icon' => 'payments', 'items' => [
            ['label' => 'Fees',                 'icon' => 'receipt_long',      'route' => 'admin.fees.index',            'match' => ['admin.fees.index', 'admin.fees.create', 'admin.fees.edit', 'admin.fees.show', 'admin.payments.*']],
            ['label' => 'Payment Lookup',       'icon' => 'pin',               'route' => 'admin.fees.lookup',           'match' => ['admin.fees.lookup']],
            ['label' => 'Fee Categories',       'icon' => 'sell',              'route' => 'admin.categories.index',      'match' => ['admin.categories.*']],
            ['label' => 'Payment Instructions', 'icon' => 'account_balance',   'route' => 'admin.settings.payments',     'match' => ['admin.settings.payments*']],
            ['label' => 'Collection Report',    'icon' => 'bar_chart',         'route' => 'admin.reports.fee-collection','match' => ['admin.reports.fee-collection*']],
            ['label' => 'Fee Aging',            'icon' => 'hourglass_bottom',  'route' => 'admin.reports.aging',         'match' => ['admin.reports.aging*']],
        ]],

        ['type' => 'group', 'label' => 'Reports', 'icon' => 'insights', 'items' => [
            ['label' => 'School Performance', 'icon' => 'insights',    'route' => 'admin.reports.school-wide', 'match' => ['admin.reports.school-wide*']],
            ['label' => 'Attendance',         'icon' => 'fact_check',  'route' => 'admin.reports.attendance',  'match' => ['admin.reports.attendance*']],
        ]],

        ['type' => 'link', 'label' => 'Announcements', 'icon' => 'campaign',
         'route' => 'admin.announcements.index', 'match' => ['admin.announcements.*']],

        ['type' => 'group', 'label' => 'School Setup', 'icon' => 'tune', 'items' => [
            ['label' => 'Academic Years', 'icon' => 'calendar_month', 'route' => 'admin.academic-years.index', 'match' => ['admin.academic-years.*']],
            ['label' => 'Terms',          'icon' => 'view_agenda',    'route' => 'admin.terms.index',          'match' => ['admin.terms.*']],
            ['label' => 'Holidays',       'icon' => 'beach_access',   'route' => 'admin.holidays.index',       'match' => ['admin.holidays.*']],
            ['label' => 'Grade Levels',   'icon' => 'stairs',         'route' => 'admin.grade-levels.index',   'match' => ['admin.grade-levels.*']],
            ['label' => 'Periods',        'icon' => 'schedule',       'route' => 'admin.periods.index',        'match' => ['admin.periods.*']],
        ]],

        ['type' => 'link', 'label' => 'Audit Logs', 'icon' => 'history',
         'route' => 'admin.audit-logs.index', 'match' => ['admin.audit-logs.*']],
    ];

    $activeClasses = 'bg-[#004493] text-white border-l-4 border-[#adc7ff] rounded-r-lg font-bold shadow-sm';
    $idleClasses   = 'text-[#dbe4ed] hover:bg-[#004493]/80 rounded-lg';
@endphp

{{--
    Plain CSS, not Tailwind variants: hiding the disclosure marker needs a
    -webkit- rule for Safari, and a named-group variant is not worth depending
    on for a chevron.
--}}
<style>
    .nav-group > summary { list-style: none; }
    .nav-group > summary::-webkit-details-marker { display: none; }
    .nav-chevron { transition: transform 200ms ease; }
    .nav-group[open] > summary .nav-chevron { transform: rotate(180deg); }
</style>

<aside id="sidebar"
    class="w-sidebar-width h-screen fixed left-0 top-0 bg-[#001a41] border-r border-[#2d476f] z-50 flex flex-col overflow-y-auto custom-scrollbar sidebar-transition">

    <div class="px-6 py-8 flex items-center gap-3 border-b border-white/10">
        <div class="w-10 h-10 bg-[#0059bb] rounded-lg flex items-center justify-center text-white shadow-sm">
            <span class="material-symbols-outlined" style="font-variation-settings: &quot;FILL&quot; 1">school</span>
        </div>
        <div>
            <h1 class="text-title-sm font-title-sm font-bold text-white">Grail SIS</h1>
            <p class="text-[10px] uppercase tracking-[0.2em] text-[#bfc8d0] opacity-90">Admin Portal</p>
        </div>
    </div>

    <nav class="flex-1 px-4 py-4 space-y-1">
        @foreach ($nav as $entry)
            @if ($entry['type'] === 'link')
                @php $active = $matches($entry['match']); @endphp
                <a href="{{ route($entry['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 transition-colors duration-200 group {{ $active ? $activeClasses : $idleClasses }}">
                    <span class="material-symbols-outlined"
                          style="{{ $active ? 'font-variation-settings: \'FILL\' 1' : '' }}">{{ $entry['icon'] }}</span>
                    <span class="font-label-sm text-label-sm">{{ $entry['label'] }}</span>
                </a>
            @else
                @php
                    $groupActive = collect($entry['items'])->contains(fn ($item) => $matches($item['match']));
                @endphp
                <details class="nav-group" @if ($groupActive) open @endif>
                    <summary
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-colors duration-200
                               {{ $groupActive ? 'text-white' : 'text-[#dbe4ed]' }} hover:bg-[#004493]/80">
                        <span class="material-symbols-outlined">{{ $entry['icon'] }}</span>
                        <span class="font-label-sm text-label-sm flex-1">{{ $entry['label'] }}</span>
                        <span class="material-symbols-outlined nav-chevron text-[18px] text-[#9fb2c6]">expand_more</span>
                    </summary>

                    <div class="mt-1 ml-3 pl-3 border-l border-white/10 space-y-0.5">
                        @foreach ($entry['items'] as $item)
                            @php $active = $matches($item['match']); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-3 px-3 py-2 transition-colors duration-200 {{ $active ? $activeClasses : $idleClasses }}">
                                <span class="material-symbols-outlined text-[18px]"
                                      style="{{ $active ? 'font-variation-settings: \'FILL\' 1' : '' }}">{{ $item['icon'] }}</span>
                                <span class="font-label-sm text-label-sm">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>
            @endif
        @endforeach
    </nav>

    <div class="p-4 mt-auto">
        <div class="space-y-2">
            <a class="flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('admin.settings') ? $activeClasses : $idleClasses }} transition-colors duration-200 group"
                href="{{ route('admin.settings') }}">
                <span class="material-symbols-outlined"
                    style="{{ request()->routeIs('admin.settings') ? 'font-variation-settings: \'FILL\' 1' : '' }}">settings</span>
                <span class="font-label-sm text-label-sm">Settings</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" id="logout-form" style="display: none;">
                @csrf
            </form>
            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="w-full flex items-center gap-3 px-3 py-2.5 text-[#dbe4ed] hover:bg-[#004493]/80 transition-colors duration-200 rounded-lg group">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-label-sm text-label-sm">Sign Out</span>
            </button>
        </div>
    </div>
</aside>
