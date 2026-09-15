@extends('layouts.app')

@section('title', 'Review Registration Request')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Registration Requests</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Request #{{ $registrationRequest->registration_request_id }}</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    {{ $registrationRequest->child_full_name }}
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    New admission request from {{ $registrationRequest->parent_full_name }}
                </p>
            </div>
            <a href="{{ route('admin.registration-requests.index') }}"
                class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface border border-outline-variant rounded-lg text-label-sm">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Back
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="text-headline-sm font-bold text-on-surface mb-4">Parent Details</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Name</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->parent_full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Email</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->parent_email }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Phone</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->parent_phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">National ID</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->parent_national_id ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Occupation</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->parent_occupation ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Address</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->parent_address ?? '—' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="text-headline-sm font-bold text-on-surface mb-4">Child Details</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Name</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->child_full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Date of Birth</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->child_date_of_birth->format('d M Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Gender</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->child_gender }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-on-surface-variant">Proposed Login Email</dt>
                            <dd class="text-body-md text-on-surface">{{ $registrationRequest->child_email }}</dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-xs text-on-surface-variant flex items-start gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">info</span>
                        This is a new admission — the school has no prior record of this child. Approving creates
                        real login accounts for both parent and child; the child isn't placed in a class yet.
                    </p>
                </section>

                @if (! $registrationRequest->isPending())
                    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                        <h2 class="text-headline-sm font-bold text-on-surface mb-3">Review Outcome</h2>
                        <p class="text-body-sm text-on-surface">
                            <strong>{{ $registrationRequest->status_label }}</strong> by {{ $registrationRequest->reviewedBy->name ?? 'N/A' }}
                            on {{ $registrationRequest->reviewed_at?->format('d M Y H:i') }}
                        </p>
                        @if ($registrationRequest->review_notes)
                            <p class="mt-2 text-body-sm text-on-surface-variant">{{ $registrationRequest->review_notes }}</p>
                        @endif
                        @if ($registrationRequest->isApproved() && $registrationRequest->createdStudent)
                            <a href="{{ route('admin.students.show', $registrationRequest->createdStudent->student_id) }}"
                                class="mt-3 inline-block text-primary hover:underline text-label-sm font-semibold">View Student Record</a>
                        @endif
                    </section>
                @endif
            </div>

            <div class="space-y-6">
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                    <h2 class="text-headline-sm font-bold text-on-surface mb-3">Status</h2>
                    @php
                        $badge = match ($registrationRequest->status) {
                            'approved' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            default    => 'bg-amber-100 text-amber-800',
                        };
                    @endphp
                    <span class="inline-block px-3 py-1 rounded-full text-label-sm font-semibold {{ $badge }}">
                        {{ $registrationRequest->status_label }}
                    </span>
                    <p class="mt-4 text-body-sm text-on-surface-variant">
                        We only hold what was submitted — confirming it's genuine (matching enrollment paperwork, a
                        call, whatever your process is) is on you before approving.
                    </p>
                </section>

                @if ($registrationRequest->isPending())
                    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                        <h2 class="text-headline-sm font-bold text-on-surface mb-3">Approve</h2>
                        <form method="POST" action="{{ route('admin.registration-requests.approve', $registrationRequest->registration_request_id) }}"
                            onsubmit="return confirm('This creates real login accounts for {{ $registrationRequest->parent_full_name }} and {{ $registrationRequest->child_full_name }}. Continue?');">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">
                                Approve &amp; Create Accounts
                            </button>
                        </form>
                    </section>

                    <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-5">
                        <h2 class="text-headline-sm font-bold text-on-surface mb-3">Reject</h2>
                        <form method="POST" action="{{ route('admin.registration-requests.reject', $registrationRequest->registration_request_id) }}" class="space-y-3">
                            @csrf
                            <textarea name="review_notes" rows="3" required placeholder="Reason the applicant will see, e.g. couldn't verify the details provided."
                                class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary text-sm">{{ old('review_notes') }}</textarea>
                            @error('review_notes')
                                <p class="text-error text-label-sm">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="w-full px-4 py-2 bg-error text-on-error rounded-lg text-label-sm font-semibold">
                                Reject
                            </button>
                        </form>
                    </section>
                @endif
            </div>
        </div>
    </main>
@endsection
