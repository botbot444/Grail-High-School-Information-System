@extends('layouts.teacher')

@section('title', 'My Classes - Teacher Portal')

@section('page')
    {{-- Top Command & Action Bar --}}
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-space-md mb-space-xl">
        <div class="flex flex-col">
            <div class="flex items-center gap-space-xs mb-1">
                <span class="font-label-sm text-label-sm uppercase tracking-wider text-secondary">Academic Roster</span>
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-secondary-container"></span>
                <span class="font-label-sm text-label-sm text-on-surface-variant font-data-mono">{{ $classes->count() }} {{ $classes->count() === 1 ? 'SECTION' : 'SECTIONS' }} ACTIVE</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-primary tracking-tight">My Classes</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-0.5">Manage your assigned classes, section statistics, and active student rosters.</p>
        </div>
        {{-- Filter Toolset --}}
        <div class="flex flex-wrap items-center gap-space-sm">
            {{-- Grade Filter --}}
            <div class="relative min-w-[130px]">
                <select class="w-full h-10 appearance-none bg-surface-container-lowest text-on-surface font-label-md text-label-md pl-3 pr-8 rounded shadow-sm focus:outline-none focus:bg-surface-container-lowest transition-all cursor-pointer hover:bg-surface-container-low" id="gradeFilter" onchange="filterCards()">
                    <option value="all">All Grades</option>
                    @foreach ($classes->pluck('grade')->filter()->unique()->sort() as $grade)
                        <option value="{{ $grade }}">Grade {{ $grade }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-outline pointer-events-none text-[18px]">keyboard_arrow_down</span>
            </div>
            {{-- Subject Filter --}}
            <div class="relative min-w-[140px]">
                <select class="w-full h-10 appearance-none bg-surface-container-lowest text-on-surface font-label-md text-label-md pl-3 pr-8 rounded shadow-sm focus:outline-none focus:bg-surface-container-lowest transition-all cursor-pointer hover:bg-surface-container-low" id="subjectFilter" onchange="filterCards()">
                    <option value="all">All Subjects</option>
                    @foreach ($classes->pluck('subject')->unique()->sort() as $subject)
                        <option value="{{ $subject }}">{{ $subject }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-outline pointer-events-none text-[18px]">keyboard_arrow_down</span>
            </div>
            {{-- Term Selector --}}
            <div class="relative min-w-[170px]">
                <select class="w-full h-10 appearance-none bg-surface-container-lowest text-on-surface font-label-md text-label-md pl-3 pr-8 rounded shadow-sm focus:outline-none focus:bg-surface-container-lowest transition-all cursor-pointer hover:bg-surface-container-low">
                    @forelse ($terms as $term)
                        <option {{ $currentTerm && $term->term_id === $currentTerm->term_id ? 'selected' : '' }}>{{ $term->name }}</option>
                    @empty
                        <option>Term in progress</option>
                    @endforelse
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-outline pointer-events-none text-[18px]">calendar_today</span>
            </div>
            <button class="h-10 px-3 flex items-center gap-1.5 rounded bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-label-md text-label-md transition-colors shadow-sm" id="toggleEmptyBtn" onclick="toggleEmptyState()" title="Toggle Empty View Simulation">
                <span class="material-symbols-outlined text-[18px] text-secondary">visibility</span>
                <span id="toggleText">Simulate Empty</span>
            </button>
        </div>
    </div>

    {{-- KPI Overview Strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-space-md mb-space-xl">
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center gap-space-md relative overflow-hidden">
            <div class="w-12 h-12 rounded-lg bg-primary-fixed flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-[24px]">groups</span>
            </div>
            <div>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider block">Total Students</span>
                <span class="font-data-lg text-data-lg font-bold text-primary">{{ $totals['students'] }} Enrolled</span>
            </div>
            <div class="absolute right-0 bottom-0 opacity-10 text-primary pointer-events-none">
                <span class="material-symbols-outlined text-[72px] -mb-3 -mr-3">groups</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center gap-space-md relative overflow-hidden">
            <div class="w-12 h-12 rounded-lg bg-surface-container-high flex items-center justify-center text-on-secondary-container">
                <span class="material-symbols-outlined text-[24px]">analytics</span>
            </div>
            <div>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider block">Cohort Mean</span>
                <span class="font-data-lg text-data-lg font-bold text-primary">{{ $totals['mean'] }}%</span>
            </div>
            <div class="absolute right-0 bottom-0 opacity-10 text-primary pointer-events-none">
                <span class="material-symbols-outlined text-[72px] -mb-3 -mr-3">analytics</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center gap-space-md relative overflow-hidden">
            <div class="w-12 h-12 rounded-lg bg-secondary-fixed flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-[24px]">event_available</span>
            </div>
            <div>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider block">Attendance Rate</span>
                <span class="font-data-lg text-data-lg font-bold text-primary">{{ $totals['attendance'] }}%</span>
            </div>
            <div class="absolute right-0 bottom-0 opacity-10 text-secondary pointer-events-none">
                <span class="material-symbols-outlined text-[72px] -mb-3 -mr-3">event_available</span>
            </div>
        </div>
        <div class="bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex items-center gap-space-md relative overflow-hidden">
            <div class="w-12 h-12 rounded-lg bg-tertiary-fixed flex items-center justify-center text-tertiary-container">
                <span class="material-symbols-outlined text-[24px]">domain</span>
            </div>
            <div>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider block">Sections Active</span>
                <span class="font-data-lg text-data-lg font-bold text-primary">{{ $totals['facilities'] }} {{ $totals['facilities'] === 1 ? 'Section' : 'Sections' }}</span>
            </div>
            <div class="absolute right-0 bottom-0 opacity-10 text-tertiary pointer-events-none">
                <span class="material-symbols-outlined text-[72px] -mb-3 -mr-3">domain</span>
            </div>
        </div>
    </div>

    {{-- Primary Class Grid (3 columns on lg/xl) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-space-lg" id="classesGrid">
        @forelse ($classes as $card)
            <div class="class-card group bg-surface-container-lowest rounded-xl shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden relative" data-grade="{{ $card['grade'] }}" data-subject="{{ $card['subject'] }}">
                @if ($card['is_homeroom'])
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-primary-container"></div>
                @endif
                <div class="p-space-lg flex-1 {{ $card['is_homeroom'] ? 'pl-space-xl' : '' }}">
                    <div class="flex items-start justify-between gap-2 mb-space-md">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-surface-container-high text-primary font-label-sm text-label-sm font-semibold">
                                <span class="material-symbols-outlined text-[14px] text-secondary">{{ $card['subject_icon'] }}</span>
                                {{ $card['subject'] }}
                            </span>
                            @if ($card['is_homeroom'])
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-primary-container text-primary-fixed font-label-sm text-label-sm font-bold shadow-sm">
                                    <span class="material-symbols-outlined text-[14px] text-secondary-container">hotel_class</span>
                                    Homeroom
                                </span>
                            @endif
                        </div>
                        <span class="font-label-sm text-label-sm font-data-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">{{ $card['section'] }}</span>
                    </div>
                    <div class="mb-space-md">
                        <div class="flex items-baseline justify-between">
                            <h2 class="font-headline-md text-headline-md text-primary group-hover:text-secondary transition-colors">{{ $card['name'] }}</h2>
                            <span class="font-label-sm text-label-sm text-outline font-data-mono">@if($currentTerm){{ $currentTerm->name }}@else{{ $card['grade_level'] ?? 'Section' }}@endif</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-on-surface-variant font-body-sm text-body-sm mt-1">
                            <span class="material-symbols-outlined text-[16px] text-secondary">gavel</span>
                            <span>{{ $card['grade_level'] }} · {{ $card['roster'] }} students</span>
                        </div>
                    </div>
                    <div class="bg-surface-container-low rounded-lg p-space-sm mb-space-md">
                        <div class="flex items-center justify-between text-on-surface-variant mb-1">
                            <span class="font-label-sm text-label-sm uppercase font-medium">Class Average</span>
                            <span class="font-data-md text-data-md font-bold text-primary">{{ $card['avg_percent'] }}%</span>
                        </div>
                        <div class="w-full bg-surface-container-highest h-2 rounded-full overflow-hidden">
                            <div class="bg-secondary h-full rounded-full transition-all duration-500" style="width: {{ min($card['avg_percent'], 100) }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center py-2 bg-surface-bright rounded">
                        <div class="flex flex-col">
                            <span class="font-label-sm text-label-sm text-on-surface-variant">Roster</span>
                            <span class="font-data-lg text-data-lg font-bold text-primary">{{ $card['roster'] }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-label-sm text-label-sm text-on-surface-variant">Avg Grade</span>
                            <span class="font-data-lg text-data-lg font-bold text-primary">{{ $card['avg_grade'] }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-label-sm text-label-sm text-on-surface-variant">Attendance</span>
                            <span class="font-data-lg text-data-lg font-bold text-secondary">{{ $card['attendance_pct'] }}%</span>
                        </div>
                    </div>
                </div>
                <div class="bg-surface-container-low px-space-md py-space-sm flex items-center justify-between gap-1 {{ $card['is_homeroom'] ? 'pl-space-xl' : '' }}">
                    <a class="flex-1 text-center py-2 px-1 rounded hover:bg-surface-container-highest text-secondary font-label-md text-label-md transition-colors flex items-center justify-center gap-1" href="{{ route('teacher.classes.roster', ['class' => $card['class_id']]) }}">
                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                        <span>Roster</span>
                    </a>
                    <a class="flex-1 text-center py-2 px-1 rounded hover:bg-surface-container-highest text-on-surface font-label-md text-label-md transition-colors flex items-center justify-center gap-1" href="{{ $card['marks_url'] }}">
                        <span class="material-symbols-outlined text-[16px]">edit_note</span>
                        <span>Marks</span>
                    </a>
                    <a class="flex-1 text-center py-2 px-1 rounded bg-secondary hover:bg-on-secondary-container text-on-primary font-label-md text-label-md transition-colors flex items-center justify-center gap-1 shadow-sm" href="{{ route('teacher.attendance') }}">
                        <span class="material-symbols-outlined text-[16px]">how_to_reg</span>
                        <span>Attend</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center text-center p-space-xl bg-surface-container-lowest rounded-xl shadow-sm my-space-lg col-span-full" id="emptyGrid">
                <div class="w-20 h-20 rounded-full bg-surface-container flex items-center justify-center text-outline mb-space-md">
                    <span class="material-symbols-outlined text-[42px]">school</span>
                </div>
                <h3 class="font-headline-sm text-headline-sm text-primary mb-1">No Classes Assigned Yet</h3>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-md mb-space-lg">There are currently no instructional class sections mapped to your teacher profile for {{ $currentTerm?->name ?? 'the current term' }}. Please reach out to the academic registrar if your schedule is incomplete.</p>
                <div class="flex items-center gap-space-sm">
                    <span class="px-space-md py-2 rounded bg-surface-container-high text-on-surface font-label-md text-label-md">No sections assigned</span>
                    <a class="px-space-md py-2 rounded bg-secondary hover:bg-on-secondary-container text-on-primary font-label-md text-label-md transition-colors flex items-center gap-1 shadow-sm" href="mailto:registrar@grail.edu">
                        <span class="material-symbols-outlined text-[18px]">support_agent</span>
                        <span>Contact Registrar</span>
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Empty state shown when filters match nothing --}}
    <div class="hidden flex-col items-center justify-center text-center p-space-xl bg-surface-container-lowest rounded-xl shadow-sm my-space-lg" id="emptyState">
        <div class="w-20 h-20 rounded-full bg-surface-container flex items-center justify-center text-outline mb-space-md">
            <span class="material-symbols-outlined text-[42px]">filter_alt_off</span>
        </div>
        <h3 class="font-headline-sm text-headline-sm text-primary mb-1">No Classes Match the Filters</h3>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-md mb-space-lg">Try resetting the grade or subject filters to see all your sections.</p>
        <button class="px-space-md py-2 rounded bg-secondary hover:bg-on-secondary-container text-on-primary font-label-md text-label-md transition-colors flex items-center gap-1 shadow-sm" onclick="filterCards('reset')">
            <span class="material-symbols-outlined text-[18px]">restart_alt</span>
            <span>Reset Filters</span>
        </button>
    </div>

    {{-- Editorial Helper Context Section --}}
    <div class="mt-space-xl bg-surface-container-low rounded-xl p-space-md flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md">
        <div class="flex items-center gap-space-md">
            <div class="w-10 h-10 rounded-full bg-secondary-fixed flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-[20px]">info</span>
            </div>
            <div>
                <span class="font-title-sm text-title-sm text-primary block">@if($currentTerm){{ $currentTerm->name }}@else Current @endif Grading Period In Progress</span>
                <span class="font-body-sm text-body-sm text-on-surface-variant">Ensure all formative marks and attendance records are entered before the grading deadline.</span>
            </div>
        </div>
        <div class="flex items-center gap-space-sm self-end md:self-auto">
            <a class="px-3 py-1.5 rounded bg-surface-container-lowest text-primary hover:bg-surface-container-high text-label-md font-label-md transition-colors shadow-sm" href="{{ route('teacher.timetable') }}">View Academic Calendar</a>
        </div>
    </div>

    <script>
        function filterCards(reset) {
            if (reset) {
                document.getElementById('gradeFilter').value = 'all';
                document.getElementById('subjectFilter').value = 'all';
            }
            const gradeVal = document.getElementById('gradeFilter').value;
            const subjectVal = document.getElementById('subjectFilter').value;
            const cards = document.querySelectorAll('.class-card');
            let visibleCount = 0;
            cards.forEach(card => {
                const cardGrade = card.getAttribute('data-grade');
                const cardSubject = card.getAttribute('data-subject');
                const matchesGrade = (gradeVal === 'all' || cardGrade === gradeVal);
                const matchesSubject = (subjectVal === 'all' || cardSubject === subjectVal);
                if (matchesGrade && matchesSubject) {
                    card.classList.remove('hidden');
                    card.classList.add('flex');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                    card.classList.remove('flex');
                }
            });
            const gridElem = document.getElementById('classesGrid');
            const emptyElem = document.getElementById('emptyState');
            if (visibleCount === 0 && emptyElem) {
                emptyElem.classList.remove('hidden');
                emptyElem.classList.add('flex');
                if (gridElem) gridElem.classList.add('hidden');
            } else {
                emptyElem.classList.add('hidden');
                emptyElem.classList.remove('flex');
                if (gridElem) gridElem.classList.remove('hidden');
            }
        }

        function toggleEmptyState() {
            const emptyElem = document.getElementById('emptyState');
            const gridElem = document.getElementById('classesGrid');
            const btnText = document.getElementById('toggleText');
            const isCurrentlyEmpty = !emptyElem.classList.contains('hidden');
            if (isCurrentlyEmpty) {
                emptyElem.classList.add('hidden');
                emptyElem.classList.remove('flex');
                gridElem.classList.remove('hidden');
                btnText.textContent = 'Simulate Empty';
                document.getElementById('gradeFilter').value = 'all';
                document.getElementById('subjectFilter').value = 'all';
                filterCards();
            } else {
                emptyElem.classList.remove('hidden');
                emptyElem.classList.add('flex');
                gridElem.classList.add('hidden');
                btnText.textContent = 'Show Classes';
            }
        }
    </script>
@endsection