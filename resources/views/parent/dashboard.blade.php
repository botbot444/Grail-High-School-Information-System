@extends('layouts.app')

@section('title', 'Parent Portal')

@section('content')

    <div id="view-parent" class="app-view" style="display:block;">

        <div class="portal-header">

            <div>
                <h1>Parent Portal 👨‍👩‍👧</h1>
                <p>Monitor your child's academic progress, attendance, and fees</p>
            </div>

            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn-header">
                    Logout
                </button>
            </form>

        </div>

        <div class="portal-container">

            @if (!$student)

                <div class="profile-card" style="text-align: center; padding: 40px;">
                    <p style="color: gray; font-size: 1.1rem;">No students linked to your account.</p>
                </div>
            @else
                <div class="profile-card">

                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div>
                            <h2>{{ $student->full_name }}</h2>
                            <p style="color: gray;">{{ $student->schoolClass?->class_name ?? 'N/A' }} | Admission No:
                                {{ $student->student_number }}</p>
                        </div>
                    </div>

                    <button class="btn"
                        style="background: #61b0e6; color: white; font-size: 0.85rem; padding: 8px 12px;">
                        <i class="fa-solid fa-pen-to-square"></i> Update Contact Info
                    </button>

                </div>

                <div class="portal-cards">

                    <div class="portal-card">
                        <i class="fa-solid fa-calendar-check"></i>
                        <h3>Attendance</h3>
                        <p style="color: gray;">{{ $attendanceRate }}% Rate (Present
                            {{ round(($attendanceRate / 100) * 45) }}/45 days)</p>
                        <div class="progress-bar">
                            <div class="progress" style="width: {{ $attendanceRate }}%;"></div>
                        </div>
                    </div>

                    <div class="portal-card">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <h3>Fee Balance</h3>
                        <p style="color: #ef4444; font-weight: bold; font-size: 1.2rem;">
                            @if ($feeBalance > 0)
                                K{{ number_format($feeBalance) }} Due
                            @else
                                Cleared
                            @endif
                        </p>
                        <button class="btn"
                            style="background: #e2e8f0; color: #177aa4; width: 100%; margin-top: 15px; font-size: 0.85rem;">
                            <i class="fa-solid fa-clock-rotate-left"></i> View Payment History
                        </button>
                    </div>

                    <div class="portal-card" style="background: #fff5f5; border: 1px solid #fee2e2;">
                        <i class="fa-solid fa-bell" style="color: #ef4444;"></i>
                        <h3 style="color: #ef4444;">Alerts & Notifications</h3>
                        <p style="color: gray; font-size: 0.9rem; margin-top: 10px;">
                            <strong>Notice:</strong> Term fees are due next week Friday.
                        </p>
                    </div>

                </div>

                <div class="table-section" style="margin-top: 25px;">

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h2 style="color: #177aa4;">Latest Results</h2>
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

            @endif

        </div>

    </div>

@endsection
