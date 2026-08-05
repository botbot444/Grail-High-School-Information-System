@extends('layouts.app')

@section('title', 'Student Profile - ' . $student->full_name)

@section('content')

    <div id="view-admin" class="app-view" style="display:flex;">

        @include('admin.sidebar')

        @include('admin.header')

        <!-- Main Content Area -->
        <main id="mainContent"
            class="ml-[260px] pt-[72px] min-h-screen bg-background p-container-padding main-transition flex-1">
            <!-- Breadcrumbs & Actions -->
            <div class="flex justify-between items-center mb-8">
                <nav class="flex items-center gap-2 text-on-surface-variant text-label-sm">
                    <a class="hover:text-primary transition-colors" href="{{ route('admin.students.index') }}">Students</a>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-on-surface font-semibold">{{ $student->full_name }}</span>
                </nav>
                <div class="flex gap-3">
                    <button onclick="window.print()"
                        class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                        <span class="material-symbols-outlined">print</span>
                        Print Profile
                    </button>
                    <a href="{{ route('admin.students.edit', $student->student_id) }}"
                        class="flex items-center gap-2 px-4 py-2 border border-outline text-on-surface rounded-lg font-label-sm text-label-sm hover:bg-surface-container-high transition-all">
                        <span class="material-symbols-outlined">edit</span>
                        Edit Records
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-gutter">
                <!-- Left Side: Main Profile Info -->
                <div class="col-span-12 lg:col-span-12 space-y-gutter">
                    <!-- Profile Header Card -->
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden card-shadow"
                        style="--mouse-x: 81px; --mouse-y: 269.6666564941406px" id="profileCard">
                        <div class="h-32 bg-primary relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent"></div>
                        </div>
                        <div class="px-8 pb-8 flex flex-col md:flex-row items-end -mt-12 relative z-10 gap-6">
                            <div class="relative group">
                                <div
                                    class="w-32 h-32 md:w-40 md:h-40 rounded-2xl border-4 border-surface-container-lowest shadow-xl object-cover bg-primary-container text-on-primary-container font-extrabold text-5xl flex items-center justify-center">
                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                </div>
                                <div class="absolute bottom-2 right-2 bg-green-500 w-5 h-5 rounded-full border-4 border-surface-container-lowest"
                                    title="Currently Enrolled"></div>
                            </div>
                            <div class="flex-1 pb-2">
                                <h2 class="font-display-lg text-display-lg text-on-surface mb-1">
                                    {{ $student->full_name }}
                                </h2>
                                <div class="flex flex-wrap gap-4 items-center text-on-surface-variant font-label-sm">
                                    <span
                                        class="flex items-center gap-1.5 bg-surface-container px-3 py-1 rounded-full border border-outline-variant/30">
                                        <span class="material-symbols-outlined text-[18px]">badge</span>
                                        #{{ $student->student_number }}
                                    </span>
                                    <span
                                        class="flex items-center gap-1.5 bg-surface-container px-3 py-1 rounded-full border border-outline-variant/30">
                                        <span class="material-symbols-outlined text-[18px]">school</span>
                                        {{ $student->schoolClass?->class_name ?? 'N/A' }}
                                    </span>
                                    <span
                                        class="flex items-center gap-1.5 bg-primary-fixed text-on-primary-fixed px-3 py-1 rounded-full font-bold">
                                        Honors Program
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabbed Navigation Content Area -->
                    <div
                        class="bg-surface-container-lowest border border-outline-variant rounded-xl card-shadow min-h-[500px] flex flex-col">
                        <!-- Custom Tabs -->
                        <div
                            class="flex px-4 border-b border-outline-variant bg-surface-container-low/30 overflow-x-auto whitespace-nowrap">
                            <button
                                class="tab-btn active px-6 py-4 font-label-sm text-label-sm text-primary border-b-2 border-primary transition-all"
                                onclick="switchTab('personal')">
                                Personal Info
                            </button>
                            <button
                                class="tab-btn px-6 py-4 font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-all"
                                onclick="switchTab('academic')">
                                Academic Records
                            </button>
                            <button
                                class="tab-btn px-6 py-4 font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-all"
                                onclick="switchTab('attendance')">
                                Attendance
                            </button>
                            <button
                                class="tab-btn px-6 py-4 font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-all"
                                onclick="switchTab('parent')">
                                Parent Details
                            </button>
                            <button
                                class="tab-btn px-6 py-4 font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-all"
                                onclick="switchTab('financial')">
                                Financials
                            </button>
                        </div>
                        <!-- Tab Panels -->
                        <div class="p-8 flex-1">
                            <!-- Personal Info Panel -->
                            <div class="tab-panel animate-in fade-in duration-500" id="panel-personal">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div class="space-y-6">
                                        <h3 class="font-title-sm text-title-sm border-l-4 border-primary pl-3">
                                            General Information
                                        </h3>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p
                                                    class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">
                                                    Full Name
                                                </p>
                                                <p class="font-body-md text-body-md font-semibold">
                                                    {{ $student->full_name }}
                                                </p>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">
                                                    Date of Birth
                                                </p>
                                                <p class="font-body-md text-body-md font-semibold">
                                                    {{ $student->date_of_birth?->format('F d, Y') ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">
                                                    Gender
                                                </p>
                                                <p class="font-body-md text-body-md font-semibold">
                                                    {{ $student->gender }}
                                                </p>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest mb-1">
                                                    Enrollment Date
                                                </p>
                                                <p class="font-body-md text-body-md font-semibold">
                                                    {{ $student->enrolment_date?->format('F d, Y') ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-6">
                                        <h3 class="font-title-sm text-title-sm border-l-4 border-primary pl-3">
                                            Contact Details
                                        </h3>
                                        <div class="space-y-4">
                                            <div class="flex items-start gap-3">
                                                <span class="material-symbols-outlined text-primary">location_on</span>
                                                <div>
                                                    <p
                                                        class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">
                                                        Permanent Address
                                                    </p>
                                                    <p class="font-body-md text-body-md font-semibold">
                                                        {{ $student->parentUser?->parentProfile?->address ?? '452 Heritage Boulevard, Apartment 4B Maple Ridge, CA 94521' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3">
                                                <span class="material-symbols-outlined text-primary">mail</span>
                                                <div>
                                                    <p
                                                        class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">
                                                        Personal Email
                                                    </p>
                                                    <p class="font-body-md text-body-md font-semibold">
                                                        {{ $student->user?->email ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3">
                                                <span class="material-symbols-outlined text-primary">call</span>
                                                <div>
                                                    <p
                                                        class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">
                                                        Phone Number
                                                    </p>
                                                    <p class="font-body-md text-body-md font-semibold">
                                                        {{ $student->guardian_phone ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Academic Info Panel -->
                            <div class="tab-panel hidden animate-in fade-in duration-500" id="panel-academic">
                                <div class="flex flex-col gap-8">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div
                                            class="bg-primary-container/10 border border-primary-container p-4 rounded-xl flex items-center gap-4">
                                            <div class="p-3 bg-primary-container text-on-primary-container rounded-lg">
                                                <span class="material-symbols-outlined">grade</span>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">
                                                    GPA
                                                </p>
                                                <p class="text-headline-md font-headline-md text-primary font-bold">
                                                    3.85 / 4.0
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="bg-secondary-container/10 border border-secondary-container p-4 rounded-xl flex items-center gap-4">
                                            <div class="p-3 bg-secondary-container text-on-secondary-container rounded-lg">
                                                <span class="material-symbols-outlined">trending_up</span>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">
                                                    Academic Records
                                                </p>
                                                <p
                                                    class="text-headline-md font-headline-md text-on-secondary-container font-bold">
                                                    {{ $student->grades->count() }} Grades
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="bg-surface-container border border-outline-variant p-4 rounded-xl flex items-center gap-4">
                                            <div class="p-3 bg-on-surface-variant text-surface rounded-lg">
                                                <span class="material-symbols-outlined">verified</span>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">
                                                    Credits Earned
                                                </p>
                                                <p class="text-headline-md font-headline-md text-on-surface font-bold">
                                                    112 / 120
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-title-sm text-title-sm mb-4">
                                            Current Subjects & Grades
                                        </h4>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left border-collapse">
                                                <thead>
                                                    <tr
                                                        class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm uppercase">
                                                        <th class="px-4 py-3 border-b border-outline-variant">
                                                            Subject Name
                                                        </th>
                                                        <th class="px-4 py-3 border-b border-outline-variant">
                                                            Instructor
                                                        </th>
                                                        <th class="px-4 py-3 border-b border-outline-variant">
                                                            Grade
                                                        </th>
                                                        <th class="px-4 py-3 border-b border-outline-variant text-right">
                                                            Score
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-body-md font-body-md">
                                                    @forelse ($student->grades as $grade)
                                                        <tr
                                                            class="hover:bg-surface-container transition-colors border-b border-outline-variant/30">
                                                            <td class="px-4 py-3 font-semibold">
                                                                {{ $grade->classSubject?->subject?->subject_name ?? 'N/A' }}
                                                            </td>
                                                            <td class="px-4 py-3">
                                                                {{ $grade->recordedByTeacher?->full_name ?? 'N/A' }}</td>
                                                            <td class="px-4 py-3">
                                                                <span
                                                                    class="px-2 py-0.5 bg-green-100 text-green-800 rounded font-semibold">{{ $grade->letter_grade }}</span>
                                                            </td>
                                                            <td class="px-4 py-3 text-right font-mono">{{ $grade->score }}
                                                                / {{ $grade->max_score }} ({{ $grade->percentage }}%)</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4"
                                                                class="px-4 py-3 text-center text-on-surface-variant">No
                                                                grade records found.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Attendance Panel -->
                            <div class="tab-panel hidden animate-in fade-in duration-500" id="panel-attendance">
                                @if ($student->attendance->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left border-collapse">
                                            <thead>
                                                <tr
                                                    class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm uppercase">
                                                    <th class="px-4 py-3 border-b border-outline-variant">Date</th>
                                                    <th class="px-4 py-3 border-b border-outline-variant">Subject</th>
                                                    <th class="px-4 py-3 border-b border-outline-variant">Status</th>
                                                    <th class="px-4 py-3 border-b border-outline-variant">Recorded By</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-body-md font-body-md">
                                                @foreach ($student->attendance as $att)
                                                    <tr
                                                        class="hover:bg-surface-container transition-colors border-b border-outline-variant/30">
                                                        <td class="px-4 py-3 font-semibold">
                                                            {{ $att->date?->format('M d, Y') ?? 'N/A' }}</td>
                                                        <td class="px-4 py-3">
                                                            {{ $att->classSubject?->subject?->subject_name ?? 'N/A' }}</td>
                                                        <td class="px-4 py-3">
                                                            @if ($att->status == 'Present')
                                                                <span
                                                                    class="px-2 py-0.5 bg-green-100 text-green-800 rounded font-semibold">{{ $att->status }}</span>
                                                            @elseif ($att->status == 'Late')
                                                                <span
                                                                    class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded font-semibold">{{ $att->status }}</span>
                                                            @else
                                                                <span
                                                                    class="px-2 py-0.5 bg-red-100 text-red-800 rounded font-semibold">{{ $att->status }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            {{ $att->recordedByTeacher?->full_name ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-20">
                                        <span
                                            class="material-symbols-outlined text-6xl text-surface-variant mb-4">event_available</span>
                                        <p class="text-on-surface-variant font-label-sm">No attendance records found.</p>
                                    </div>
                                @endif
                            </div>
                            <!-- Parent Details Panel -->
                            <div class="tab-panel hidden animate-in fade-in duration-500" id="panel-parent">
                                @if ($student->guardian_name)
                                    <div
                                        class="flex items-center gap-6 p-6 border border-outline-variant rounded-xl bg-surface-container-low">
                                        <div
                                            class="w-24 h-24 rounded-full border-2 border-white shadow-sm bg-primary/10 flex items-center justify-center text-primary font-bold text-3xl">
                                            {{ substr($student->guardian_name, 0, 2) }}
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-title-sm text-title-sm text-on-surface font-bold">
                                                {{ $student->guardian_name }}
                                            </h4>
                                            <p class="text-on-surface-variant text-body-md mb-3">
                                                Primary Contact & Guardian
                                            </p>
                                            <div class="flex flex-wrap gap-4">
                                                @if ($student->guardian_phone)
                                                    <span
                                                        class="text-on-surface-variant text-label-sm flex items-center gap-1.5 bg-surface-container px-3 py-1 rounded-full border border-outline-variant/30 font-semibold font-mono">
                                                        <span class="material-symbols-outlined text-sm">call</span>
                                                        {{ $student->guardian_phone }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-20">
                                        <span
                                            class="material-symbols-outlined text-6xl text-surface-variant mb-4">family_restroom</span>
                                        <p class="text-on-surface-variant font-label-sm">No guardian details recorded.</p>
                                    </div>
                                @endif
                            </div>
                            <!-- Financials Panel -->
                            <div class="tab-panel hidden animate-in fade-in duration-500" id="panel-financial">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div
                                        class="bg-surface-container border border-outline-variant p-4 rounded-xl flex items-center gap-4">
                                        <div class="p-3 bg-red-100 text-red-800 rounded-lg">
                                            <span class="material-symbols-outlined">payments</span>
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">
                                                Total Fees Due</p>
                                            <p class="text-headline-md font-headline-md text-red-600 font-bold">
                                                K{{ number_format($student->fees()->sum('amount_due')) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-surface-container border border-outline-variant p-4 rounded-xl flex items-center gap-4">
                                        <div class="p-3 bg-green-100 text-green-800 rounded-lg">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </div>
                                        <div>
                                            <p
                                                class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">
                                                Total Paid</p>
                                            <p class="text-headline-md font-headline-md text-green-600 font-bold">
                                                K{{ number_format($student->fees()->where('status', 'Cleared')->sum('amount_paid')) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="font-title-sm text-title-sm mb-4">Fee Structure Details</h4>
                                @if ($student->fees->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left border-collapse">
                                            <thead>
                                                <tr
                                                    class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm uppercase">
                                                    <th class="px-4 py-3 border-b border-outline-variant">Fee Type</th>
                                                    <th class="px-4 py-3 border-b border-outline-variant">Amount Due</th>
                                                    <th class="px-4 py-3 border-b border-outline-variant">Amount Paid</th>
                                                    <th class="px-4 py-3 border-b border-outline-variant text-right">Status
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-body-md font-body-md">
                                                @foreach ($student->fees as $fee)
                                                    <tr
                                                        class="hover:bg-surface-container transition-colors border-b border-outline-variant/30">
                                                        <td class="px-4 py-3 font-semibold">{{ $fee->fee_type }}</td>
                                                        <td class="px-4 py-3">K{{ number_format($fee->amount_due) }}</td>
                                                        <td class="px-4 py-3">K{{ number_format($fee->amount_paid) }}</td>
                                                        <td class="px-4 py-3 text-right">
                                                            @if ($fee->status === 'Cleared')
                                                                <span
                                                                    class="px-2 py-0.5 bg-green-100 text-green-800 rounded font-semibold">Cleared</span>
                                                            @else
                                                                <span
                                                                    class="px-2 py-0.5 bg-red-100 text-red-800 rounded font-semibold">Unpaid</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-20">
                                        <span
                                            class="material-symbols-outlined text-6xl text-surface-variant mb-4">payments</span>
                                        <p class="text-on-surface-variant font-label-sm">No fee records found.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll(".tab-panel").forEach((panel) => {
                panel.classList.add("hidden");
            });
            document.getElementById("panel-" + tabId).classList.remove("hidden");

            document.querySelectorAll(".tab-btn").forEach((btn) => {
                btn.classList.remove(
                    "text-primary",
                    "border-b-2",
                    "border-primary",
                    "active",
                );
                btn.classList.add("text-on-surface-variant");
            });

            const activeBtn = Array.from(
                document.querySelectorAll(".tab-btn"),
            ).find((btn) => btn.getAttribute("onclick").includes(tabId));
            if (activeBtn) {
                activeBtn.classList.add(
                    "text-primary",
                    "border-b-2",
                    "border-primary",
                    "active",
                );
                activeBtn.classList.remove("text-on-surface-variant");
            }
        }

        const mainCard = document.getElementById("profileCard");
        if (mainCard) {
            mainCard.addEventListener("mousemove", (e) => {
                const rect = mainCard.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                mainCard.style.setProperty("--mouse-x", `${x}px`);
                mainCard.style.setProperty("--mouse-y", `${y}px`);
            });
        }
    </script>

@endsection
