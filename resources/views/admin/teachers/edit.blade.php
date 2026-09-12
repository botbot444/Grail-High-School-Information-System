@extends('layouts.app')

@section('title', 'Edit Teacher')

@section('content')
    <div id="view-admin" class="app-view" style="display:flex;">
        @include('admin.sidebar')
        @include('admin.header')

        <main id="mainContent"
            class="ml-[260px] pt-[72px] min-h-screen bg-background p-container-padding main-transition flex-1">
            <div class="pb-12 max-w-[1400px] mx-auto">
                <div class="flex flex-col gap-3 mb-8">
                    <nav
                        class="flex items-center gap-2 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                        <a class="hover:text-primary transition-colors" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <a class="hover:text-primary transition-colors"
                            href="{{ route('admin.teachers.index') }}">Teachers</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <span class="text-primary font-bold">Edit Teacher</span>
                    </nav>
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-1">
                        <div>
                            <h1 class="font-display-lg text-display-lg text-on-surface tracking-tight">Edit Teacher</h1>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-2xl">
                                Update the teacher profile, contact details, and current teaching assignments.
                            </p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <a href="{{ route('admin.teachers.show', $teacher) }}"
                                class="px-4 h-9 rounded-lg bg-surface-container-lowest text-on-surface font-label-sm text-label-sm shadow-sm hover:bg-surface-container-low transition-colors flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                                Cancel
                            </a>
                            <button type="submit" form="teacherEditForm"
                                class="px-4 h-9 rounded-lg bg-primary text-on-primary font-label-sm text-label-sm shadow-sm hover:bg-primary-container transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                Save Changes
                            </button>
                        </div>
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

                <form action="{{ route('admin.teachers.update', $teacher) }}" method="POST" id="teacherEditForm">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <div class="lg:col-span-7 flex flex-col gap-6">
                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                <div class="flex items-center justify-between pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-secondary"></span>
                                        <h2 class="font-title-sm text-title-sm text-on-surface">Personal Information</h2>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-on-surface-variant text-[20px]">person</span>
                                </div>

                                <div class="rounded-xl bg-surface-container-low p-4 mb-6 flex items-center gap-4">
                                    <div
                                        class="w-14 h-14 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-[26px]">badge</span>
                                    </div>
                                    <div>
                                        <h3 class="font-title-sm text-title-sm text-on-surface">{{ $teacher->full_name }}
                                        </h3>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant">Teacher account and
                                            profile record</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex flex-col">
                                        <label class="font-label-sm text-label-sm text-on-surface-variant mb-1.5 uppercase"
                                            for="firstName">First Name <span class="text-error">*</span></label>
                                        <input id="firstName" name="first_name"
                                            value="{{ old('first_name', $teacher->first_name) }}" required type="text"
                                            class="h-[38px] px-3 bg-surface-container-lowest text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all shadow-xs @error('first_name') border border-error @enderror">
                                        @error('first_name')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="font-label-sm text-label-sm text-on-surface-variant mb-1.5 uppercase"
                                            for="lastName">Last Name <span class="text-error">*</span></label>
                                        <input id="lastName" name="last_name"
                                            value="{{ old('last_name', $teacher->last_name) }}" required type="text"
                                            class="h-[38px] px-3 bg-surface-container-lowest text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all shadow-xs @error('last_name') border border-error @enderror">
                                        @error('last_name')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                <div class="flex items-center justify-between pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-secondary"></span>
                                        <h2 class="font-title-sm text-title-sm text-on-surface">Assigned Subjects</h2>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-on-surface-variant text-[20px]">menu_book</span>
                                </div>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mb-4">Select the subjects this
                                    teacher delivers.</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @forelse ($subjects as $subject)
                                        <label
                                            class="flex items-center gap-2.5 p-2.5 rounded-lg bg-surface-container-low cursor-pointer hover:bg-surface-container transition-colors">
                                            <input class="w-4 h-4 rounded accent-secondary cursor-pointer"
                                                name="subject_ids[]" type="checkbox" value="{{ $subject->subject_id }}"
                                                {{ in_array($subject->subject_id, old('subject_ids', $assignedSubjects ?? [])) ? 'checked' : '' }}>
                                            <span
                                                class="font-label-sm text-label-sm text-on-surface">{{ $subject->subject_name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-on-surface-variant text-sm">No subjects available.</p>
                                    @endforelse
                                </div>
                            </section>
                        </div>

                        <div class="lg:col-span-5 flex flex-col gap-6">
                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                <div class="flex items-center justify-between pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-secondary"></span>
                                        <h2 class="font-title-sm text-title-sm text-on-surface">Contact Details</h2>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-on-surface-variant text-[20px]">contact_phone</span>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex flex-col">
                                        <label class="font-label-sm text-label-sm text-on-surface-variant mb-1.5 uppercase"
                                            for="email">Institutional Email <span class="text-error">*</span></label>
                                        <input id="email" name="email" value="{{ old('email', $teacher->email) }}"
                                            required type="email"
                                            class="w-full h-[38px] px-3 bg-surface-container-lowest text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all shadow-xs @error('email') border border-error @enderror">
                                        @error('email')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="font-label-sm text-label-sm text-on-surface-variant mb-1.5 uppercase"
                                            for="phone">Primary Phone Number</label>
                                        <input id="phone" name="phone" value="{{ old('phone', $teacher->phone) }}"
                                            type="tel"
                                            class="w-full h-[38px] px-3 bg-surface-container-lowest text-on-surface font-body-md text-body-md rounded-lg focus:outline-none focus:ring-1 focus:ring-primary transition-all shadow-xs @error('phone') border border-error @enderror">
                                        @error('phone')
                                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="bg-surface-container-lowest rounded-xl p-6 shadow-sm">
                                <div class="flex items-center justify-between pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-secondary"></span>
                                        <h2 class="font-title-sm text-title-sm text-on-surface">Assigned Homeroom Classes
                                        </h2>
                                    </div>
                                    <span
                                        class="material-symbols-outlined text-on-surface-variant text-[20px]">meeting_room</span>
                                </div>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mb-4">Select the classes
                                    assigned to this teacher.</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @forelse ($classes as $class)
                                        <label
                                            class="flex items-center gap-2.5 p-2.5 rounded-lg bg-surface-container-low cursor-pointer hover:bg-surface-container transition-colors">
                                            <input class="w-4 h-4 rounded accent-secondary cursor-pointer"
                                                name="class_ids[]" type="checkbox" value="{{ $class->class_id }}"
                                                {{ in_array($class->class_id, old('class_ids', $assignedClasses ?? [])) ? 'checked' : '' }}>
                                            <span
                                                class="font-label-sm text-label-sm text-on-surface">{{ $class->class_name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-on-surface-variant text-sm">No classes available.</p>
                                    @endforelse
                                </div>
                            </section>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
@endsection
