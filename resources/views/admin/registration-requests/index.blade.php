@extends('layouts.app')

@section('title', 'Registration Requests')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">
        @include('admin.partials.flash')

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">People</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Registration Requests</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Registration Requests</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Parents who signed themselves and a new child up for review — nothing is active until you approve it.
                </p>
            </div>
        </div>

        {{-- Status tabs --}}
        <div class="flex items-center gap-2 mb-6 border-b border-outline-variant">
            @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
                <a href="{{ route('admin.registration-requests.index', ['status' => $key]) }}"
                    class="px-4 py-2.5 text-label-sm font-semibold border-b-2 -mb-px transition-colors
                           {{ $status === $key ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
                    {{ $label }}
                    @if ($key === 'pending' && $counts['pending'] > 0)
                        <span class="ml-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">{{ $counts['pending'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-outline-variant bg-surface-container">
                            <th class="px-5 py-3 text-label-sm font-semibold text-on-surface-variant">Parent</th>
                            <th class="px-5 py-3 text-label-sm font-semibold text-on-surface-variant">Child</th>
                            <th class="px-5 py-3 text-label-sm font-semibold text-on-surface-variant">Submitted</th>
                            <th class="px-5 py-3 text-label-sm font-semibold text-on-surface-variant">Status</th>
                            <th class="px-5 py-3 text-label-sm font-semibold text-on-surface-variant text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $registrationRequest)
                            @php
                                $badge = match ($registrationRequest->status) {
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default    => 'bg-amber-100 text-amber-800',
                                };
                            @endphp
                            <tr class="border-b border-outline-variant/60 hover:bg-surface-container">
                                <td class="px-5 py-3 text-body-sm text-on-surface font-semibold">
                                    {{ $registrationRequest->parent_full_name }}
                                    <span class="block text-xs text-on-surface-variant font-normal">{{ $registrationRequest->parent_email }}</span>
                                </td>
                                <td class="px-5 py-3 text-body-sm text-on-surface">
                                    {{ $registrationRequest->child_full_name }}
                                    <span class="block text-xs text-on-surface-variant">{{ $registrationRequest->child_date_of_birth->format('d M Y') }}</span>
                                </td>
                                <td class="px-5 py-3 text-body-sm text-on-surface-variant whitespace-nowrap">{{ $registrationRequest->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold {{ $badge }}">{{ $registrationRequest->status_label }}</span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('admin.registration-requests.show', $registrationRequest->registration_request_id) }}"
                                        class="text-primary hover:underline text-label-sm font-semibold">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-on-surface-variant text-body-sm">
                                    Nothing here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </main>
@endsection
