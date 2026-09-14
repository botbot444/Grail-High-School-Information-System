<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as RouteFacade;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locks an account down to its own settings page (and logout) until it
 * changes its password, for any user created with a system-generated
 * one-time password instead of one they chose themselves.
 *
 * Currently only set true on parent accounts created by an admin — see
 * AdminParentController@store — but the flag lives on `users` generically,
 * so any role can use it. PasswordController@update clears the flag once a
 * new password is saved.
 */
class EnsurePasswordIsChanged
{
    /** Route names always reachable, even while the flag is set. */
    private const ALLOWED_ROUTES = [
        'password.update',
        'logout',
        'admin.settings',
        'teacher.settings',
        'parent.settings',
        'student.settings',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        $settingsRoute = match (true) {
            $user->hasRole('admin')   => 'admin.settings',
            $user->hasRole('teacher') => 'teacher.settings',
            $user->hasRole('parent')  => 'parent.settings',
            $user->hasRole('student') => 'student.settings',
            default => null,
        };

        // No settings page for this role to send them to (or it isn't
        // registered) — don't lock someone out of the whole app over it.
        if (! $settingsRoute || ! RouteFacade::has($settingsRoute)) {
            return $next($request);
        }

        if ($routeName === $settingsRoute) {
            return $next($request);
        }

        return redirect()->route($settingsRoute)
            ->with('notification', 'For security, please set a new password before continuing.');
    }
}
