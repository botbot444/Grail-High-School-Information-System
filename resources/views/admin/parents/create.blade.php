{{-- resources/views/admin/parents/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Add New Parent')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">

        @include('admin.sidebar')
        @include('admin.header')

        <main id="mainContent"
            class="ml-[260px] pt-[72px] min-h-screen bg-background p-container-padding main-transition flex-1">
            <div class="pb-28 max-w-[1400px] mx-auto">

                <!-- ==================== BREADCRUMB & HEADER ==================== -->
                <div class="flex flex-col gap-3 mb-8">
                    <nav
                        class="flex items-center gap-2 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                        <a class="hover:text-primary transition-colors" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <a class="hover:text-primary transition-colors"
                            href="{{ route('admin.parents.index') }}">Parents &amp; Guardians</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <span class="text-primary font-bold">Add New</span>
                    </nav>
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-1">
                        <div>
                            <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight">Add New Parent /
                                Guardian</h1>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-2xl">
                                Register a guardian profile and link enrolled students to grant them portal access.
                            </p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0 flex-wrap">
                            <a href="{{ route('admin.parents.index') }}"
                                class="px-4 h-9 rounded-lg bg-surface-container-lowest text-on-surface font-label-sm text-label-sm shadow-sm hover:bg-surface-container-low transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                                Cancel
                            </a>
                            <button type="submit" form="parentForm"
                                class="px-4 h-9 rounded-lg bg-primary text-on-primary font-label-sm text-label-sm shadow-sm hover:bg-primary-container transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">person_add</span>
                                Create Parent
                            </button>
                        </div>
                    </div>
                </div>

                @include('admin.partials.flash')

                <!-- ==================== FORM ==================== -->
                <form action="{{ route('admin.parents.store') }}" method="POST" id="parentForm">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                        <!-- ===== LEFT COLUMN ===== -->
                        <div class="lg:col-span-7 flex flex-col gap-6">

                            <!-- Card: Personal Information -->
                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-surface-container">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary-container">
                                        <span class="material-symbols-outlined text-[22px]">person</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Personal Information
                                        </h2>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant">Official identity and
                                            residential details.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex flex-col">
                                        <label class="font-label-form text-label-form text-on-surface-variant mb-1.5 uppercase"
                                            for="firstName">
                                            First Name <span class="text-error">*</span>
                                        </label>
                                        <input id="firstName" name="first_name" value="{{ old('first_name') }}"
                                            required type="text" placeholder="e.g. Maria"
                                            class="h-[38px] px-3 bg-surface-container-low text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all @error('first_name') border border-error @enderror" />
                                        @error('first_name')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="font-label-form text-label-form text-on-surface-variant mb-1.5 uppercase"
                                            for="lastName">
                                            Last Name <span class="text-error">*</span>
                                        </label>
                                        <input id="lastName" name="last_name" value="{{ old('last_name') }}" required
                                            type="text" placeholder="e.g. Banda"
                                            class="h-[38px] px-3 bg-surface-container-low text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all @error('last_name') border border-error @enderror" />
                                        @error('last_name')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex flex-col sm:col-span-2">
                                        <label class="font-label-form text-label-form text-on-surface-variant mb-1.5 uppercase"
                                            for="address">
                                            Address
                                        </label>
                                        <textarea id="address" name="address" rows="2" placeholder="Street, city, postal code"
                                            class="px-3 py-2.5 bg-surface-container-low text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all resize-none @error('address') border border-error @enderror">{{ old('address') }}</textarea>
                                        @error('address')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="font-label-form text-label-form text-on-surface-variant mb-1.5 uppercase"
                                            for="occupation">
                                            Occupation
                                        </label>
                                        <input id="occupation" name="occupation" value="{{ old('occupation') }}"
                                            type="text" placeholder="e.g. Accountant"
                                            class="h-[38px] px-3 bg-surface-container-low text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all @error('occupation') border border-error @enderror" />
                                        @error('occupation')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="font-label-form text-label-form text-on-surface-variant mb-1.5 uppercase"
                                            for="nationalId">
                                            National ID
                                        </label>
                                        <input id="nationalId" name="national_id" value="{{ old('national_id') }}"
                                            type="text" placeholder="e.g. 123456/78/1"
                                            class="h-[38px] px-3 bg-surface-container-low text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all font-data-mono @error('national_id') border border-error @enderror" />
                                        @error('national_id')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- ===== RIGHT COLUMN ===== -->
                        <div class="lg:col-span-5 flex flex-col gap-6">

                            <!-- Card: Contact Information -->
                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-surface-container">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary-container">
                                        <span class="material-symbols-outlined text-[22px]">contact_phone</span>
                                    </div>
                                    <div>
                                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Contact Information
                                        </h2>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant">Direct
                                            communication channels.</p>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-4">
                                    <div class="flex flex-col">
                                        <label class="font-label-form text-label-form text-on-surface-variant mb-1.5 uppercase"
                                            for="email">
                                            Email <span class="text-error">*</span>
                                        </label>
                                        <div class="relative">
                                            <span
                                                class="material-symbols-outlined absolute left-2.5 top-2 text-on-surface-variant text-[18px]">mail</span>
                                            <input id="email" name="email" value="{{ old('email') }}" required
                                                type="email" placeholder="guardian@example.com"
                                                class="w-full h-[38px] pl-9 pr-3 bg-surface-container-low text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all @error('email') border border-error @enderror" />
                                        </div>
                                        @error('email')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="font-label-form text-label-form text-on-surface-variant mb-1.5 uppercase"
                                            for="phone">
                                            Phone
                                        </label>
                                        <div class="relative">
                                            <span
                                                class="material-symbols-outlined absolute left-2.5 top-2 text-on-surface-variant text-[18px]">call</span>
                                            <input id="phone" name="phone" value="{{ old('phone') }}" type="tel"
                                                placeholder="+260 97 000 0000"
                                                class="w-full h-[38px] pl-9 pr-3 bg-surface-container-low text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all font-data-mono @error('phone') border border-error @enderror" />
                                        </div>
                                        @error('phone')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <!-- Card: Link Children -->
                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm flex flex-col">
                                <div class="flex items-center justify-between pb-4 mb-3 border-b border-surface-container">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary-container">
                                            <span class="material-symbols-outlined text-[22px]">family_restroom</span>
                                        </div>
                                        <div>
                                            <h2 class="font-headline-sm text-headline-sm text-on-surface">Link Children
                                                (optional)</h2>
                                            <p class="font-body-sm text-body-sm text-on-surface-variant">Associate
                                                enrolled students.</p>
                                        </div>
                                    </div>
                                    <span id="selectedStudentsCounter"
                                        class="px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-label-sm font-semibold">
                                        0 selected
                                    </span>
                                </div>

                                <div class="relative mb-3">
                                    <span
                                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">search</span>
                                    <input id="studentSearch" type="text"
                                        placeholder="Search student by name or class..."
                                        class="w-full pl-9 pr-4 py-2 bg-surface-container-low font-body-sm text-body-sm text-on-surface rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all" />
                                </div>

                                <div class="max-h-64 overflow-y-auto space-y-2 pr-1" id="studentList">
                                    @forelse ($students as $s)
                                        <label
                                            class="flex items-center justify-between p-2.5 rounded-lg bg-surface-container-low hover:bg-surface-container transition-all cursor-pointer checkbox-card"
                                            onclick="toggleCheckbox(this)">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="student_ids[]" value="{{ $s->student_id }}"
                                                    onchange="handleStudentToggle(this)"
                                                    class="w-4 h-4 rounded accent-primary-container cursor-pointer"
                                                    {{ in_array($s->student_id, old('student_ids', [])) ? 'checked' : '' }} />
                                                <div
                                                    class="w-9 h-9 rounded-full bg-primary-container text-on-primary flex items-center justify-center font-label-sm text-label-sm font-bold">
                                                    {{ strtoupper(substr($s->first_name, 0, 1) . substr($s->last_name, 0, 1)) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="font-label-md text-label-md font-semibold text-on-surface leading-tight">{{ $s->full_name }}</span>
                                                    <span class="font-label-sm text-label-sm text-on-surface-variant">
                                                        {{ $s->schoolClass?->class_name ?? 'No Class' }} &middot;
                                                        {{ $s->student_number }}
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    @empty
                                        <p class="text-on-surface-variant text-sm text-center py-4">No unlinked students
                                            available.</p>
                                    @endforelse
                                </div>
                            </section>

                            <!-- Info banner -->
                            <div class="rounded-xl p-4 bg-primary-fixed text-on-primary-fixed-variant flex items-start gap-3 shadow-sm">
                                <div
                                    class="w-8 h-8 rounded-full bg-primary-container text-on-primary flex items-center justify-center shrink-0 mt-0.5">
                                    <span class="material-symbols-outlined text-[18px]">vpn_key</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-label-md text-label-md font-bold leading-snug">One-time
                                        password</span>
                                    <p class="font-body-sm text-body-sm mt-0.5">
                                        A secure one-time password will be generated automatically and shown after the
                                        account is created. Share it with the parent — they'll be required to set their
                                        own password on first login.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleCheckbox(labelElement) {
            const checkbox = labelElement.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        }

        function handleStudentToggle(checkbox) {
            const selected = document.querySelectorAll('input[name="student_ids[]"]:checked');
            const counter = document.getElementById('selectedStudentsCounter');
            counter.textContent = `${selected.length} selected`;

            const parentLabel = checkbox.closest('label');
            if (checkbox.checked) {
                parentLabel.classList.add('bg-primary-fixed', 'selected');
                parentLabel.classList.remove('bg-surface-container-low');
            } else {
                parentLabel.classList.remove('bg-primary-fixed', 'selected');
                parentLabel.classList.add('bg-surface-container-low');
            }
        }

        const studentSearch = document.getElementById('studentSearch');
        if (studentSearch) {
            studentSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                document.querySelectorAll('#studentList > label').forEach((item) => {
                    item.style.display = item.textContent.toLowerCase().includes(query) ? 'flex' : 'none';
                });
            });
        }

        document.getElementById('parentForm').addEventListener('submit', function() {
            const buttons = document.querySelectorAll('button[type="submit"]');
            buttons.forEach((btn) => {
                btn.disabled = true;
                btn.classList.add('opacity-80', 'pointer-events-none');
            });
        });

        document.querySelectorAll('#parentForm input, #parentForm textarea').forEach((field) => {
            field.addEventListener('input', function() {
                this.classList.remove('border-error');
            });
        });
    </script>
@endpush
