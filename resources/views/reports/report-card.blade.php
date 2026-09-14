{{--
    Phase 11 — the report card itself.

    One template serves both the on-screen preview and the dompdf download, so
    what a parent sees is byte-for-byte what prints. dompdf understands only a
    narrow slice of CSS, so this is deliberately plain: inline styles, tables for
    layout, no flexbox or grid, no external stylesheets or webfonts.

    Expects: $student, $term, $schoolClass, $subjects, $attendance, $termAverage,
             $reportCard, $nextTerm, and $forPdf (bool).
--}}
@php
    $forPdf = $forPdf ?? false;
    $school = config('app.name', 'Grail SIS');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card — {{ $student->full_name }} — {{ $term->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #191c1d;
            background: {{ $forPdf ? '#ffffff' : '#f3f4f6' }};
            margin: 0;
            padding: {{ $forPdf ? '0' : '24px' }};
        }
        .sheet {
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
            padding: {{ $forPdf ? '0' : '32px' }};
            {!! $forPdf ? '' : 'border: 1px solid #e5e7eb; border-radius: 12px;' !!}
        }
        .masthead { border-bottom: 2px solid #0059bb; padding-bottom: 12px; margin-bottom: 16px; }
        .masthead h1 { margin: 0; font-size: 20px; color: #0059bb; letter-spacing: -0.3px; }
        .masthead p { margin: 3px 0 0; font-size: 11px; color: #414754; }
        .draft {
            background: #fef3c7; border: 1px solid #92400e; color: #92400e;
            padding: 7px 10px; margin-bottom: 14px; font-size: 11px; font-weight: bold;
        }
        table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 3px 0; font-size: 11px; vertical-align: top; }
        .meta .label { color: #414754; width: 90px; }
        .meta .value { font-weight: bold; }
        h2 {
            font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px;
            color: #414754; margin: 18px 0 7px; border-bottom: 1px solid #c1c6d7; padding-bottom: 4px;
        }
        .marks th {
            background: #eef2f8; text-align: left; padding: 6px 8px;
            font-size: 10px; text-transform: uppercase; letter-spacing: 0.4px;
            color: #414754; border-bottom: 1px solid #c1c6d7;
        }
        .marks td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .marks .num { text-align: right; white-space: nowrap; }
        .marks .fail { color: #ba1a1a; font-weight: bold; }
        .marks .pass { color: #166534; font-weight: bold; }
        .marks .remark { color: #414754; font-style: italic; font-size: 10px; }
        .summary td {
            width: 25%; padding: 10px; border: 1px solid #c1c6d7; text-align: center; vertical-align: top;
        }
        .summary .k { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #414754; }
        .summary .v { font-size: 17px; font-weight: bold; color: #0059bb; padding-top: 3px; }
        .comment { border: 1px solid #c1c6d7; padding: 10px; min-height: 46px; }
        .comment .who { font-size: 10px; color: #414754; margin-top: 6px; }
        .foot { margin-top: 22px; font-size: 10px; color: #414754; }
        .sign { margin-top: 26px; }
        .sign td { width: 50%; padding-top: 26px; font-size: 10px; color: #414754; }
        .sign .line { border-top: 1px solid #717786; padding-top: 4px; width: 78%; }
        .none { color: #717786; font-style: italic; padding: 14px 8px; }
        .toolbar { max-width: 820px; margin: 0 auto 14px; text-align: right; }
        .btn {
            display: inline-block; padding: 8px 14px; margin-left: 6px;
            border: 1px solid #c1c6d7; border-radius: 8px; background: #fff;
            color: #191c1d; font-size: 12px; font-weight: bold;
            text-decoration: none; cursor: pointer;
        }
        .btn:hover { background: #eef2f8; }
        .btn-primary { background: #0059bb; border-color: #0059bb; color: #fff; }
        .btn-primary:hover { background: #004493; }
        @media print { body { background: #fff; padding: 0; } .sheet { border: 0; padding: 0; } .noprint { display: none; } }
    </style>
</head>
<body>

@unless ($forPdf)
    {{--
        Screen-only controls. Print uses the browser's own dialog against the
        @media print rules above, so what prints is this same sheet minus the
        toolbar. The PDF link re-requests the current URL with download=1, which
        every portal's preview route accepts.
    --}}
    <div class="toolbar noprint">
        <button type="button" class="btn btn-primary" onclick="window.print()">🖨 Print report card</button>
        <a class="btn" href="{{ request()->fullUrlWithQuery(['download' => 1]) }}">⬇ Download PDF</a>
    </div>
@endunless

<div class="sheet">

    <div class="masthead">
        <h1>{{ $school }}</h1>
        <p>Student Report Card &middot; {{ $term->name }}@if ($term->academicYear) &middot; Academic Year {{ $term->academicYear->label }}@endif</p>
    </div>

    @unless ($reportCard?->isFinalized())
        <div class="draft">
            PROVISIONAL — these grades have not been finalized by the class teacher.
            Position in class is not yet assigned.
        </div>
    @endunless

    {{-- Student details --}}
    <table class="meta">
        <tr>
            <td class="label">Name</td>
            <td class="value">{{ $student->full_name }}</td>
            <td class="label">Class</td>
            <td class="value">{{ $schoolClass?->class_name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Student No.</td>
            <td class="value">{{ $student->student_number ?? '—' }}</td>
            <td class="label">Grade level</td>
            <td class="value">{{ $schoolClass?->gradeLevel?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Term</td>
            <td class="value">
                {{ $term->name }}
                @if ($term->start_date && $term->end_date)
                    ({{ $term->start_date->format('j M') }} – {{ $term->end_date->format('j M Y') }})
                @endif
            </td>
            <td class="label">Class teacher</td>
            <td class="value">{{ $schoolClass?->teacher?->full_name ?? '—' }}</td>
        </tr>
    </table>

    {{-- Marks --}}
    <h2>Subject Performance</h2>
    <table class="marks">
        <thead>
            <tr>
                <th style="width:23%">Subject</th>
                <th class="num" style="width:11%">CA</th>
                <th class="num" style="width:11%">Exam</th>
                <th class="num" style="width:9%">Total</th>
                <th class="num" style="width:9%">Grade</th>
                <th>Teacher's remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subjects as $row)
                <tr>
                    <td><strong>{{ $row['subject'] }}</strong></td>
                    <td class="num">
                        {{ $row['ca_score'] !== null ? rtrim(rtrim(number_format($row['ca_score'], 1), '0'), '.') . '/' . rtrim(rtrim(number_format($row['ca_max'], 1), '0'), '.') : '—' }}
                    </td>
                    <td class="num">
                        {{ $row['exam_score'] !== null ? rtrim(rtrim(number_format($row['exam_score'], 1), '0'), '.') . '/' . rtrim(rtrim(number_format($row['exam_max'], 1), '0'), '.') : '—' }}
                    </td>
                    <td class="num">{{ $row['total'] !== null ? $row['total'] . '%' : '—' }}</td>
                    <td class="num {{ $row['total'] !== null && $row['total'] >= 50 ? 'pass' : ($row['total'] !== null ? 'fail' : '') }}">
                        {{ $row['letter'] ?? '—' }}
                    </td>
                    <td class="remark">{{ $row['comment'] ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="none">No marks were recorded for this term.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary --}}
    <h2>Summary</h2>
    <table class="summary">
        <tr>
            <td>
                <div class="k">Term Average</div>
                <div class="v">{{ $termAverage !== null ? $termAverage . '%' : '—' }}</div>
            </td>
            <td>
                <div class="k">Position in Class</div>
                <div class="v">{{ $reportCard?->rank_label ?? 'N/A' }}</div>
            </td>
            <td>
                {{-- "Present" here means punctual: a day the student arrived on
                     time. A late arrival is still attendance, so it counts in the
                     Attendance percentage beside this but not in this figure.
                     Labelling both "present" made the two tiles look like they
                     contradicted each other — 3 of 10, next to 100%. --}}
                <div class="k">Days Punctual</div>
                <div class="v">{{ $attendance['present'] }}</div>
                <div class="k">of {{ $attendance['recorded'] }} recorded</div>
            </td>
            <td>
                <div class="k">Attendance</div>
                <div class="v">{{ $attendance['rate'] !== null ? $attendance['rate'] . '%' : '—' }}</div>
                <div class="k">{{ $attendance['absent'] }} absent &middot; {{ $attendance['late'] }} late</div>
            </td>
        </tr>
    </table>

    {{-- Class teacher comment --}}
    <h2>Class Teacher's Comment</h2>
    <div class="comment">
        {{ $reportCard?->class_teacher_comment ?: 'No comment recorded.' }}
        @if ($reportCard?->finalizedByTeacher)
            <div class="who">
                {{ $reportCard->finalizedByTeacher->full_name }} &middot;
                finalized {{ $reportCard->finalized_at->format('j M Y') }}
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="foot">
        @if ($term->end_date)
            Term ends {{ $term->end_date->format('j F Y') }}.
        @endif
        @if ($nextTerm?->start_date)
            Next term begins <strong>{{ $nextTerm->start_date->format('j F Y') }}</strong>.
        @endif
        @if ($attendance['school_days'])
            <br>School days in term: {{ $attendance['school_days'] }} (excluding weekends and holidays).
        @endif
    </div>

    <table class="sign">
        <tr>
            <td><div class="line">Class Teacher</div></td>
            <td><div class="line">Head Teacher</div></td>
        </tr>
    </table>
</div>
</body>
</html>
