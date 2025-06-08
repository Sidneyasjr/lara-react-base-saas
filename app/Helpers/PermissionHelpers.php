<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('can_user')) {
    /**
     * Check if current user has permission.
     */
    function can_user(string $permission): bool
    {
        return Auth::check() && Auth::user()->can($permission);
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if current user has role.
     */
    function has_role(string $role): bool
    {
        return Auth::check() && Auth::user()->hasRole($role);
    }
}

if (!function_exists('has_any_role')) {
    /**
     * Check if current user has any of the given roles.
     */
    function has_any_role(array $roles): bool
    {
        return Auth::check() && Auth::user()->hasAnyRole($roles);
    }
}

if (!function_exists('user_permissions')) {
    /**
     * Get current user's permissions.
     */
    function user_permissions(): array
    {
        if (!Auth::check()) {
            return [];
        }

        return Auth::user()->getAllPermissions()->pluck('name')->toArray();
    }
}

if (!function_exists('user_roles')) {
    /**
     * Get current user's roles.
     */
    function user_roles(): array
    {
        if (!Auth::check()) {
            return [];
        }

        return Auth::user()->getRoleNames()->toArray();
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * Check if current user is super admin.
     */
    function is_super_admin(): bool
    {
        return has_role('Super Admin');
    }
}

if (!function_exists('can_manage_users')) {
    /**
     * Check if current user can manage users.
     */
    function can_manage_users(): bool
    {
        return can_user('create users') || can_user('edit users') || can_user('delete users');
    }
}

if (!function_exists('can_manage_roles')) {
    /**
     * Check if current user can manage roles.
     */
    function can_manage_roles(): bool
    {
        return can_user('create roles') || can_user('edit roles') || can_user('delete roles');
    }
}
