<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * Get all permissions grouped by module.
     */
    public function getPermissionsByModule(): Collection
    {
        return Permission::all()->groupBy('module');
    }

    /**
     * Get all available modules.
     */
    public function getModules(): array
    {
        return Permission::getModules();
    }

    /**
     * Check if user has permission for a specific action and module.
     */
    public function userCan(User $user, string $action, string $module): bool
    {
        $permission = "{$action} {$module}";
        return $user->can($permission);
    }

    /**
     * Get user's permissions organized by module.
     */
    public function getUserPermissionsByModule(User $user): Collection
    {
        return $user->getAllPermissions()->groupBy('module');
    }

    /**
     * Assign role to user with validation.
     */
    public function assignRole(User $user, string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            return false;
        }

        $user->assignRole($role);
        return true;
    }

    /**
     * Remove role from user.
     */
    public function removeRole(User $user, string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            return false;
        }

        $user->removeRole($role);
        return true;
    }

    /**
     * Get all roles with their permissions count.
     */
    public function getRolesWithPermissionCount(): Collection
    {
        return Role::withCount('permissions')->get();
    }

    /**
     * Create a new role with permissions.
     */
    public function createRole(string $name, string $description, array $permissions = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'description' => $description,
            'guard_name' => 'web',
        ]);

        if (!empty($permissions)) {
            $role->givePermissionTo($permissions);
        }

        return $role;
    }

    /**
     * Update role permissions.
     */
    public function updateRolePermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);
        return $role;
    }

    /**
     * Get permissions that a user has through roles and direct assignments.
     */
    public function getUserPermissions(User $user): array
    {
        return [
            'via_roles' => $user->getPermissionsViaRoles(),
            'direct' => $user->getDirectPermissions(),
            'all' => $user->getAllPermissions(),
        ];
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function userHasAnyPermission(User $user, array $permissions): bool
    {
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function userHasAllPermissions(User $user, array $permissions): bool
    {
        return $user->hasAllPermissions($permissions);
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName): Collection
    {
        return User::role($roleName)->get();
    }

    /**
     * Get role statistics.
     */
    public function getRoleStatistics(): array
    {
        $roles = Role::withCount(['users', 'permissions'])->get();
        
        return [
            'total_roles' => $roles->count(),
            'total_permissions' => Permission::count(),
            'roles_breakdown' => $roles->map(function ($role) {
                return [
                    'name' => $role->name,
                    'description' => $role->description,
                    'users_count' => $role->users_count,
                    'permissions_count' => $role->permissions_count,
                ];
            }),
        ];
    }
}
