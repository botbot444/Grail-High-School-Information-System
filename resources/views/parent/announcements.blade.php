@extends('layouts.parent')

@section('title', 'Announcements – Parent Portal')

@section('page')
    <div class="max-w-7xl mx-auto space-y-6">

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="font-headline-md text-headline-md font-extrabold text-on-surface">School Announcements</h1>
                <p class="text-sm text-on-surface-variant mt-1">
                    Notices for every class your {{ $students->count() === 1 ? 'child is' : 'children are' }} in,
                    plus school-wide messages.
                </p>
            </div>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('parent.announcements.read-all') }}" class="self-start">
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
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-12 text-center">
                <span class="material-symbols-outlined text-4xl text-on-surface-variant">campaign</span>
                <p class="font-headline-sm text-headline-sm font-bold text-on-surface mt-3">No announcements right now</p>
                <p class="text-sm text-on-surface-variant mt-1 max-w-md mx-auto">
                    When the school posts a notice for your {{ $students->count() === 1 ? "child's class" : "children's classes" }}
                    or grade level, it will appear here.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($announcements as $announcement)
                    <article @class([
                        'bg-white rounded-xl border shadow-sm overflow-hidden',
                        'border-primary/40' => ! $announcement->is_read,
                        'border-outline-variant' => $announcement->is_read,
                    ])>
                        <div class="px-5 py-4 border-b border-outline-variant flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5 min-w-0">
                                @unless ($announcement->is_read)
                                    <span class="mt-2 w-2 h-2 rounded-full bg-primary shrink-0" aria-label="Unread"></span>
                                @endunless
                                <div class="min-w-0">
                                    <h2 class="font-headline-sm text-headline-sm font-bold text-on-surface">{{ $announcement->title }}</h2>
                                    <p class="text-xs text-on-surface-variant mt-0.5">
                                        {{ $announcement->published_at?->diffForHumans() }}
                                        · {{ $announcement->audience === 'all' ? 'Whole school' : $announcement->target_summary }}
                                    </p>
                                </div>
                            </div>

                            @unless ($announcement->is_read)
                                <form method="POST" action="{{ route('parent.announcements.read', $announcement->announcement_id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-primary hover:underline whitespace-nowrap">
                                        Mark read
                                    </button>
                                </form>
                            @endunless
                        </div>

                        <div class="px-5 py-4">
                            <p class="text-sm text-on-surface whitespace-pre-line leading-relaxed">{{ $announcement->body }}</p>
                            @if ($announcement->expires_at)
                                <p class="mt-4 text-xs text-on-surface-variant flex items-center gap-1.5">
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
