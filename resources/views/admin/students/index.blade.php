@extends('layouts.app')

@section('title', 'Manage Students')

@section('content')

    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        <!-- Header & Breadcrumbs -->
        <div class="mb-8 flex justify-between items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Dashboard</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Students</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    Student Management
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.students.create') }}"
                    class="flex items-center gap-2 px-4 py-2.5 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                    <span class="material-symbols-outlined text-[20px]">file_download</span>
                    Export CSV
                </a>
                <a href="{{ route('admin.students.create') }}"
                    class="flex items-center gap-2 px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-sm text-label-sm font-semibold shadow-md active:opacity-80 transition-all">
                    <span class="material-symbols-outlined text-[20px]">person_add</span>
                    + Add Student
                </a>
            </div>
        </div>
        <!-- Filters Section -->
        <div
            class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4 mb-6 shadow-sm flex flex-wrap items-center gap-gutter">
            <div class="flex flex-col gap-1.5 min-w-[180px]">
                <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Grade / Level</label>
                <select
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option>All Grades</option>
                    <option>Grade 10</option>
                    <option>Grade 11</option>
                    <option>Grade 12</option>
                </select>
            </div>
            <div class="flex flex-col gap-1.5 min-w-[150px]">
                <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Status</label>
                <select
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option>All Status</option>
                    <option>Active</option>
                    <option>Suspended</option>
                    <option>Pending</option>
                </select>
            </div>
            <div class="flex flex-col gap-1.5 min-w-[120px]">
                <label class="text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">Gender</label>
                <select
                    class="bg-surface border-outline-variant rounded-lg text-body-md py-1.5 focus:ring-primary focus:border-primary">
                    <option>All</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
            </div>
            <div class="h-10 w-px bg-surface-container-high"></div>
            <div class="flex items-center gap-2">
                <button
                    class="bg-secondary-container text-on-secondary-container px-4 py-2 rounded-lg font-label-sm text-label-sm font-semibold hover:opacity-90 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">filter_list</span>
                    Advanced Filters
                </button>
                <button class="text-on-surface-variant font-label-sm text-label-sm hover:underline">
                    Clear all
                </button>
            </div>
            <div class="ml-auto flex items-center gap-3">
                <span class="text-body-md text-on-surface-variant">Bulk Actions:</span>
                <button
                    class="px-3 py-1.5 border border-outline-variant rounded text-on-surface-variant font-label-sm opacity-50 cursor-not-allowed"
                    disabled>
                    Transfer
                </button>
                <button
                    class="px-3 py-1.5 border border-outline-variant rounded text-on-surface-variant font-label-sm opacity-50 cursor-not-allowed"
                    disabled>
                    Archive
                </button>
                <button id="bulkDeleteBtn"
                    class="px-3 py-1.5 border border-outline-variant rounded text-on-surface-variant font-label-sm opacity-50 cursor-not-allowed flex items-center gap-1"
                    disabled>
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                    Delete
                </button>
            </div>
        </div>
        <!-- Data Table Container -->
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-surface-container-low border-b border-outline-variant text-[12px] font-bold uppercase tracking-wider text-on-surface-variant">
                            <th class="px-6 py-4 w-12">
                                <input class="rounded border-outline-variant text-primary focus:ring-primary"
                                    type="checkbox" />
                            </th>
                            <th class="px-4 py-4">Student ID</th>
                            <th class="px-4 py-4">Student Name</th>
                            <th class="px-4 py-4">Class</th>
                            <th class="px-4 py-4">Parent / Guardian</th>
                            <th class="px-4 py-4">Attendance</th>
                            <th class="px-4 py-4 text-center">Status</th>
                            <th class="px-4 py-4 text-right pr-8">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high">
                        @forelse ($students as $student)
                            <tr class="table-row-hover transition-colors">
                                <td class="px-6 py-3">
                                    <input
                                        class="rounded border-outline-variant text-primary focus:ring-primary row-checkbox"
                                        type="checkbox" />
                                </td>
                                <td class="px-4 py-3 font-data-mono text-data-mono text-primary font-bold">
                                    {{ $student->student_number }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center text-on-primary-fixed font-bold border border-primary/10">
                                            {{ strtoupper(substr($student->full_name, 0, 2)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-on-surface">{{ $student->full_name }}</span>
                                            <span
                                                class="text-[11px] text-on-surface-variant">{{ $student->email ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-body-md text-on-surface-variant">
                                    {{ $student->schoolClass?->class_name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-body-md text-on-surface">
                                    {{ $student->parent?->full_name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1 w-32">
                                        <div class="flex justify-between text-[11px] font-bold">
                                            <span>{{ $student->attendance_percentage ?? '0.0%' }}</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-surface-container-high rounded-full overflow-hidden">
                                            <div class="bg-primary h-full"
                                                style="width: {{ $student->attendance_percentage ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase bg-primary-fixed text-on-primary-fixed-variant">Active</span>
                                </td>
                                <td class="px-4 py-3 text-right pr-6">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.students.show', $student->student_id) }}"
                                            class="p-1.5 hover:bg-surface-container-highest rounded text-primary transition-all"
                                            title="View Profile">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </a>
                                        <a href="{{ route('admin.students.edit', $student->student_id) }}"
                                            class="p-1.5 hover:bg-surface-container-highest rounded text-on-surface-variant transition-all"
                                            title="Edit">
                                            <span class="material-symbols-outlined text-[20px]">edit_note</span>
                                        </a>
                                        <button
                                            class="p-1.5 hover:bg-error-container/20 hover:text-error rounded text-on-surface-variant transition-all"
                                            title="Suspend">
                                            <span class="material-symbols-outlined text-[20px]">person_off</span>
                                        </button>
                                        <form method="POST"
                                            action="{{ route('admin.students.destroy', $student->student_id) }}"
                                            style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 hover:bg-error-container/20 hover:text-error rounded text-on-surface-variant transition-all delete-btn"
                                                title="Delete student">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-3 text-center text-gray-500">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex justify-end">
                {{ $students->links() }}
            </div>
        </div>
        <!-- Quick Summary Cards (Bento Style) -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <div class="p-6 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <span class="material-symbols-outlined p-2 bg-primary-fixed text-primary rounded-lg">groups</span>
                    <span class="text-[12px] font-bold text-primary">+2.4% this month</span>
                </div>
                <p class="text-on-surface-variant text-label-sm font-label-sm uppercase tracking-wider">
                    Total Enrollment
                </p>
                <p class="text-display-lg font-display-lg text-on-surface mt-1">
                    {{ $students->total() }}
                </p>
            </div>
            <div class="p-6 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <span
                        class="material-symbols-outlined p-2 bg-secondary-fixed text-on-secondary-container rounded-lg">how_to_reg</span>
                    <span class="text-[12px] font-bold text-error">-0.8% below target</span>
                </div>
                <p class="text-on-surface-variant text-label-sm font-label-sm uppercase tracking-wider">
                    Avg. Attendance
                </p>
                <p class="text-display-lg font-display-lg text-on-surface mt-1">
                    92.1%
                </p>
            </div>
            <div class="p-6 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <span
                        class="material-symbols-outlined p-2 bg-tertiary-fixed text-on-tertiary-fixed-variant rounded-lg">person_off</span>
                    <span class="text-[12px] font-bold text-on-surface-variant">3 pending review</span>
                </div>
                <p class="text-on-surface-variant text-label-sm font-label-sm uppercase tracking-wider">
                    Suspensions
                </p>
                <p class="text-display-lg font-display-lg text-on-surface mt-1">14</p>
            </div>
        </div>
    </main>

    <script>
        // Sidebar toggle functionality (already handled in header.blade.php, but keeping for completeness)
        (function() {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("mainContent");
            const header = document.getElementById("header");
            const toggleBtn = document.getElementById("sidebarToggle");

            let sidebarVisible = true;

            function updateLayout() {
                if (sidebarVisible) {
                    sidebar.classList.remove("sidebar-collapsed");
                    mainContent.classList.remove("main-expanded");
                    header.classList.remove("header-expanded");
                    toggleBtn.querySelector(".material-symbols-outlined").textContent =
                        "menu";
                } else {
                    sidebar.classList.add("sidebar-collapsed");
                    mainContent.classList.add("main-expanded");
                    header.classList.add("header-expanded");
                    toggleBtn.querySelector(".material-symbols-outlined").textContent =
                        "menu_open";
                }
            }

            toggleBtn.addEventListener("click", function(e) {
                e.stopPropagation();
                sidebarVisible = !sidebarVisible;
                updateLayout();
            });

            updateLayout();
        })();

        // Micro-interactions for table checkboxes + bulk delete
        const mainCheckbox = document.querySelector(
            'thead input[type="checkbox"]',
        );
        const rowCheckboxes = document.querySelectorAll(".row-checkbox");
        const bulkButtons = document.querySelectorAll(
            ".ml-auto .cursor-not-allowed, .ml-auto button[disabled]",
        );
        const bulkDeleteBtn = document.getElementById("bulkDeleteBtn");

        function toggleBulkActions() {
            const anyChecked = Array.from(rowCheckboxes).some((cb) => cb.checked);
            // Transfer, Archive, Delete
            const allBulkBtns = document.querySelectorAll(
                ".ml-auto button:not(.opacity-50)",
            );
            const disabledBtns = document.querySelectorAll(
                ".ml-auto .opacity-50",
            );
            if (anyChecked) {
                disabledBtns.forEach((btn) => {
                    btn.removeAttribute("disabled");
                    btn.classList.remove("opacity-50", "cursor-not-allowed");
                    btn.classList.add(
                        "bg-white",
                        "hover:bg-surface-container-high",
                        "cursor-pointer",
                    );
                });
            } else {
                disabledBtns.forEach((btn) => {
                    btn.setAttribute("disabled", "true");
                    btn.classList.add("opacity-50", "cursor-not-allowed");
                    btn.classList.remove(
                        "bg-white",
                        "hover:bg-surface-container-high",
                        "cursor-pointer",
                    );
                });
            }
        }

        mainCheckbox.addEventListener("change", (e) => {
            rowCheckboxes.forEach((cb) => (cb.checked = e.target.checked));
            toggleBulkActions();
        });

        rowCheckboxes.forEach((cb) => {
            cb.addEventListener("change", () => {
                toggleBulkActions();
            });
        });

        // Individual delete buttons: show alert
        document.querySelectorAll(".delete-btn").forEach((btn) => {
            btn.addEventListener("click", function(e) {
                e.stopPropagation();
                const row = this.closest("tr");
                const name = row
                    .querySelector(".font-semibold")
                    ?.textContent.trim();
                if (confirm(`Delete student "${name || 'this student'}"?`)) {
                    // In a real app, you'd send a DELETE request
                    row.style.transition = "opacity 0.2s";
                    row.style.opacity = "0.3";
                    setTimeout(() => {
                        row.remove();
                    }, 200);
                }
            });
        });

        // Bulk delete: show alert and remove selected rows
        bulkDeleteBtn?.addEventListener("click", function() {
            const checked = document.querySelectorAll(".row-checkbox:checked");
            if (checked.length === 0) return;
            if (
                confirm(
                    `Delete ${checked.length} selected student(s)? This action cannot be undone.`,
                )
            ) {
                checked.forEach((cb) => {
                    const row = cb.closest("tr");
                    row.style.transition = "opacity 0.2s";
                    row.style.opacity = "0.3";
                    setTimeout(() => {
                        row.remove();
                    }, 200);
                });
                // Uncheck main and reset bulk actions
                mainCheckbox.checked = false;
                toggleBulkActions();
            }
        });

        // Toggle bulk actions initially
        toggleBulkActions();
    </script>

@endsection
