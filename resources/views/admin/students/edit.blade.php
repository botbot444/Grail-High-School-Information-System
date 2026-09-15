{{-- resources/views/admin/students/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Student: ' . $student->full_name)

@section('content')
<div id="view-admin" class="app-view" style="display:flex;">

    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="ml-[260px] pt-[72px] min-h-screen bg-background p-container-padding main-transition flex-1">
        @include('admin.partials.flash')

        <div class="pb-12 max-w-[1400px] mx-auto">

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                        <a class="font-label-sm text-label-sm hover:text-primary transition-colors"
                            href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <a class="font-label-sm text-label-sm hover:text-primary transition-colors"
                            href="{{ route('admin.students.index') }}">Students</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <a class="font-label-sm text-label-sm hover:text-primary transition-colors"
                            href="{{ route('admin.students.show', $student->student_id) }}">{{ $student->full_name }}</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <span class="font-label-sm text-label-sm text-on-surface font-semibold">Edit</span>
                    </nav>
                    <h1 class="font-headline-md text-headline-md text-on-surface">Edit Student: {{ $student->full_name }}</h1>
                </div>
            </div>

            <!-- Validation Summary -->
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

            <!-- Form -->
            <form action="{{ route('admin.students.update', $student->student_id) }}" method="POST"
                enctype="multipart/form-data" id="studentForm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-12 gap-gutter">
                    <!-- ==================== LEFT COLUMN ==================== -->
                    <div class="col-span-12 lg:col-span-4 space-y-gutter">
                        <!-- Profile Card -->
                        <div
                            class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 flex flex-col items-center text-center shadow-sm">
                            <div class="relative group cursor-pointer mb-4">
                                <div id="avatarInitials"
                                    class="w-32 h-32 rounded-full border-4 border-surface-container bg-primary-container text-on-primary-container font-extrabold text-4xl flex items-center justify-center">
                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                </div>
                                <img id="avatarPreview"
                                    class="hidden absolute inset-0 w-32 h-32 rounded-full object-cover border-4 border-surface-container"
                                    alt="{{ $student->full_name }}">
                                <div
                                    class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <label class="cursor-pointer">
                                        <span class="material-symbols-outlined text-white">photo_camera</span>
                                        <input type="file" name="avatar" class="hidden" accept="image/*"
                                            onchange="previewAvatar(event)">
                                    </label>
                                </div>
                            </div>
                            <h2 class="font-title-sm text-title-sm text-on-surface mb-1">{{ $student->full_name }}</h2>
                            <span
                                class="inline-flex items-center px-3 py-1 bg-primary-container/10 text-primary-container font-label-sm text-label-sm rounded-full font-bold">
                                {{ $student->student_number }}
                            </span>

                            <div class="w-full mt-6 pt-6 border-t border-outline-variant space-y-4 text-left">
                                <div class="flex justify-between items-center">
                                    <span class="text-on-surface-variant font-label-sm text-label-sm">Academic Status</span>
                                    <span class="text-green-600 font-label-sm text-label-sm font-semibold flex items-center gap-1">
                                        <span class="w-2 h-2 bg-green-600 rounded-full"></span>
                                        Active
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-on-surface-variant font-label-sm text-label-sm">Attendance</span>
                                    <span class="text-on-surface font-label-sm text-label-sm font-semibold">
                                        @if (!is_null($attendanceRate))
                                            {{ $attendanceRate }}%
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-on-surface-variant font-label-sm text-label-sm">Enrolled Since</span>
                                    <span class="text-on-surface font-label-sm text-label-sm font-semibold">
                                        {{ $student->enrolment_date?->format('M d, Y') ?? '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                            <h3 class="font-title-sm text-title-sm text-on-surface mb-4">Quick Shortcuts</h3>
                            <div class="space-y-2">
                                <a href="{{ route('admin.students.show', $student->student_id) }}"
                                    class="w-full text-left px-4 py-3 rounded hover:bg-surface-container text-body-md flex items-center gap-3 transition-all border border-transparent hover:border-outline-variant">
                                    <span class="material-symbols-outlined text-primary">person</span>
                                    View Full Profile
                                </a>
                                <a href="{{ route('admin.audit-logs.index') }}"
                                    class="w-full text-left px-4 py-3 rounded hover:bg-surface-container text-body-md flex items-center gap-3 transition-all border border-transparent hover:border-outline-variant">
                                    <span class="material-symbols-outlined text-primary">history</span>
                                    View Activity Log
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== RIGHT COLUMN ==================== -->
                    <div class="col-span-12 lg:col-span-8 space-y-gutter">

                        <!-- ===== SECTION 1: PERSONAL INFORMATION ===== -->
                        <section
                            class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between bg-surface-container-low">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-primary">person</span>
                                    <h3 class="font-title-sm text-title-sm text-on-surface">Personal Information</h3>
                                </div>
                                <span class="text-on-surface-variant text-[11px] uppercase font-bold tracking-widest">Section 01</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- First Name -->
                                <div class="space-y-1.5">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        class="w-full rounded bg-surface border px-4 py-2.5 text-body-md border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('first_name') border-red-500 @enderror"
                                        type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required>
                                    @error('first_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Last Name -->
                                <div class="space-y-1.5">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        class="w-full rounded bg-surface border px-4 py-2.5 text-body-md border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('last_name') border-red-500 @enderror"
                                        type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required>
                                    @error('last_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Date of Birth -->
                                <div class="space-y-1.5">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        Date of Birth <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            class="w-full rounded bg-surface border px-4 py-2.5 text-body-md border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('date_of_birth') border-red-500 @enderror"
                                            type="date" name="date_of_birth"
                                            value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}" required>
                                        <span
                                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">calendar_today</span>
                                    </div>
                                    @error('date_of_birth')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Gender -->
                                <div class="space-y-1.5">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        Gender <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        class="w-full rounded bg-surface border px-4 py-2.5 text-body-md border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('gender') border-red-500 @enderror"
                                        name="gender" required>
                                        <option value="">Select Gender</option>
                                        @foreach (['Male', 'Female'] as $gender)
                                            <option value="{{ $gender }}" {{ old('gender', $student->gender) == $gender ? 'selected' : '' }}>
                                                {{ $gender }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gender')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <!-- ===== SECTION 2: ENROLLMENT & ACADEMIC ===== -->
                        <section
                            class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between bg-surface-container-low">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-primary">school</span>
                                    <h3 class="font-title-sm text-title-sm text-on-surface">Enrollment & Academic</h3>
                                </div>
                                <span class="text-on-surface-variant text-[11px] uppercase font-bold tracking-widest">Section 02</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Student ID (Read Only) -->
                                <div class="space-y-1.5">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        Student ID
                                    </label>
                                    <div
                                        class="w-full bg-surface-container-high border border-outline-variant rounded px-4 py-2.5 text-on-surface-variant font-data-mono text-data-mono flex items-center justify-between">
                                        {{ $student->student_number }}
                                        <span class="material-symbols-outlined text-[16px]">lock</span>
                                    </div>
                                    <p class="text-[11px] text-on-surface-variant mt-1">Immutable system-generated identifier.</p>
                                </div>

                                <!-- Class -->
                                <div class="space-y-1.5">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        Class <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        class="w-full rounded bg-surface border px-4 py-2.5 text-body-md border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('class_id') border-red-500 @enderror"
                                        name="class_id" required>
                                        <option value="">Select Class</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->class_id }}"
                                                {{ old('class_id', $student->class_id) == $class->class_id ? 'selected' : '' }}>
                                                {{ $class->class_name }} – {{ $class->grade_level_name }}
                                                @if ($class->teacher)
                                                    · {{ $class->teacher->full_name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Enrolment Date -->
                                <div class="space-y-1.5">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        Enrolment Date <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            class="w-full rounded bg-surface border px-4 py-2.5 text-body-md border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('enrolment_date') border-red-500 @enderror"
                                            type="date" name="enrolment_date"
                                            value="{{ old('enrolment_date', $student->enrolment_date?->format('Y-m-d')) }}" required>
                                        <span
                                            class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">event</span>
                                    </div>
                                    @error('enrolment_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <!-- ===== SECTION 3: PARENT/GUARDIAN INFORMATION ===== -->
                        <section
                            class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-outline-variant flex items-center justify-between bg-surface-container-low">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-primary">family_restroom</span>
                                    <h3 class="font-title-sm text-title-sm text-on-surface">Parent/Guardian Information</h3>
                                </div>
                                <span class="text-on-surface-variant text-[11px] uppercase font-bold tracking-widest">Section 03</span>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Guardian Name -->
                                <div class="space-y-1.5">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        Guardian Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        class="w-full rounded bg-surface border px-4 py-2.5 text-body-md border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('guardian_name') border-red-500 @enderror"
                                        type="text" name="guardian_name"
                                        value="{{ old('guardian_name', $student->guardian_name) }}" required>
                                    @error('guardian_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Guardian Phone -->
                                <div class="space-y-1.5">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        Guardian Phone <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        class="w-full rounded bg-surface border px-4 py-2.5 text-body-md border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('guardian_phone') border-red-500 @enderror"
                                        type="tel" name="guardian_phone"
                                        value="{{ old('guardian_phone', $student->guardian_phone) }}" required>
                                    @error('guardian_phone')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Linked Accounts (read-only info) -->
                                <div class="space-y-1.5 md:col-span-2">
                                    <label class="font-label-sm text-label-sm text-on-surface font-semibold uppercase tracking-wide">
                                        Linked Accounts
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div
                                            class="bg-surface-container-low border border-outline-variant rounded px-4 py-3 text-body-md">
                                            <span class="text-on-surface-variant font-label-sm text-label-sm">Student login:</span>
                                            @if ($student->user)
                                                <span class="text-on-surface font-semibold">{{ $student->user->email }}</span>
                                            @else
                                                <span class="text-on-surface-variant italic">No student account linked</span>
                                            @endif
                                        </div>
                                        <div class="bg-surface-container-low border border-outline-variant rounded px-4 py-3 text-body-md">
                                            <span class="text-on-surface-variant font-label-sm text-label-sm">Parent login:</span>
                                            @if ($student->parentUser)
                                                <span class="text-on-surface font-semibold">{{ $student->parentUser->email }}</span>
                                            @else
                                                <span class="text-on-surface-variant italic">No parent account linked</span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-on-surface-variant mt-1">
                                        Account links are managed via the user account system and cannot be changed here.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- ===== FORM ACTIONS ===== -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 pb-8">
                            <div class="flex items-center gap-4 text-on-surface-variant">
                                <span class="material-symbols-outlined">info</span>
                                <p class="font-label-sm text-label-sm italic">
                                    Last updated:
                                    {{ $student->updated_at?->format('M d, Y') ?? 'Never' }}
                                    @if ($student->updated_at)
                                        at {{ $student->updated_at->format('h:i A') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3 w-full sm:w-auto flex-wrap justify-center sm:justify-end">
                                <button type="button" onclick="confirmDelete()"
                                    class="flex items-center gap-2 px-4 py-2 text-error border border-error hover:bg-error hover:text-white transition-all rounded font-label-sm text-label-sm">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                    Delete Student
                                </button>
                                <a href="{{ route('admin.students.show', $student->student_id) }}"
                                    class="flex items-center gap-2 px-4 py-2 text-on-surface-variant bg-surface-container-low hover:bg-surface-container-high transition-all rounded font-label-sm text-label-sm border border-outline-variant">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                    Cancel
                                </a>
                                <button type="submit"
                                    class="flex items-center gap-2 px-6 py-2 bg-primary text-white hover:bg-primary-container transition-all rounded font-label-sm text-label-sm shadow-sm active:scale-95"
                                    id="submitBtn">
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                    Update Student
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

</div>
@endsection

<!-- ==================== DELETE MODAL ==================== -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex items-center gap-3 mb-4">
            <span class="material-symbols-outlined text-error text-3xl">warning</span>
            <h3 class="text-xl font-bold text-gray-900">Delete Student</h3>
        </div>
        <p class="text-gray-600 mb-2">
            Are you sure you want to delete <strong class="text-[#003461]">{{ $student->full_name }}</strong>?
        </p>
        <p class="text-gray-500 text-sm mb-6">
            This action <span class="text-red-500 font-semibold">cannot be undone</span> and will permanently remove:
        </p>
        <ul class="text-sm text-gray-600 space-y-1 mb-6 list-disc list-inside">
            <li>All student profile data</li>
            <li>Academic records and grades</li>
            <li>Attendance history</li>
            <li>Fee records</li>
            <li>Associated user account (if exists)</li>
        </ul>
        <div class="flex items-center justify-end gap-3">
            <button onclick="closeDeleteModal()"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <form action="{{ route('admin.students.destroy', $student->student_id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Yes, Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ==================== TOAST NOTIFICATION ==================== -->
<div id="toast"
    class="fixed bottom-8 right-8 bg-inverse-surface text-inverse-on-surface px-6 py-4 rounded-lg shadow-xl translate-y-20 opacity-0 transition-all duration-300 flex items-center gap-3 z-[100]">
    <span class="material-symbols-outlined text-green-400">check_circle</span>
    <span class="font-label-sm text-label-sm" id="toastMessage">Student record updated successfully.</span>
</div>

@push('scripts')
    <script>
        // ===================================================
        // 1. AVATAR PREVIEW (client-side only; avatar upload is
        //    not persisted until an avatar column exists)
        // ===================================================
        function previewAvatar(event) {
            const input = event.target;
            const preview = document.getElementById('avatarPreview');
            const initials = document.getElementById('avatarInitials');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (initials) initials.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // ===================================================
        // 2. FORM SUBMIT WITH LOADING STATE
        // ===================================================
        document.getElementById('studentForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML =
                '<span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span> Saving...';
            submitBtn.classList.add('opacity-80', 'pointer-events-none');
            submitBtn.disabled = true;
            formChanged = false;
        });

        // ===================================================
        // 3. DELETE MODAL
        // ===================================================
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Close modal on backdrop click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDeleteModal();
        });

        // ===================================================
        // 4. TOAST (server-side flash messages)
        // ===================================================
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const icon = toast.querySelector('.material-symbols-outlined');

            toastMessage.textContent = message;

            if (type === 'error') {
                icon.textContent = 'error';
                icon.className = 'material-symbols-outlined text-red-400';
            } else {
                icon.textContent = 'check_circle';
                icon.className = 'material-symbols-outlined text-green-400';
            }

            toast.classList.remove('translate-y-20', 'opacity-0');

            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 4000);
        }

        @if (session('notification'))
            showToast(@js(session('notification')), 'success');
        @endif

        @if (session('error'))
            showToast(@js(session('error')), 'error');
        @endif

        // ===================================================
        // 5. CONFIRM BEFORE NAVIGATION (if form is dirty)
        // ===================================================
        let formChanged = false;

        document.querySelectorAll('#studentForm input, #studentForm select, #studentForm textarea').forEach(field => {
            field.addEventListener('change', function() {
                formChanged = true;
            });
            field.addEventListener('input', function() {
                formChanged = true;
            });
        });

        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });

        console.log('📝 Edit Student page loaded successfully');
    </script>
@endpush
