<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportPolicy
{
    /**
     * Ability flags are used at the Gate level; the admin group is already
     * protected by the role:admin middleware, so these are defence-in-depth
     * that also work if the optional RBAC (permissions) tables exist.
     */
    public function viewReports(User $user): bool
    {
        return $user->isAdmin() || $this->hasPermission($user, 'view_reports');
    }

    public function exportReports(User $user): bool
    {
        return $user->isAdmin() || $this->hasPermission($user, 'export_reports');
    }

    public function viewFinancials(User $user): bool
    {
        return $user->isAdmin() || $this->hasPermission($user, 'view_financials');
    }

    /**
     * Check a permission against the optional role_permission tables when they
     * exist. Returns false when RBAC has not been set up yet (the Phase 4
     * migrations are optional), so non-admin users simply fall back to denied.
     */
    private function hasPermission(User $user, string $permission): bool
    {
        if (!$user->role_id) {
            return false;
        }

        if (!Schema::hasTable('permissions') || !Schema::hasTable('role_permission')) {
            return false;
        }

        $permissionId = DB::table('permissions')->where('name', $permission)->value('id');

        if (!$permissionId) {
            return false;
        }

        return DB::table('role_permission')
            ->where('role_id', $user->role_id)
            ->where('permission_id', $permissionId)
            ->exists();
    }
}