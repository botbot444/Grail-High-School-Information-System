@extends('layouts.app')

@section('title', 'Teacher Marks')

@section('content')

    <div class="teacher-container">

        <div class="teacher-header">

            <div>
                <h1>Teacher Mark Entry</h1>
                <p style="color: gray;">Manage daily attendance and student assessment records</p>
            </div>

            <div style="display: flex; gap: 15px; align-items: center;">
                <div class="teacher-info">
                    <strong>{{ Auth::user()->name }}</strong><br>
                    @if (count($assignments) > 0)
                        {{ $assignments->first()->subject->subject_name ?? 'N/A' }}
                    @endif
                </div>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn-header" style="background: #ef4444; cursor: pointer;">
                        Logout
                    </button>
                </form>
            </div>

        </div>

        @if ($assignments->isEmpty())

            <div class="table-section">
                <p style="color: gray; text-align: center; padding: 25px;">No assignments found for this teacher.</p>
            </div>
        @else
            <div class="filters">

                <div class="filter-group">

                    <div class="input-group">
                        <label>Select Class/Subject</label>
                        <select style="padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none;">
                            @foreach ($assignments as $asn)
                                <option value="{{ $asn->class_subject_id }}"
                                    {{ $asn->class_subject_id === $assignment->class_subject_id ? 'selected' : '' }}>
                                    {{ $asn->schoolClass->class_name }} – {{ $asn->subject->subject_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <button class="btn" style="background: #177aa4; color: white; height: 45px;">
                    <i class="fa-solid fa-users"></i> View Class List
                </button>

            </div>

            <form method="POST" action="{{ route('teacher.marks.store') }}">

                @csrf

                <input type="hidden" name="assignment_id" value="{{ $assignment->class_subject_id }}">

                <div class="table-section">

                    <h2 style="color: #177aa4;">Student Records ({{ $assignment->schoolClass->class_name }})</h2>

                    <table>

                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Daily Attendance</th>
                                <th>Marks /100</th>
                                <th>Performance</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($students as $student)
                                <tr>

                                    <td>{{ $student['name'] }}</td>

                                    <td>
                                        <select class="attendance-select" name="attendance[{{ $student['id'] }}]">
                                            <option value="P" {{ $student['attendance'] === 'P' ? 'selected' : '' }}>
                                                Present</option>
                                            <option value="A" {{ $student['attendance'] === 'A' ? 'selected' : '' }}>
                                                Absent</option>
                                            <option value="L" {{ $student['attendance'] === 'L' ? 'selected' : '' }}>
                                                Late</option>
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number" class="marks-input" name="marks[{{ $student['id'] }}]"
                                            min="0" max="100" value="{{ $student['mark'] }}">
                                    </td>

                                    <td>
                                        @php
                                            $mark = $student['mark'];
                                            if ($mark >= 75) {
                                                $performance = 'Excellent';
                                                $class = 'good';
                                            } elseif ($mark >= 50) {
                                                $performance = 'Average';
                                                $class = 'average';
                                            } else {
                                                $performance = 'Poor';
                                                $class = 'poor';
                                            }
                                        @endphp
                                        <span class="status {{ $class }}">{{ $performance }}</span>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: gray;">No students in this class.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                    <div class="buttons" style="justify-content: space-between; margin-top: 25px;">

                        <div style="display: flex; gap: 15px;">
                            <button type="submit" class="btn save-btn">
                                <i class="fa-solid fa-floppy-disk"></i> Save Records
                            </button>
                            <button type="button" class="btn submit-btn" onclick="confirmSubmit()">
                                <i class="fa-solid fa-paper-plane"></i> Submit Final Grades
                            </button>
                        </div>

                        <div style="display: flex; gap: 15px;">
                            <button type="button" class="btn"
                                style="background: white; border: 1px solid #177aa4; color: #177aa4;"
                                onclick="generateReport()">
                                <i class="fa-solid fa-file-pdf"></i> Generate Class Report
                            </button>
                            <button type="reset" class="btn reset-btn">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </button>
                        </div>

                    </div>

                </div>

            </form>

        @endif

    </div>

    <script>
        function confirmSubmit() {
            if (confirm(
                    'Are you sure you want to submit these final grades to the Admin? They cannot be altered after submission.'
                    )) {
                alert('Final grades submitted successfully! ✓');
            }
        }

        function generateReport() {
            alert('Report generated successfully! ✓');
        }

        // Auto-update performance status based on marks input
        document.querySelectorAll('.marks-input').forEach((input) => {
            input.addEventListener('input', () => {
                let value = parseInt(input.value) || 0;
                const row = input.closest('tr');
                const status = row.querySelector('.status');

                if (value > 100) input.value = 100;
                if (value < 0) input.value = 0;

                if (value >= 75) {
                    status.innerHTML = 'Excellent';
                    status.className = 'status good';
                } else if (value >= 50) {
                    status.innerHTML = 'Average';
                    status.className = 'status average';
                } else {
                    status.innerHTML = 'Poor';
                    status.className = 'status poor';
                }
            });
        });
    </script>

@endsection
