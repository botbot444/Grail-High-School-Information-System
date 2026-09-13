@extends('layouts.student')

@section('title', 'Announcements')
@section('page-title', 'School Announcements')
@section('page-subtitle', 'Notices for your class and grade level')

@section('content')
    @php $unread = $announcements->where('is_read', false)->count(); @endphp

    <div class="flex flex-wrap items-center justify-between gap-space-md mb-space-lg">
        <p class="text-body-md text-on-surface-variant">
            @if ($unread > 0)
                <span class="font-semibold text-on-surface">{{ $unread }} unread</span> of {{ $announcements->count() }}
            @else
                All caught up — {{ $announcements->count() }} notice{{ $announcements->count() === 1 ? '' : 's' }}
            @endif
        </p>

        @if ($unread > 0)
            <form method="POST" action="{{ route('student.announcements.read-all') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-outline-variant/60 text-on-surface-variant hover:bg-surface-container-low hover:text-primary text-label-md font-medium transition-colors">
                    <span class="material-symbols-outlined text-lg">done_all</span>
                    Mark all read
                </button>
            </form>
        @endif
    </div>

    @if ($announcements->isEmpty())
        <section class="bg-surface-container-lowest rounded-xl border border-outline-variant/50 card-shadow">
            @include('student.partials.empty-state', [
                'icon'    => 'campaign',
                'title'   => 'No announcements right now',
                'message' => 'Notices from the school aimed at your class or grade level will appear here, newest first.',
            ])
        </section>
    @else
        <div class="space-y-space-md">
            @foreach ($announcements as $announcement)
                <article @class([
                    'bg-surface-container-lowest rounded-xl border card-shadow overflow-hidden',
                    'border-secondary/40' => ! $announcement->is_read,
                    'border-outline-variant/50' => $announcement->is_read,
                ])>
                    <header class="px-space-md py-space-sm border-b border-outline-variant/40 flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex items-start gap-2.5">
                            @unless ($announcement->is_read)
                                <span class="mt-2 w-2 h-2 rounded-[50%] bg-secondary shrink-0" aria-label="Unread"></span>
                            @endunless
                            <div class="min-w-0">
                                <h2 class="text-headline-sm font-headline-sm text-on-surface">{{ $announcement->title }}</h2>
                                <p class="text-body-sm text-on-surface-variant mt-0.5">
                                    {{ $announcement->published_at?->diffForHumans() }}
                                    @if ($announcement->audience !== 'all')
                                        · {{ $announcement->target_summary }}
                                    @else
                                        · Whole school
                                    @endif
                                </p>
                            </div>
                        </div>

                        @unless ($announcement->is_read)
                            <form method="POST" action="{{ route('student.announcements.read', $announcement->announcement_id) }}">
                                @csrf
                                <button type="submit"
                                    class="text-label-md font-medium text-primary hover:underline whitespace-nowrap">
                                    Mark read
                                </button>
                            </form>
                        @endunless
                    </header>

                    <div class="px-space-md py-space-md">
                        <p class="text-body-md text-on-surface whitespace-pre-line leading-relaxed">{{ $announcement->body }}</p>
                        @if ($announcement->expires_at)
                            <p class="mt-space-md text-label-sm text-on-surface-variant flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                Removed after {{ $announcement->expires_at->format('j M Y') }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
