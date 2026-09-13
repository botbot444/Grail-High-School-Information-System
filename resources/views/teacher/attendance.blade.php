@extends('layouts.teacher')

@section('title', 'Record Attendance - Teacher Portal')

@section('page')
@php
    $counts = ['P' => 0, 'L' => 0, 'A' => 0, 'E' => 0];
    foreach ($students as $st) { $counts[$st['status']]++; }
@endphp
<div class="flex flex-col w-full gap-space-lg pb-24">
    @if ($assignments->isEmpty())
        <div class="bg-surface-container-lowest rounded-xl shadow-sm p-space-xl flex flex-col items-center text-center gap-2">
            <span class="material-symbols-outlined text-[48px] text-outline">how_to_reg</span>
            <h2 class="font-title-md text-title-md text-on-surface">No Classes Assigned</h2>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-md">You have no class subject assignments yet. Once the administrator assigns you to a class section, its roster will appear here for daily attendance.</p>
        </div>
    @else
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-space-md">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-secondary/10 text-secondary font-label-sm text-label-sm uppercase tracking-wider">Session Active</span>
                    <span class="font-data-sm text-data-sm text-on-surface-variant">SEC-{{ $assignment->class_subject_id }} · {{ $assignment->schoolClass->class_name }}</span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Record Attendance</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Mark and verify real-time presence, tardiness, and verified excuses for your roster.</p>
            </div>
            <div class="flex items-center gap-space-sm">
                <button type="button" class="inline-flex items-center gap-2 px-space-md py-2.5 rounded-lg bg-surface-container-lowest text-on-surface font-title-sm text-title-sm shadow-sm hover:bg-surface-container transition-all" id="viewLogsBtn">
                    <span class="material-symbols-outlined text-[18px] text-secondary">history</span>
                    <span>View Logs</span>
                </button>
                <button type="button" class="inline-flex items-center gap-2 px-space-md py-2.5 rounded-lg bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-title-sm text-title-sm shadow-sm transition-all" id="exportBtn">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    <span>Export History</span>
                </button>
            </div>
        </div>
        {{-- Filter & Configuration Bento Bar --}}
        <section class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm">
            <form method="GET" action="{{ route('teacher.attendance') }}">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-space-md items-end">
                    <div class="md:col-span-4 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant tracking-wider" for="classSelect">Target Section &amp; Subject</label>
                        <div class="relative">
                            <select class="w-full h-10 pl-3.5 pr-10 rounded-lg bg-surface-container-low font-body-md text-body-md text-on-surface appearance-none focus:outline-none focus:bg-surface-container-lowest shadow-[0_0_0_2px_#085bbd] transition-all cursor-pointer" id="classSelect" name="assignment_id" onchange="this.form.submit()">
                                @foreach ($assignments as $a)
                                    <option value="{{ $a->class_subject_id }}" {{ $a->class_subject_id === $assignment->class_subject_id ? 'selected' : '' }}>{{ $a->schoolClass->class_name }} ({{ $a->subject->subject_name }})</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined pointer-events-none absolute right-3 top-2.5 text-on-surface-variant text-[20px]">expand_more</span>
                        </div>
                    </div>
                    <div class="md:col-span-3 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant tracking-wider" for="attendDate">Roster Date</label>
                        <div class="relative">
                            <input class="w-full h-10 px-3 rounded-lg bg-surface-container-low font-body-md text-body-md text-on-surface focus:outline-none focus:bg-surface-container-lowest shadow-[0_0_0_2px_#085bbd] transition-all cursor-pointer" id="attendDate" type="date" name="date" value="{{ $rosterDate }}" onchange="this.form.submit()"/>
                        </div>
                    </div>
                    <div class="md:col-span-3 flex flex-col gap-1.5">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant tracking-wider" for="sessionPeriod">Session Slot</label>
                        <div class="relative">
                            <select class="w-full h-10 pl-3.5 pr-10 rounded-lg bg-surface-container-low font-body-md text-body-md text-on-surface appearance-none focus:outline-none focus:bg-surface-container-lowest shadow-[0_0_0_2px_#085bbd] transition-all cursor-pointer" id="sessionPeriod">
                                <option selected>Morning Session (Period 1-2)</option>
                                <option>Midday Session (Period 3-4)</option>
                                <option>Afternoon (Period 5-6)</option>
                                <option>Full Day Homeroom Record</option>
                            </select>
                            <span class="material-symbols-outlined pointer-events-none absolute right-3 top-2.5 text-on-surface-variant text-[20px]">schedule</span>
                        </div>
                    </div>
                    <div class="md:col-span-2 flex">
                        <button type="submit" class="w-full h-10 inline-flex items-center justify-center gap-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-md transition-all">
                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                            <span>Load Class</span>
                        </button>
                    </div>
                </div>
            </form>
        </section>
        {{-- Live Stats Strip & Bulk Action Hub --}}
        <section class="flex flex-col xl:flex-row xl:items-center justify-between gap-space-md bg-surface-container-low p-space-md rounded-xl">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[#166534] hover:bg-[#14532d] text-white font-label-md text-label-md shadow-sm transition-all" id="btnMarkAllPresent">
                    <span class="material-symbols-outlined text-[16px]">done_all</span>
                    <span>Mark All Present</span>
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-error-container hover:bg-error/20 text-error font-label-md text-label-md shadow-sm transition-all" id="btnMarkAllAbsent">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                    <span>Mark All Absent</span>
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-surface-container-lowest hover:bg-surface-container text-on-surface-variant font-label-md text-label-md shadow-sm transition-all" id="btnResetRoster">
                    <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                    <span>Reset Defaults</span>
                </button>
            </div>
            <div class="flex flex-wrap items-center gap-space-sm bg-surface-container-lowest px-4 py-2 rounded-lg shadow-sm">
                <div class="flex items-center gap-1.5 pr-2">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span class="font-label-md text-label-md text-on-surface font-semibold" id="countLoaded">{{ $students->count() }}</span>
                    <span class="font-body-sm text-body-sm text-on-surface-variant">enrolled</span>
                </div>
                <span class="text-outline-variant">•</span>
                <span class="inline-flex px-2 py-0.5 rounded-full bg-[#dcfce7] text-[#166534] font-label-sm text-label-sm font-semibold" id="countPresent">{{ $counts['P'] }} Present</span>
                <span class="inline-flex px-2 py-0.5 rounded-full bg-[#fef3c7] text-[#92400e] font-label-sm text-label-sm font-semibold" id="countLate">{{ $counts['L'] }} Late</span>
                <span class="inline-flex px-2 py-0.5 rounded-full bg-[#fee2e2] text-[#ba1a1a] font-label-sm text-label-sm font-semibold" id="countAbsent">{{ $counts['A'] }} Absent</span>
                <span class="inline-flex px-2 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-label-sm font-semibold" id="countExcused">{{ $counts['E'] }} Excused</span>
            </div>
        </section>
        {{-- Interactive Attendance Gradebook Table --}}
        <form method="POST" action="{{ route('teacher.attendance.store') }}" id="attendanceForm">
            @csrf
            <input type="hidden" name="assignment_id" value="{{ $assignment->class_subject_id }}"/>
            <input type="hidden" name="date" value="{{ $rosterDate }}"/>
            <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="attendanceTable">
                        <thead>
                            <tr class="bg-surface-container-low text-on-surface-variant">
                                <th class="py-3 px-4 font-label-sm text-label-sm uppercase tracking-wider w-12 text-center">#</th>
                                <th class="py-3 px-4 font-label-sm text-label-sm uppercase tracking-wider min-w-[240px]">Student Identifier</th>
                                <th class="py-3 px-4 font-label-sm text-label-sm uppercase tracking-wider min-w-[280px]">Status Marking</th>
                                <th class="py-3 px-4 font-label-sm text-label-sm uppercase tracking-wider min-w-[280px]">Remarks / Note</th>
                                <th class="py-3 px-4 font-label-sm text-label-sm uppercase tracking-wider w-16 text-center">Flags</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container-low" id="studentRosterBody">
                            @forelse ($students as $student)
                                @php
                                    $tints = ['L' => 'bg-[#fef3c7]/20', 'A' => 'bg-[#fee2e2]/20', 'E' => 'bg-primary-fixed/20'];
                                    $rowTint = $tints[$student['status']] ?? '';
                                    $ring = ['P' => '#085bbd', 'L' => '#92400e', 'A' => '#ba1a1a', 'E' => '#085bbd'][$student['status']] ?? '#085bbd';
                                    $pillActive = ['P' => 'bg-[#166534] text-white shadow-xs font-semibold', 'L' => 'bg-[#92400e] text-white shadow-xs font-semibold', 'A' => 'bg-[#ba1a1a] text-white shadow-xs font-semibold', 'E' => 'bg-[#085bbd] text-white shadow-xs font-semibold'];
                                @endphp
                                <tr class="hover:bg-surface-container-lowest/70 transition-colors student-row {{ $rowTint }}" data-id="{{ $student['id'] }}" data-status="{{ $student['status'] }}">
                                    <td class="py-3.5 px-4 font-data-sm text-data-sm text-on-surface-variant text-center">{{ str_pad($student['index'], 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-secondary-fixed text-on-secondary-fixed flex items-center justify-center font-title-sm text-title-sm">{{ $student['initials'] }}</div>
                                            <div class="flex flex-col">
                                                <span class="font-title-sm text-title-sm text-on-surface">{{ $student['name'] }}</span>
                                                <span class="font-data-sm text-data-sm text-on-surface-variant">ID: {{ $student['admission'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <input type="hidden" name="status[{{ $student['id'] }}]" value="{{ $student['status'] }}" class="status-input"/>
                                        <div class="inline-flex p-1 bg-surface-container-low rounded-lg gap-1 status-group">
                                            @foreach (['P', 'L', 'A', 'E'] as $val)
                                                <button type="button" class="px-3 py-1 rounded text-label-sm font-label-sm transition-all status-pill {{ $student['status'] === $val ? $pillActive[$val] : 'text-on-surface-variant hover:bg-surface-container' }}" data-val="{{ $val }}">{{ $val }}</button>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <input class="w-full h-8 px-3 rounded bg-surface-container-low/70 font-body-sm text-body-sm text-on-surface placeholder:text-outline focus:outline-none focus:bg-surface-container-lowest shadow-[0_0_0_1.5px_{{ $ring }}] transition-all remark-input" type="text" name="remarks[{{ $student['id'] }}]" value="{{ $student['remarks'] }}" placeholder="Add optional log..."/>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if ($student['status'] === 'A')
                                            <button type="button" class="text-error p-1 rounded hover:bg-surface-container transition-colors" title="Guardian flagged for review">
                                                <span class="material-symbols-outlined text-[18px]">sms_failed</span>
                                            </button>
                                        @else
                                            <button type="button" class="text-outline hover:text-secondary p-1 rounded transition-colors" title="View Student History">
                                                <span class="material-symbols-outlined text-[18px]">notes</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center">
                                        <span class="material-symbols-outlined text-[40px] text-outline block mx-auto mb-2">group_off</span>
                                        <p class="font-title-sm text-title-sm text-on-surface">No students enrolled</p>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant">This section has no enrolled students yet.</p>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
                <div class="px-space-md py-3 bg-surface-container-low flex flex-col sm:flex-row items-center justify-between gap-2">
                    <span class="font-body-sm text-body-sm text-on-surface-variant">Displaying all {{ $students->count() }} enrolled student records for {{ \Carbon\Carbon::parse($rosterDate)->format('M j, Y') }}</span>
                    <span class="font-data-sm text-data-sm text-on-surface-variant">{{ $assignment->schoolClass->class_name }} · {{ $assignment->subject->subject_name }}</span>
                </div>
            </div>
        </form>
        {{-- Sticky Bottom Verification / Commit Action Bar --}}
        <div class="fixed bottom-0 left-[260px] right-0 z-30 bg-surface-container-lowest shadow-[0_-4px_16px_rgba(0,0,0,0.06)] px-space-xl py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-space-md">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#166534] animate-pulse"></span>
                    <span class="font-title-sm text-title-sm text-on-surface" id="completionStatus">{{ $students->count() }} / {{ $students->count() }} marked</span>
                    <span class="px-2 py-0.5 rounded bg-[#dcfce7] text-[#166534] font-label-sm text-label-sm font-semibold">100% Complete</span>
                </div>
                <div class="hidden md:flex items-center gap-1.5 text-on-surface-variant font-body-sm text-body-sm pl-4">
                    <span class="material-symbols-outlined text-[16px] text-secondary">cloud_done</span>
                    <span id="autosaveLabel">Drafts stored locally until finalization</span>
                </div>
            </div>
            <div class="flex items-center gap-space-sm">
                <button type="button" class="inline-flex items-center gap-1.5 px-space-md py-2.5 rounded-lg bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-title-sm text-title-sm transition-all shadow-sm" id="btnSaveDraft">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    <span>Save Draft</span>
                </button>
                <button type="submit" form="attendanceForm" class="inline-flex items-center gap-2 px-space-lg py-2.5 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-md hover:shadow-lg transition-all" id="btnFinalizeAttendance">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                    <span>Save &amp; Finalize Attendance</span>
                </button>
            </div>
        </div>
        {{-- Toast Notification Feedback Area --}}
        <div class="fixed top-20 right-6 z-50 transform translate-y-[-150%] opacity-0 transition-all duration-300 pointer-events-none flex items-center gap-3 bg-primary-container text-on-primary px-4 py-3 rounded-xl shadow-xl" id="toastNotification">
            <span class="material-symbols-outlined text-[22px] text-secondary-container" id="toastIcon">check_circle</span>
            <div class="flex flex-col">
                <span class="font-title-sm text-title-sm" id="toastTitle">Success</span>
                <span class="font-body-sm text-body-sm text-on-primary-container" id="toastMessage">Operation completed successfully.</span>
            </div>
        </div>
        <script>
            (function () {
                const rows = Array.from(document.querySelectorAll('.student-row'));
                const form = document.getElementById('attendanceForm');
                const toastEl = document.getElementById('toastNotification');
                const toastTitle = document.getElementById('toastTitle');
                const toastMsg = document.getElementById('toastMessage');
                const toastIcon = document.getElementById('toastIcon');
                const autosaveLabel = document.getElementById('autosaveLabel');
                const statusLabels = { P: 'Present', L: 'Late', A: 'Absent', E: 'Excused' };
                const pillActive = { P: ['bg-[#166534]'], L: ['bg-[#92400e]'], A: ['bg-[#ba1a1a]'], E: ['bg-[#085bbd]'] };
                const rowTints = { L: 'bg-[#fef3c7]/20', A: 'bg-[#fee2e2]/20', E: 'bg-primary-fixed/20' };
                const ringColors = { P: '#085bbd', L: '#92400e', A: '#ba1a1a', E: '#085bbd' };
                const initialStatuses = {};
                rows.forEach(r => { initialStatuses[r.getAttribute('data-id')] = r.getAttribute('data-status'); });
                let toastTimer = null;

                function showToast(title, message, icon) {
                    if (toastTimer) clearTimeout(toastTimer);
                    toastTitle.textContent = title;
                    toastMsg.textContent = message;
                    toastIcon.textContent = icon || 'check_circle';
                    toastEl.classList.remove('translate-y-[-150%]', 'opacity-0');
                    toastEl.classList.add('translate-y-0', 'opacity-100');
                    toastTimer = setTimeout(() => {
                        toastEl.classList.add('translate-y-[-150%]', 'opacity-0');
                        toastEl.classList.remove('translate-y-0', 'opacity-100');
                    }, 3200);
                }
                window.__att = { rows, form, initialStatuses, statusLabels, pillActive, rowTints, ringColors, autosaveLabel, showToast };
            })();
        </script>
        <script>
            (function () {
                const A = window.__att;
                if (!A) return;
                const rows = A.rows;
                const form = A.form;

                function applyStatusToRow(row, status) {
                    row.setAttribute('data-status', status);
                    const id = row.getAttribute('data-id');
                    const input = form.querySelector('.status-input[name="status[' + id + ']"]');
                    if (input) input.value = status;
                    const remark = row.querySelector('.remark-input');
                    if (remark) {
                        remark.classList.remove('shadow-[0_0_0_1.5px_#085bbd]', 'shadow-[0_0_0_1.5px_#92400e]', 'shadow-[0_0_0_1.5px_#ba1a1a]');
                        remark.classList.add('shadow-[0_0_0_1.5px_' + A.ringColors[status] + ']');
                    }
                    row.classList.remove('bg-[#fef3c7]/20', 'bg-[#fee2e2]/20', 'bg-primary-fixed/20');
                    if (A.rowTints[status]) row.classList.add(A.rowTints[status]);
                    row.querySelectorAll('.status-pill').forEach(pill => {
                        const val = pill.getAttribute('data-val');
                        pill.className = 'px-3 py-1 rounded text-label-sm font-label-sm transition-all status-pill text-on-surface-variant hover:bg-surface-container';
                        if (val === status) {
                            pill.classList.remove('text-on-surface-variant', 'hover:bg-surface-container');
                            pill.classList.add('text-white', 'shadow-xs', 'font-semibold');
                            A.pillActive[val].forEach(c => pill.classList.add(c));
                        }
                    });
                    const flagBtn = row.querySelector('td:last-child button');
                    if (flagBtn) {
                        if (status === 'A') {
                            flagBtn.className = 'text-error p-1 rounded hover:bg-surface-container transition-colors';
                            flagBtn.title = 'Guardian flagged for review';
                            flagBtn.innerHTML = '<span class="material-symbols-outlined text-[18px]">sms_failed</span>';
                        } else {
                            flagBtn.className = 'text-outline hover:text-secondary p-1 rounded transition-colors';
                            flagBtn.title = 'View Student History';
                            flagBtn.innerHTML = '<span class="material-symbols-outlined text-[18px]">notes</span>';
                        }
                    }
                }
                function updateAggregations() {
                    let pC = 0, lC = 0, aC = 0, eC = 0;
                    form.querySelectorAll('.status-input').forEach(i => {
                        if (i.value === 'P') pC++; else if (i.value === 'L') lC++; else if (i.value === 'A') aC++; else eC++;
                    });
                    document.getElementById('countPresent').textContent = pC + ' Present';
                    document.getElementById('countLate').textContent = lC + ' Late';
                    document.getElementById('countAbsent').textContent = aC + ' Absent';
                    document.getElementById('countExcused').textContent = eC + ' Excused';
                }

                rows.forEach(row => {
                    row.querySelectorAll('.status-pill').forEach(btn => {
                        btn.addEventListener('click', () => {
                            applyStatusToRow(row, btn.getAttribute('data-val'));
                            updateAggregations();
                        });
                    });
                });

                document.getElementById('btnMarkAllPresent').addEventListener('click', () => {
                    rows.forEach(row => applyStatusToRow(row, 'P'));
                    updateAggregations();
                    A.showToast('Bulk Updated', 'All ' + rows.length + ' students designated as Present.', 'done_all');
                });

                document.getElementById('btnMarkAllAbsent').addEventListener('click', () => {
                    rows.forEach(row => applyStatusToRow(row, 'A'));
                    updateAggregations();
                    A.showToast('Roster Overridden', 'All students marked absent pending review.', 'warning');
                });

                document.getElementById('btnResetRoster').addEventListener('click', () => {
                    rows.forEach(row => applyStatusToRow(row, A.initialStatuses[row.getAttribute('data-id')] || 'P'));
                    updateAggregations();
                    A.showToast('Roster Reset', 'Saved records for this date reloaded.', 'restart_alt');
                });

                function draftKey() {
                    return 'att_draft_' + form.querySelector('[name="assignment_id"]').value + '_' + form.querySelector('[name="date"]').value;
                }

                document.getElementById('btnSaveDraft').addEventListener('click', () => {
                    const draft = { statuses: {}, remarks: {} };
                    form.querySelectorAll('.status-input').forEach(i => { draft.statuses[i.name] = i.value; });
                    form.querySelectorAll('.remark-input').forEach(i => { draft.remarks[i.name] = i.value; });
                    localStorage.setItem(draftKey(), JSON.stringify(draft));
                    A.autosaveLabel.textContent = 'Draft saved locally at ' + new Date().toLocaleTimeString();
                    A.showToast('Draft Saved', 'Attendance progress safely stored in your browser.', 'save');
                });

                document.getElementById('exportBtn').addEventListener('click', () => {
                    const lines = ['Index,Student,Student ID,Status,Remarks'];
                    rows.forEach(row => {
                        const name = row.querySelector('.font-title-sm').textContent.trim();
                        const sid = row.getAttribute('data-id');
                        const status = row.getAttribute('data-status');
                        const remark = row.querySelector('.remark-input').value;
                        lines.push('"' + name + '","' + sid + '","' + A.statusLabels[status] + '","' + remark.replace(/"/g, '""') + '"');
                    });
                    const blob = new Blob([lines.join('\n')], { type: 'text/csv' });
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = 'attendance_' + form.querySelector('[name="date"]').value + '.csv';
                    a.click();
                    URL.revokeObjectURL(a.href);
                    A.showToast('Export Complete', 'CSV roster report downloaded.', 'download');
                });

                document.getElementById('viewLogsBtn').addEventListener('click', () => {
                    A.showToast('Attendance Logs', 'Historical records are visible per date via the Roster Date filter.', 'history');
                });
            })();
        </script>







    @endif
</div>
@endsection
