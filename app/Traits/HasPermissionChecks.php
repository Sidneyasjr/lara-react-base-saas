<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

trait HasPermissionChecks
{
    /**
     * Check if user has permission and return error response if not.
     */
    protected function checkPermission(string $permission): ?JsonResponse
    {
        if (!Auth::user()->can($permission)) {
            return response()->json([
                'error' => 'Você não tem permissão para realizar esta ação.',
                'required_permission' => $permission,
            ], 403);
        }

        return null;
    }

    /**
     * Check if user has any of the given permissions.
     */
    protected function checkAnyPermission(array $permissions): ?JsonResponse
    {
        if (!Auth::user()->hasAnyPermission($permissions)) {
            return response()->json([
                'error' => 'Você não tem permissão para realizar esta ação.',
                'required_permissions' => $permissions,
            ], 403);
        }

        return null;
    }

    /**
     * Check if user has role and return error response if not.
     */
    protected function checkRole(string $role): ?JsonResponse
    {
        if (!Auth::user()->hasRole($role)) {
            return response()->json([
                'error' => 'Você não tem o role necessário para realizar esta ação.',
                'required_role' => $role,
            ], 403);
        }

        return null;
    }

    /**
     * Check if user has any of the given roles.
     */
    protected function checkAnyRole(array $roles): ?JsonResponse
    {
        if (!Auth::user()->hasAnyRole($roles)) {
            return response()->json([
                'error' => 'Você não tem o role necessário para realizar esta ação.',
                'required_roles' => $roles,
            ], 403);
        }

        return null;
    }

    /**
     * Check permission and redirect if not authorized (for web routes).
     */
    protected function checkPermissionWeb(string $permission): ?RedirectResponse
    {
        if (!Auth::user()->can($permission)) {
            return redirect()->back()
                ->with('error', 'Você não tem permissão para realizar esta ação.');
        }

        return null;
    }

    /**
     * Check if user is super admin.
     */
    protected function isSuperAdmin(): bool
    {
        return Auth::user()->hasRole('Super Admin');
    }

    /**
     * Check if user can manage users.
     */
    protected function canManageUsers(): bool
    {
        return Auth::user()->can('create users') || 
               Auth::user()->can('edit users') || 
               Auth::user()->can('delete users');
    }

    /**
     * Check if user can manage roles.
     */
    protected function canManageRoles(): bool
    {
        return Auth::user()->can('create roles') || 
               Auth::user()->can('edit roles') || 
               Auth::user()->can('delete roles');
    }

    /**
     * Get user's module permissions.
     */
    protected function getUserModulePermissions(string $module): array
    {
        return Auth::user()
            ->getAllPermissions()
            ->where('module', $module)
            ->pluck('name')
            ->map(function ($permission) use ($module) {
                return str_replace($module . '.', '', $permission);
            })
            ->toArray();
    }

    /**
     * Check if user has full access to a module.
     */
    protected function hasFullModuleAccess(string $module): bool
    {
        $requiredPermissions = [
            $module . '.view',
            $module . '.create',
            $module . '.edit',
            $module . '.delete',
        ];

        return Auth::user()->hasAllPermissions($requiredPermissions);
    }
}
