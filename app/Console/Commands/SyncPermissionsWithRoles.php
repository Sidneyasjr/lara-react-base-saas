<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class SyncPermissionsWithRoles extends Command
{
    protected $signature = 'permissions:sync-roles 
                           {--role= : Sync specific role}
                           {--auto-assign : Auto-assign permissions based on patterns}';

    protected $description = 'Sync permissions with roles based on patterns';

    public function handle()
    {
        $roleName = $this->option('role');
        $autoAssign = $this->option('auto-assign');

        $this->info('🔄 Syncing permissions with roles...');

        if ($roleName) {
            $roles = Role::where('name', $roleName)->get();
            if ($roles->isEmpty()) {
                $this->error("Role '{$roleName}' not found!");
                return;
            }
        } else {
            $roles = Role::all();
        }

        foreach ($roles as $role) {
            $this->info("\n👥 Processing role: {$role->name}");
            
            if ($autoAssign) {
                $this->autoAssignPermissions($role);
            } else {
                $this->showRolePermissions($role);
            }
        }

        $this->info("\n✅ Permission sync completed!");
    }

    private function autoAssignPermissions(Role $role)
    {
        $permissions = Permission::all();
        $currentPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = [];

        foreach ($permissions as $permission) {
            if (in_array($permission->name, $currentPermissions)) {
                continue;
            }

            $shouldAssign = $this->shouldAutoAssign($role, $permission);
            
            if ($shouldAssign) {
                $newPermissions[] = $permission->name;
                $this->line("  ✓ Auto-assigned: {$permission->name}");
            }
        }

        if (!empty($newPermissions)) {
            $role->givePermissionTo($newPermissions);
            $this->info("  🎯 Assigned {count($newPermissions)} new permissions to {$role->name}");
        } else {
            $this->line("  ℹ️ No new permissions to assign");
        }
    }

    private function shouldAutoAssign(Role $role, Permission $permission): bool
    {
        $roleName = strtolower($role->name);
        $permissionName = strtolower($permission->name);

        // Super Admin gets everything
        if (str_contains($roleName, 'super admin')) {
            return true;
        }

        // Admin patterns
        if (str_contains($roleName, 'admin')) {
            // Skip auth permissions for admin roles
            if (str_starts_with($permissionName, 'auth.register')) {
                return false;
            }
            
            // Give admin access to most admin routes
            if (str_starts_with($permissionName, 'admin.')) {
                return true;
            }
            
            // Give basic access
            if (in_array($permissionName, ['dashboard', 'home', 'api.menu', 'api.menu.breadcrumb'])) {
                return true;
            }
        }

        // Manager patterns
        if (str_contains($roleName, 'manager')) {
            // Read-only admin access
            if (str_contains($permissionName, '.index') || str_contains($permissionName, '.show')) {
                return true;
            }
            
            // Basic access
            if (in_array($permissionName, ['dashboard', 'home', 'api.menu', 'api.menu.breadcrumb'])) {
                return true;
            }
        }

        // User patterns
        if (str_contains($roleName, 'user')) {
            // Basic access only
            if (in_array($permissionName, [
                'dashboard', 'home', 'api.menu', 'api.menu.breadcrumb',
                'settings.profile.edit', 'settings.profile.update',
                'settings.password.edit', 'settings.password.update'
            ])) {
                return true;
            }
        }

        return false;
    }

    private function showRolePermissions(Role $role)
    {
        $permissions = $role->permissions;
        $this->line("  Current permissions: {$permissions->count()}");
        
        if ($permissions->isNotEmpty()) {
            $grouped = $permissions->groupBy('module');
            foreach ($grouped as $module => $modulePermissions) {
                $this->line("    📁 {$module}: {$modulePermissions->count()} permissions");
            }
        }
    }
}
