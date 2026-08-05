@extends('layouts.app')

@section('title', 'Create Student')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <div class="main-content main-transition pt-[88px]" id="mainContent">
            <div class="p-8 max-w-7xl mx-auto">
                <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <nav class="flex items-center gap-2 text-sm text-on-surface-variant mb-2">
                            <a href="{{ route('admin.students.index') }}"
                                class="hover:text-primary transition-colors">Students</a>
                            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                            <span class="text-primary font-semibold">Create Student</span>
                        </nav>
                        <h1 class="text-2xl font-extrabold text-on-surface tracking-tight">Create New Student</h1>
                        <p class="text-on-surface-variant text-sm mt-1">Capture the student profile and enrollment details
                            in one place.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.students.index') }}"
                            class="flex items-center gap-2 px-4 py-2 border border-outline-variant rounded-lg bg-white text-on-surface hover:bg-surface-container-high transition-all">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                            Cancel
                        </a>
                        <button type="submit" form="student-form"
                            class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg hover:opacity-90 transition-all">
                            <span class="material-symbols-outlined text-[18px]">person_add</span>
                            Save Student
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-semibold">Please review the following issues:</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="student-form" method="POST" action="{{ route('admin.students.store') }}"
                    class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    @csrf

                    <div class="lg:col-span-8 space-y-6">
                        <section class="rounded-xl border border-outline-variant bg-white p-6 shadow-sm">
                            <div class="flex items-center gap-2 mb-6">
                                <span class="material-symbols-outlined text-primary">person</span>
                                <h2 class="text-lg font-bold text-on-surface">Personal Information</h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">First Name *</label>
                                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">Last Name *</label>
                                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">Date of Birth *</label>
                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">Gender *</label>
                                    <select name="gender" required
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male
                                        </option>
                                        <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">Student Number *</label>
                                    <input type="text" name="student_number" value="{{ old('student_number') }}" required
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">Enrollment Date</label>
                                    <input type="date" name="enrolment_date" value="{{ old('enrolment_date') }}"
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-outline-variant bg-white p-6 shadow-sm">
                            <div class="flex items-center gap-2 mb-6">
                                <span class="material-symbols-outlined text-primary">family_restroom</span>
                                <h2 class="text-lg font-bold text-on-surface">Parent / Guardian Information</h2>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">Guardian Name</label>
                                    <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">Guardian Phone</label>
                                    <input type="tel" name="guardian_phone" value="{{ old('guardian_phone') }}"
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">Link to existing
                                        parent/guardian</label>
                                    <select name="parent_user_id"
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                                        <option value="">Create new guardian details manually</option>
                                        @foreach ($parents as $parent)
                                            <option value="{{ $parent->user_id }}"
                                                {{ old('parent_user_id') == $parent->user_id ? 'selected' : '' }}>
                                                {{ $parent->full_name }} — {{ $parent->user?->email ?? 'No email' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-xs text-on-surface-variant">Select an existing parent profile to
                                        link this student to an already registered guardian.</p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="lg:col-span-4 space-y-6">
                        <section class="rounded-xl border border-outline-variant bg-white p-6 shadow-sm">
                            <div class="flex items-center gap-2 mb-6">
                                <span class="material-symbols-outlined text-primary">school</span>
                                <h2 class="text-lg font-bold text-on-surface">Enrollment Details</h2>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-on-surface">Class *</label>
                                    <select name="class_id" required
                                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                                        <option value="">Select Class</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->class_id }}"
                                                {{ old('class_id') == $class->class_id ? 'selected' : '' }}>
                                                {{ $class->class_name }} – {{ $class->grade_level }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div
                                    class="rounded-lg border border-primary/20 bg-primary/5 p-4 text-sm text-on-surface-variant">
                                    <p class="font-semibold text-on-surface">Enrollment status</p>
                                    <p class="mt-1">Students will be added to the selected class immediately after
                                        saving.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-xl border border-outline-variant bg-surface-container-low p-6">
                            <h2 class="text-lg font-bold text-on-surface">Next steps</h2>
                            <ul class="mt-3 space-y-2 text-sm text-on-surface-variant">
                                <li>• Review the submitted details before confirmation.</li>
                                <li>• The student record will appear in the student roster afterward.</li>
                                <li>• Additional academic records can be added from the student profile later.</li>
                            </ul>
                        </section>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInputs = document.querySelectorAll('input[type="date"]');
            const today = new Date().toISOString().split('T')[0];

            dateInputs.forEach(function(input) {
                if (!input.value) {
                    input.value = today;
                }
            });
        });
    </script>
@endpush
