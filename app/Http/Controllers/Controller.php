<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Laravel 11 dropped AuthorizesRequests from the base controller, so
 * $this->authorize(...) is undefined unless a controller pulls the trait in
 * itself. AnalyticsController did not, which made every Phase 9 report — fee
 * aging, attendance, school-wide and their CSV exports — throw a 500 on the
 * first line of the method. Adding it here fixes those and stops the next
 * controller hitting the same wall.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
