@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <main id="mainContent"
        class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Dashboard</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Audit Logs</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">
                    System Audit Trail
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Track every change across fees, grades, students and teachers.
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="mb-6 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">User</label>
                    <select name="user_id"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                        <option value="">All users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Model Type</label>
                    <select name="model_type"
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary">
                        <option value="">All models</option>
                        @foreach ($modelTypes as $model)
                            <option value="{{ $model }}" {{ request('model_type') == $model ? 'selected' : '' }}>
                                {{ $model }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="md:w-96">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reason or model name..."
                        class="w-full rounded-lg border border-outline-variant px-3 py-2 focus:ring-2 focus:ring-primary" />
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-label-sm font-semibold">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-surface-container text-on-surface border border-outline-variant rounded-lg text-label-sm">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-outline-variant bg-surface-container">
                            <th class="px-4 py-3 text-label-sm font-semibold text-on-surface-variant">Timestamp</th>
                            <th class="px-4 py-3 text-label-sm font-semibold text-on-surface-variant">User</th>
                            <th class="px-4 py-3 text-label-sm font-semibold text-on-surface-variant">Model</th>
                            <th class="px-4 py-3 text-label-sm font-semibold text-on-surface-variant text-center">Action</th>
                            <th class="px-4 py-3 text-label-sm font-semibold text-on-surface-variant">Old Values</th>
                            <th class="px-4 py-3 text-label-sm font-semibold text-on-surface-variant">New Values</th>
                            <th class="px-4 py-3 text-label-sm font-semibold text-on-surface-variant">Reason</th>
                            <th class="px-4 py-3 text-label-sm font-semibold text-on-surface-variant">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="border-b border-outline-variant/60 hover:bg-surface-container-high/60 transition-colors">
                                <td class="px-4 py-3 text-body-sm text-on-surface whitespace-nowrap">
                                    {{ $log->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-body-sm text-on-surface">
                                    {{ $log->user->name ?? 'System' }}
                                </td>
                                <td class="px-4 py-3 text-body-sm text-on-surface font-mono">
                                    {{ class_basename($log->auditable_type) }}
                                    <span class="text-on-surface-variant">#{{ $log->auditable_id }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $color = match($log->action) {
                                            'created' => 'bg-green-100 text-green-700',
                                            'updated' => 'bg-blue-100 text-blue-700',
                                            'deleted' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="inline-block px-3 py-1 rounded-full text-label-sm font-semibold {{ $color }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-body-sm text-on-surface">
                                    {{ $log->old_values ? json_encode($log->old_values) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-body-sm text-on-surface">
                                    {{ $log->new_values ? json_encode($log->new_values) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-body-sm text-on-surface">
                                    {{ $log->reason ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-body-sm text-on-surface">
                                    {{ $log->ip_address ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-on-surface-variant">
                                    No audit logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div
                class="px-6 py-4 bg-surface-container-low border-t border-outline-variant flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
                <p class="font-label-sm text-label-sm text-on-surface-variant">
                    Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }}
                    records
                </p>
                <div class="flex items-center justify-end">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </main>
@endsection
