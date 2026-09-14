<?php

namespace App\Observers;

use App\Services\AnalyticsService;

/**
 * Phase 9 — keeps cached reports honest.
 *
 * Attached to every model whose rows the reports read. A teacher entering marks
 * at 10:05 should see them in the school-wide report immediately, not after the
 * hour's cache expires.
 *
 * Flushing bumps a version number rather than deleting keys, so it costs one
 * cache write regardless of how many reports are cached.
 */
class ReportCacheObserver
{
    public function saved(mixed $model): void
    {
        AnalyticsService::flush();
    }

    public function deleted(mixed $model): void
    {
        AnalyticsService::flush();
    }

    public function restored(mixed $model): void
    {
        AnalyticsService::flush();
    }
}
