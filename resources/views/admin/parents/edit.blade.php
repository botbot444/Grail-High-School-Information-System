{{-- resources/views/admin/parents/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Parent')

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
                        <a class="hover:text-primary transition-colors"
                            href="{{ route('admin.parents.show', $parent) }}">{{ $parent->full_name }}</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <span class="text-primary font-bold">Edit</span>
                    </nav>
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-1">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-primary-container/15 text-primary flex items-center justify-center font-headline-sm text-headline-sm font-bold shrink-0">
                                {{ strtoupper(substr($parent->first_name, 0, 1) . substr($parent->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight">Edit Parent /
                                    Guardian</h1>
                                <p class="font-body-md text-body-md text-on-surface-variant mt-1">{{ $parent->full_name }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0 flex-wrap">
                            <a href="{{ route('admin.parents.show', $parent) }}"
                                class="px-4 h-9 rounded-lg bg-surface-container-lowest text-on-surface font-label-sm text-label-sm shadow-sm hover:bg-surface-container-low transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                                Cancel
                            </a>
                            <button type="submit" form="parentForm"
                                class="px-4 h-9 rounded-lg bg-primary text-on-primary font-label-sm text-label-sm shadow-sm hover:bg-primary-container transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                @include('admin.partials.flash')

                <!-- ==================== FORM ==================== -->
                <form action="{{ route('admin.parents.update', $parent) }}" method="POST" id="parentForm">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

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
                                        <input id="firstName" name="first_name"
                                            value="{{ old('first_name', $parent->first_name) }}" required type="text"
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
                                        <input id="lastName" name="last_name"
                                            value="{{ old('last_name', $parent->last_name) }}" required type="text"
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
                                        <textarea id="address" name="address" rows="2"
                                            class="px-3 py-2.5 bg-surface-container-low text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all resize-none @error('address') border border-error @enderror">{{ old('address', $parent->address) }}</textarea>
                                        @error('address')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="font-label-form text-label-form text-on-surface-variant mb-1.5 uppercase"
                                            for="occupation">
                                            Occupation
                                        </label>
                                        <input id="occupation" name="occupation"
                                            value="{{ old('occupation', $parent->occupation) }}" type="text"
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
                                        <input id="nationalId" name="national_id"
                                            value="{{ old('national_id', $parent->national_id) }}" type="text"
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
                                            <input id="email" name="email" value="{{ old('email', $parent->email) }}"
                                                required type="email"
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
                                            <input id="phone" name="phone" value="{{ old('phone', $parent->phone) }}"
                                                type="tel"
                                                class="w-full h-[38px] pl-9 pr-3 bg-surface-container-low text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all font-data-mono @error('phone') border border-error @enderror" />
                                        </div>
                                        @error('phone')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <!-- Card: Account & Security -->
                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm flex flex-col gap-4">
                                <div class="flex items-center justify-between pb-4 border-b border-surface-container">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary-container">
                                            <span class="material-symbols-outlined text-[22px]">security</span>
                                        </div>
                                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Account &amp;
                                            Security</h2>
                                    </div>
                                    @if ($parent->user?->must_change_password)
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 font-label-sm text-label-sm font-semibold uppercase">Reset
                                            Required</span>
                                    @elseif ($parent->user?->is_active)
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-label-sm text-label-sm font-semibold uppercase">Active</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-surface-container text-on-surface-variant font-label-sm text-label-sm font-semibold uppercase">Inactive</span>
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2 bg-surface-container-low/60 p-3.5 rounded-xl">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="font-label-form text-label-form uppercase text-on-surface-variant">Linked
                                            Login</span>
                                        <span
                                            class="font-body-sm text-body-sm text-on-surface font-medium">{{ $parent->user?->email ?? 'No linked user' }}</span>
                                    </div>
                                    <div class="h-px bg-surface-container"></div>
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="font-label-form text-label-form uppercase text-on-surface-variant">Member
                                            Since</span>
                                        <span
                                            class="font-body-sm text-body-sm text-on-surface">{{ $parent->user?->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                @if ($parent->user)
                                    <div class="flex flex-col gap-1.5 pt-1">
                                        {{-- Deliberately NOT a nested <form> here — it used to be its
                                             own <form> sitting inside #parentForm. Nested forms are
                                             invalid HTML: the browser closed #parentForm early at this
                                             form's </form> tag, which pushed the "Linked Children"
                                             checkboxes further down the page outside #parentForm
                                             entirely — so saving a parent silently never submitted any
                                             student_ids[] changes. This button submits the standalone
                                             #parent-reset-password-form declared just after #parentForm's
                                             closing tag (a sibling, not a child). --}}
                                        <button type="button" id="parent-reset-password-btn"
                                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100/80 text-amber-900 font-label-md text-label-md transition-colors border border-amber-200">
                                            <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                                            <span>Reset Password</span>
                                        </button>
                                        <span class="font-body-sm text-body-sm text-on-surface-variant text-center">
                                            Generates a new temporary password, shown once, for you to share with the
                                            parent.
                                        </span>
                                    </div>
                                @endif
                            </section>

                            <!-- Card: Linked Children -->
                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm flex flex-col">
                                <div class="flex items-center justify-between pb-4 mb-3 border-b border-surface-container">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-primary-container">
                                            <span class="material-symbols-outlined text-[22px]">family_restroom</span>
                                        </div>
                                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Linked Children</h2>
                                    </div>
                                    <span id="selectedStudentsCounter"
                                        class="px-2.5 py-0.5 rounded-full bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-label-sm font-semibold">
                                        {{ count($linked ?? []) }} linked
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
                                    @foreach ($students as $s)
                                        @php($isLinked = in_array($s->student_id, $linked ?? []))
                                        <label
                                            class="flex items-center justify-between p-2.5 rounded-lg {{ $isLinked ? 'bg-primary-fixed selected' : 'bg-surface-container-low' }} hover:bg-surface-container transition-all cursor-pointer checkbox-card"
                                            onclick="toggleCheckbox(this)">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" name="student_ids[]" value="{{ $s->student_id }}"
                                                    onchange="handleStudentToggle(this)"
                                                    class="w-4 h-4 rounded accent-primary-container cursor-pointer"
                                                    {{ $isLinked ? 'checked' : '' }} />
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
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    </div>
                </form>

                {{-- Sibling of #parentForm (not nested inside it) — see comment above. --}}
                @if ($parent->user)
                    <form method="POST" action="{{ route('admin.users.reset-password', $parent->user_id) }}"
                        id="parent-reset-password-form" class="hidden">
                        @csrf
                        @method('PUT')
                    </form>
                @endif
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
            counter.textContent = `${selected.length} linked`;

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

        // Reset Password: submits the standalone #parent-reset-password-form
        // (a sibling of #parentForm, not nested inside it — see the HTML
        // comment above it).
        const resetPasswordBtn = document.getElementById('parent-reset-password-btn');
        const resetPasswordForm = document.getElementById('parent-reset-password-form');
        if (resetPasswordBtn && resetPasswordForm) {
            resetPasswordBtn.addEventListener('click', () => {
                if (confirm('Issue a new temporary password for {{ $parent->full_name }}? Their current password will stop working immediately.')) {
                    resetPasswordForm.submit();
                }
            });
        }
    </script>
@endpush
