<?php

namespace App\Providers;

use App\View\Composers\StudentPortalComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Payment::class, \App\Policies\PaymentPolicy::class);

        // Phase 4 — Report & analytics access (delegates to ReportPolicy).
        Gate::define('viewReports',   [\App\Policies\ReportPolicy::class, 'viewReports']);
        Gate::define('exportReports', [\App\Policies\ReportPolicy::class, 'exportReports']);
        Gate::define('viewFinancials',[\App\Policies\ReportPolicy::class, 'viewFinancials']);

        // Student portal chrome (sidebar profile, term badge, assignments-due count).
        View::composer(['layouts.student', 'student.partials.*'], StudentPortalComposer::class);
    }
}