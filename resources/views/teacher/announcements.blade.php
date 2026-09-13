@extends('layouts.teacher')

@section('title', 'Announcements – Teacher Portal')

@section('page')
    <div class="mx-auto flex max-w-5xl flex-col gap-space-lg">

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="font-label-sm text-label-sm uppercase tracking-wider text-secondary">Teacher Portal / Announcements</p>
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Announcements</h1>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                    School-wide notices from the admin team.
                </p>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('teacher.announcements.read-all') }}" class="self-start">
                    @csrf
                    <button type="submit"
                        class="text-xs font-bold bg-primary text-on-primary rounded-lg px-3 py-2 hover:bg-primary/90 transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined" style="font-size:14px">done_all</span>
                        Mark all {{ $unreadCount }} read
                    </button>
                </form>
            @endif
        </div>

        @if ($announcements->isEmpty())
            <div class="rounded-xl bg-surface-container-lowest shadow-sm p-12 text-center">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant">campaign</span>
                <p class="font-headline-sm text-headline-sm font-bold text-on-surface mt-3">No announcements right now</p>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 max-w-md mx-auto">
                    When the school posts a school-wide notice, it will appear here.
                </p>
            </div>
        @else
            <div class="flex flex-col gap-space-md">
                @foreach ($announcements as $announcement)
                    <article @class([
                        'rounded-xl shadow-sm overflow-hidden bg-surface-container-lowest border',
                        'border-primary/40' => ! $announcement->is_read,
                        'border-outline-variant' => $announcement->is_read,
                    ])>
                        <div class="px-space-lg py-space-md border-b border-outline-variant flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5 min-w-0">
                                @unless ($announcement->is_read)
                                    <span class="mt-2 w-2 h-2 rounded-full bg-primary shrink-0" aria-label="Unread"></span>
                                @endunless
                                <div class="min-w-0">
                                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">{{ $announcement->title }}</h2>
                                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                                        {{ $announcement->published_at?->diffForHumans() }}
                                        · {{ $announcement->audience === 'all' ? 'Whole school' : $announcement->target_summary }}
                                    </p>
                                </div>
                            </div>

                            @unless ($announcement->is_read)
                                <form method="POST" action="{{ route('teacher.announcements.read', $announcement->announcement_id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-primary hover:underline whitespace-nowrap">
                                        Mark read
                                    </button>
                                </form>
                            @endunless
                        </div>

                        <div class="px-space-lg py-space-md">
                            <p class="font-body-md text-body-md text-on-surface whitespace-pre-line leading-relaxed">{{ $announcement->body }}</p>
                            @if ($announcement->expires_at)
                                <p class="mt-4 font-body-sm text-body-sm text-on-surface-variant flex items-center gap-1.5">
                                    <span class="material-symbols-outlined" style="font-size:14px">schedule</span>
                                    Removed after {{ $announcement->expires_at->format('j M Y') }}
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
