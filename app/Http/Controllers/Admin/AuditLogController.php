<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()
            ->with('user')
            ->forDateRange(
                $request->input('date_from'),
                $request->input('date_to')
            )
            ->forUser($request->input('user_id'))
            ->forModelType($request->input('model_type'))
            ->search($request->input('search'));

        $logs = $query->orderByDesc('created_at')->paginate(15);

        $users      = User::orderBy('name')->get(['id', 'name']);
        $modelTypes = AuditLog::select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');

        return view('admin.audit-logs.index', compact('logs', 'users', 'modelTypes'));
    }
}
