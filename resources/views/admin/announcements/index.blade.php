@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
    @include('admin.sidebar')
    @include('admin.header')

    <div class="fixed top-header-height right-0 w-[calc(100%-260px)] h-[calc(100vh-72px)] overflow-y-auto bg-surface p-container-padding main-transition">

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:justify-between lg:items-end">
            <div>
                <nav class="flex items-center gap-2 text-on-surface-variant mb-2">
                    <span class="text-label-sm font-label-sm">Dashboard</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    <span class="text-label-sm font-label-sm text-primary font-bold">Announcements</span>
                </nav>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">Announcements</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Notices for families, aimed at the whole school, specific classes, or whole grade levels.
                </p>
            </div>
            <a href="{{ route('admin.announcements.create') }}"
                class="self-start inline-flex items-center gap-2 px-space-md py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-primary font-title-sm text-title-sm shadow-sm transition-all">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>New announcement</span>
            </a>
        </div>

        @if (session('notification'))
            <div class="mb-6 rounded-lg border border-secondary/30 bg-secondary-fixed px-4 py-3 font-body-md text-body-md text-on-surface">
                {{ session('notification') }}
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach ([
                ['Live', $counts['live'], 'campaign'],
                ['Scheduled', $counts['scheduled'], 'schedule'],
                ['Drafts', $counts['draft'], 'edit_note'],
                ['Expired', $counts['expired'], 'history'],
            ] as [$label, $value, $icon])
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">{{ $label }}</span>
                        <span class="material-symbols-outlined text-on-surface-variant text-[18px]">{{ $icon }}</span>
                    </div>
                    <p class="font-headline-md text-headline-md font-extrabold text-on-surface">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm overflow-hidden">
            @if ($announcements->isEmpty())
                <div class="py-16 text-center">
                    <span class="material-symbols-outlined text-[40px] text-outline">campaign</span>
                    <p class="mt-3 font-headline-sm text-headline-sm text-on-surface">No announcements yet</p>
                    <p class="mt-1 font-body-md text-body-md text-on-surface-variant">
                        Write one to reach families in the parent and student portals.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full font-body-md text-body-md">
                        <thead class="bg-surface-container-low font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">
                            <tr>
                                <th class="text-left font-medium px-4 py-3">Announcement</th>
                                <th class="text-left font-medium px-4 py-3">Reaches</th>
                                <th class="text-left font-medium px-4 py-3">Dates</th>
                                <th class="text-center font-medium px-4 py-3">State</th>
                                <th class="text-right font-medium px-4 py-3">Read</th>
                                <th class="text-right font-medium px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @foreach ($announcements as $announcement)
                                <tr class="hover:bg-surface-container-low/60 transition-colors align-top">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-on-surface">{{ $announcement->title }}</p>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5 max-w-md">
                                            {{ \Illuminate\Support\Str::limit($announcement->body, 90) }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-body-sm text-body-sm text-on-surface">{{ $announcement->audience_label }}</p>
                                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">{{ $announcement->target_summary }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-body-sm text-body-sm text-on-surface-variant whitespace-nowrap">
                                        {{ $announcement->published_at?->format('j M Y, H:i') ?? 'Not published' }}
                                        @if ($announcement->expires_at)
                                            <br>until {{ $announcement->expires_at->format('j M Y') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $stateTone = match ($announcement->state) {
                                                'Live'      => 'bg-green-50 text-green-700',
                                                'Scheduled' => 'bg-secondary-fixed text-on-secondary-container',
                                                'Expired'   => 'bg-surface-container text-on-surface-variant',
                                                default     => 'bg-tertiary-fixed text-on-surface-variant',
                                            };
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 rounded-lg font-label-sm text-label-sm font-semibold {{ $stateTone }}">
                                            {{ $announcement->state }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-data-mono text-data-mono text-on-surface-variant">
                                        {{ $announcement->reads_count }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.announcements.edit', $announcement->announcement_id) }}"
                                                title="Edit" class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors">
                                                <span class="material-symbols-outlined text-[20px]">edit</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement->announcement_id) }}"
                                                  onsubmit="return confirm('Remove “{{ $announcement->title }}”? It disappears from every feed.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Remove"
                                                    class="p-2 rounded-lg text-on-surface-variant hover:bg-error-container hover:text-error transition-colors">
                                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
