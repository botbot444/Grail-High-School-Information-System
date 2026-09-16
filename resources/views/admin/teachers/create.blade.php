{{-- resources/views/admin/teachers/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add New Teacher')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">

        @include('admin.sidebar')
        @include('admin.header')

        <main id="mainContent"
            class="ml-[260px] pt-[72px] min-h-screen bg-background p-container-padding main-transition flex-1">
        @include('admin.partials.flash')

            <div class="pb-12 max-w-[1400px] mx-auto">

                <!-- ==================== BREADCRUMB & HEADER ==================== -->
                <div class="flex flex-col gap-3 mb-8">
                    <nav
                        class="flex items-center gap-2 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                        <a class="hover:text-primary transition-colors" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <a class="hover:text-primary transition-colors"
                            href="{{ route('admin.teachers.index') }}">Teachers</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <span class="text-primary font-bold">Add New</span>
                    </nav>
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-1">
                        <div>
                            <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight">Add New Teacher</h1>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-2xl">
                                Create a new faculty record, configure teaching assignments, and issue system credentials.
                            </p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0 flex-wrap">
                            <a href="{{ route('admin.teachers.index') }}"
                                class="px-4 h-9 rounded-lg bg-surface-container-lowest text-on-surface font-label-sm text-label-sm shadow-sm hover:bg-surface-container-low transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                                Cancel
                            </a>
                            <button type="button" onclick="saveAsDraft()"
                                class="px-4 h-9 rounded-lg bg-surface-container-low text-on-surface-variant font-label-sm text-label-sm hover:bg-surface-container transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">drafts</span>
                                Save Draft
                            </button>
                            <button type="submit" form="teacherForm"
                                class="px-4 h-9 rounded-lg bg-primary text-on-primary font-label-sm text-label-sm shadow-sm hover:bg-primary-container transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">person_add</span>
                                Create Teacher
                            </button>
                        </div>
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

                <!-- ==================== FORM ==================== -->
                <form action="{{ route('admin.teachers.store') }}" method="POST" id="teacherForm">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        <!-- ===== LEFT COLUMN ===== -->
                        <div class="lg:col-span-7 flex flex-col gap-6">

                            <!-- Card: Personal Information -->
                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                <div class="flex items-center justify-between pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-secondary"></span>
                                        <h2 class="font-title-sm text-title-sm text-on-surface">Personal Information</h2>
                                    </div>
                                    <span
                                        class="font-data-mono text-data-mono text-on-surface-variant bg-surface-container-low px-2 py-0.5 rounded">SEC-01</span>
                                </div>

                                <!-- Faculty account note -->
                                <div
                                    class="flex flex-col sm:flex-row items-start gap-6 mb-6 p-3 bg-surface-container-low rounded-xl">
                                    <div
                                        class="w-16 h-16 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-[28px]">person</span>
                                    </div>
                                    <div class="flex flex-col justify-center">
                                        <h3 class="font-title-sm text-title-sm text-on-surface">Faculty Account</h3>
                                        <p class="font-body-md text-body-md text-on-surface-variant mt-0.5">
                                            A login account (role: teacher) is created automatically with default
                                            credentials.
                                            The faculty photograph and additional HR fields will be added once supported.
                                        </p>
                                    </div>
                                </div>

                                <!-- Personal Fields -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex flex-col">
                                        <label class="font-label-sm text-label-sm text-on-surface-variant mb-1.5 uppercase"
                                            for="firstName">
                                            First Name <span class="text-error">*</span>
                                        </label>
                                        <input id="firstName" name="first_name" value="{{ old('first_name') }}"
                                            required="" type="text" placeholder="e.g. Eleanor"
                                            class="h-[38px] px-3 bg-surface-container-lowest text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all shadow-xs @error('first_name') border border-error @enderror" />
                                        @error('first_name')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="font-label-sm text-label-sm text-on-surface-variant mb-1.5 uppercase"
                                            for="lastName">
                                            Last Name <span class="text-error">*</span>
                                        </label>
                                        <input id="lastName" name="last_name" value="{{ old('last_name') }}" required=""
                                            type="text" placeholder="e.g. Vance"
                                            class="h-[38px] px-3 bg-surface-container-lowest text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all shadow-xs @error('last_name') border border-error @enderror" />
                                        @error('last_name')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </section>
                            <!-- Card: Academic Assignments -->
                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                <div class="flex items-center justify-between pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-secondary"></span>
                                        <h2 class="font-title-sm text-title-sm text-on-surface">Academic Assignments &amp; Workload</h2>
                                    </div>
                                    <span class="font-label-sm text-label-sm text-primary font-semibold leading-tight"
                                        id="selectedSubjectsCounter">
                                        @if ($subjects->isEmpty())
                                            0 subjects available
                                        @else
                                            0 subjects selected
                                        @endif
                                    </span>
                                </div>

                                <!-- Teaching Subjects (data-driven from $subjects) -->
                                <div class="mb-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <label
                                            class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                                            Teaching Subjects <span class="text-error">*</span>
                                        </label>
                                        <span class="font-data-mono text-[11px] text-on-surface-variant">Click to toggle
                                            curriculum links</span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5" id="subjectGrid">
                                        @if ($subjects->isEmpty())
                                            <p class="text-on-surface-variant text-sm">No subjects available yet.</p>
                                        @else
                                            @foreach ($subjects as $subject)
                                                <label
                                                    class="group relative flex items-center gap-2.5 p-2.5 rounded-lg bg-surface-container-low cursor-pointer hover:bg-surface-container transition-all checkbox-card"
                                                    onclick="toggleCheckbox(this)">
                                                    <input class="w-4 h-4 rounded accent-secondary cursor-pointer"
                                                        name="subject_ids[]" onchange="handleSubjectToggle(this)"
                                                        type="checkbox" value="{{ $subject->subject_id }}"
                                                        {{ in_array($subject->subject_id, old('subject_ids', [])) ? 'checked' : '' }} />
                                                    <span
                                                        class="font-label-sm text-label-sm text-on-surface">{{ $subject->subject_name }}</span>
                                                </label>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <!-- Assigned Homeroom Classes (data-driven from $classes) -->
                                <div>
                                    <label
                                        class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider block mb-2">
                                        Assigned Homeroom / Classes
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        @if ($classes->isEmpty())
                                            <p class="text-on-surface-variant text-sm">No classes available yet.</p>
                                        @else
                                            @foreach ($classes as $class)
                                                <label
                                                    class="group flex items-center gap-2.5 p-2.5 rounded-lg bg-surface-container-low cursor-pointer hover:bg-surface-container transition-all checkbox-card"
                                                    onclick="toggleCheckbox(this)">
                                                    <input class="w-4 h-4 rounded accent-secondary cursor-pointer"
                                                        name="class_ids[]" type="checkbox"
                                                        value="{{ $class->class_id }}" />
                                                    <div class="flex flex-col">
                                                        <span
                                                            class="font-label-sm text-label-sm text-on-surface">{{ $class->class_name }}</span>
                                                        <span
                                                            class="font-data-mono text-[10px] text-on-surface-variant">{{ $class->grade_level_name }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </section>
                            <!-- ===== RIGHT COLUMN ===== -->
                            <div class="lg:col-span-5 flex flex-col gap-6">

                                <!-- Card: Contact Details -->
                                <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                    <div class="flex items-center justify-between pb-3 mb-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-4 rounded-full bg-secondary"></span>
                                            <h2 class="font-title-sm text-title-sm text-on-surface">Contact Details</h2>
                                        </div>
                                        <span
                                            class="material-symbols-outlined text-on-surface-variant text-[20px]">alternate_email</span>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex flex-col">
                                            <label
                                                class="font-label-sm text-label-sm text-on-surface-variant mb-1.5 uppercase"
                                                for="email">
                                                Institutional Email <span class="text-error">*</span>
                                            </label>
                                            <div class="relative">
                                                <span
                                                    class="material-symbols-outlined absolute left-2.5 top-2 text-on-surface-variant text-[18px]">mail</span>
                                                <input id="email" name="email" value="{{ old('email') }}"
                                                    required="" type="email" placeholder="teacher@grail.edu"
                                                    class="w-full h-[38px] pl-9 pr-3 bg-surface-container-lowest text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all shadow-xs @error('email') border border-error @enderror" />
                                            </div>
                                            <span class="text-[11px] text-on-surface-variant mt-1">Login account
                                                credentials will be created for this address.</span>
                                            @error('email')
                                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="flex flex-col">
                                            <label
                                                class="font-label-sm text-label-sm text-on-surface-variant mb-1.5 uppercase"
                                                for="phone">
                                                Primary Phone Number <span class="text-error">*</span>
                                            </label>
                                            <div class="relative">
                                                <span
                                                    class="material-symbols-outlined absolute left-2.5 top-2 text-on-surface-variant text-[18px]">phone</span>
                                                <input id="phone" name="phone" value="{{ old('phone') }}"
                                                    required="" type="tel" placeholder="+260 97 000 0000"
                                                    class="w-full h-[38px] pl-9 pr-3 bg-surface-container-lowest text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all shadow-xs @error('phone') border border-error @enderror" />
                                            </div>
                                            @error('phone')
                                                <p class="text-error text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </section>

                                <!-- Card: Account / Defaults note -->
                                <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                    <div class="flex items-center justify-between pb-3 mb-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-4 rounded-full bg-secondary"></span>
                                            <h2 class="font-title-sm text-title-sm text-on-surface">Login &amp; Defaults
                                            </h2>
                                        </div>
                                        <span
                                            class="font-data-mono text-data-mono text-on-surface-variant bg-surface-container-low px-2 py-0.5 rounded">AUTH</span>
                                    </div>
                                    <div
                                        class="rounded-lg bg-surface-container-low p-4 text-body-md text-on-surface-variant">
                                        <p class="flex items-center gap-2 font-label-sm text-label-sm text-on-surface">
                                            <span class="material-symbols-outlined text-[18px] text-primary">key</span>
                                            On create
                                        </p>
                                        <ul class="mt-2 text-sm list-disc list-inside space-y-1">
                                            <li>A <strong>teacher</strong> user account is created with default credentials.
                                            </li>
                                            <li>Selected subjects are assigned to the teacher independently of homeroom
                                                classes.</li>
                                            <li>Selected classes are assigned as the teacher's homeroom.</li>
                                        </ul>
                                    </div>
                                </section>
                            </div>
                        </div>
                </form>
            </div>
        </main>

    </div>
@endsection
<!-- ==================== TOAST NOTIFICATION ==================== -->
<div id="toast"
    class="fixed bottom-8 right-8 bg-inverse-surface text-inverse-on-surface px-6 py-4 rounded-lg shadow-xl translate-y-20 opacity-0 transition-all duration-300 flex items-center gap-3 z-[100]">
    <span class="material-symbols-outlined text-green-400">check_circle</span>
    <div class="flex flex-col">
        <span class="font-label-sm text-label-sm font-semibold leading-tight">Faculty Created</span>
        <span class="font-body-sm text-body-sm leading-tight mt-0.5" id="toastMessage">Faculty record created
            successfully.</span>
    </div>
</div>

@push('scripts')
    <script>
        // ===================================================
        // 1. SHOULD SET A DEFAULT EMPLOYMENT DATE - REMOVED
        //    (no employment date column exists)
        // ===================================================

        // ===================================================
        // 2. CHECKBOX CARD TOGGLE
        // ===================================================
        function toggleCheckbox(labelElement) {
            const checkbox = labelElement.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        }

        // ===================================================
        // 3. SUBJECT SELECTION COUNTER & SELECTED STYLING
        // ===================================================
        function handleSubjectToggle(checkbox) {
            const selected = document.querySelectorAll('input[name="subject_ids[]"]:checked');
            const counter = document.getElementById('selectedSubjectsCounter');
            counter.textContent = `${selected.length} subject${selected.length === 1 ? '' : 's'} selected`;

            const parentLabel = checkbox.closest('label');
            if (checkbox.checked) {
                parentLabel.classList.add('bg-secondary-fixed/50', 'selected');
                parentLabel.classList.remove('bg-surface-container-low');
            } else {
                parentLabel.classList.remove('bg-secondary-fixed/50', 'selected');
                parentLabel.classList.add('bg-surface-container-low');
            }
        }

        // ===================================================
        // 4. TOAST FEEDBACK (flash messages + save draft)
        // ===================================================
        function showToast(message) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            if (message) {
                toastMessage.textContent = message;
            }
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                hideToast();
            }, 4500);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('translate-y-20', 'opacity-0');
        }

        function saveAsDraft() {
            showToast('Form draft saved to your session for this page.');
        }

        // ===================================================
        // 5. FORM SUBMIT - LOADING STATE
        // ===================================================
        document.getElementById('teacherForm').addEventListener('submit', function() {
            const buttons = this.querySelectorAll('button[type="submit"]');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-80', 'pointer-events-none');
            });
        });

        // ===================================================
        // 7. CLEAR ERROR STYLING ON INPUT
        // ===================================================
        document.querySelectorAll('#teacherForm input, #teacherForm select, #teacherForm textarea').forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('border-error', 'ring-1', 'ring-error');
            });
            field.addEventListener('change', function() {
                this.classList.remove('border-error', 'ring-1', 'ring-error');
            });
        });

        console.log('📝 Add Teacher page loaded successfully');
    </script>
@endpush
