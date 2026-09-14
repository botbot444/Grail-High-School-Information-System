<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase 12 — deactivation takes effect immediately.
 *
 * Blocking at login is not enough on its own: someone already signed in would
 * keep their session until it expired. This ends it on their next request, so
 * an admin deactivating an account actually removes access there and then.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'This account has been deactivated. Please contact the school office.']);
        }

        return $next($request);
    }
}
