@extends('layouts.app')

@section('title', 'Student Portal')

@section('content')

    <div id="view-student" class="app-view" style="display: block; background: #f1f5f9;">

        <div class="portal-header" style="background: linear-gradient(135deg, #177aa4, #9de2ec);">

            <div>
                <h1>Student Portal 🎓</h1>
                <p>Track your academic performance and school standing</p>
            </div>

            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn-header">
                    Logout
                </button>
            </form>

        </div>

        <div class="portal-container">

            <div class="profile-card">

                <div style="display: flex; align-items: center; gap: 20px;">
                    <div>
                        <h2>{{ $student->full_name }}</h2>
                        <p style="color: gray;">{{ $student->schoolClass?->class_name ?? 'N/A' }} | GPA: 4.3</p>
                    </div>
                </div>

                <button class="btn" style="background: #61b0e6; color: white; font-size: 0.85rem; padding: 8px 12px;">
                    <i class="fa-solid fa-key"></i> Change Password
                </button>

            </div>

            <div class="portal-cards">

                <div class="portal-card">
                    <i class="fa-solid fa-calendar-check"></i>
                    <h3>My Attendance</h3>
                    <p style="color: gray;">{{ $attendanceRate }}% Rate (Present
                        {{ round(($attendanceRate / 100) * 45) }}/45
                        days)</p>
                    <div class="progress-bar">
                        <div class="progress" style="width: {{ $attendanceRate }}%;"></div>
                    </div>
                </div>

                <div class="portal-card">
                    <i class="fa-solid fa-money-check"></i>
                    <h3>My Fee Status</h3>
                    <p style="color: #ef4444; font-weight: bold; font-size: 1.2rem;">
                        {{ $feeStatus }}
                    </p>
                    <p style="color: gray; font-size: 0.85rem; margin-top: 5px;">
                        @if ($feeBalance > 0)
                            Balance: K{{ number_format($feeBalance) }}
                        @else
                            Account Cleared
                        @endif
                    </p>
                </div>

            </div>

            <div class="table-section" style="margin-top: 25px;">

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="color: #177aa4;">My Subjects & Grades</h2>
                    <button class="btn" style="background: #177aa4; color: white; padding: 8px 15px;">
                        <i class="fa-solid fa-download"></i> Download Report Card
                    </button>
                </div>

                <table>

                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Grade</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($results as $result)
                            <tr>
                                <td>{{ $result->classSubject->subject->subject_name ?? 'N/A' }}</td>
                                <td>{{ $result->marks }}</td>
                                <td>
                                    @php
                                        $marks = $result->marks;
                                        if ($marks >= 80) {
                                            $grade = 'A';
                                        } elseif ($marks >= 70) {
                                            $grade = 'B+';
                                        } elseif ($marks >= 60) {
                                            $grade = 'B';
                                        } elseif ($marks >= 50) {
                                            $grade = 'C';
                                        } else {
                                            $grade = 'F';
                                        }
                                    @endphp
                                    {{ $grade }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: gray;">No grade records yet.</td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection
