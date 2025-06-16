<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class ValidatePermissions extends Command
{
    protected $signature = 'permissions:validate 
                           {--fix : Automatically fix issues}
                           {--remove-orphaned : Remove permissions not matching any route}';

    protected $description = 'Validate permissions against routes and roles';

    public function handle()
    {
        $fix = $this->option('fix');
        $removeOrphaned = $this->option('remove-orphaned');

        $this->info('🔍 Validating permissions system...');

        // Get all routes with names
        $routes = collect(Route::getRoutes())
            ->filter(fn($route) => $route->getName())
            ->map(fn($route) => $route->getName())
            ->toArray();

        // Get all permissions
        $permissions = Permission::all();
        
        $issues = [];

        // Check for permissions without matching routes
        $orphanedPermissions = $permissions->filter(function ($permission) use ($routes) {
            return !in_array($permission->name, $routes);
        });

        if ($orphanedPermissions->isNotEmpty()) {
            $this->warn("\n⚠️ Found {$orphanedPermissions->count()} permissions without matching routes:");
            foreach ($orphanedPermissions as $permission) {
                $this->line("  ❌ {$permission->name} (Module: {$permission->module})");
                
                if ($removeOrphaned && $fix) {
                    $permission->delete();
                    $this->line("    🗑️ Removed");
                }
            }
            $issues[] = "Orphaned permissions: {$orphanedPermissions->count()}";
        }

        // Check for routes without permissions
        $routesWithoutPermissions = collect($routes)->filter(function ($route) use ($permissions) {
            return !$permissions->pluck('name')->contains($route);
        });

        if ($routesWithoutPermissions->isNotEmpty()) {
            $this->warn("\n⚠️ Found {$routesWithoutPermissions->count()} routes without permissions:");
            foreach ($routesWithoutPermissions->take(10) as $route) {
                $this->line("  ❌ {$route}");
            }
            if ($routesWithoutPermissions->count() > 10) {
                $remaining = $routesWithoutPermissions->count() - 10;
                $this->line("  ... and {$remaining} more");
            }
            $issues[] = "Routes without permissions: {$routesWithoutPermissions->count()}";
        }

        // Check for users without roles
        $usersWithoutRoles = User::doesntHave('roles')->count();
        if ($usersWithoutRoles > 0) {
            $this->warn("\n⚠️ Found {$usersWithoutRoles} users without roles");
            $issues[] = "Users without roles: {$usersWithoutRoles}";
        }

        // Check for roles without permissions
        $rolesWithoutPermissions = Role::doesntHave('permissions')->get();
        if ($rolesWithoutPermissions->isNotEmpty()) {
            $this->warn("\n⚠️ Found roles without permissions:");
            foreach ($rolesWithoutPermissions as $role) {
                $this->line("  ❌ {$role->name}");
            }
            $issues[] = "Roles without permissions: {$rolesWithoutPermissions->count()}";
        }

        // Summary
        if (empty($issues)) {
            $this->info("\n✅ All permissions are valid!");
        } else {
            $this->error("\n❌ Found issues:");
            foreach ($issues as $issue) {
                $this->line("  • {$issue}");
            }
            
            if (!$fix) {
                $this->info("\nUse --fix to automatically resolve some issues");
                $this->info("Use --remove-orphaned with --fix to remove orphaned permissions");
            }
        }

        // Performance check
        $this->info("\n📊 Performance Stats:");
        $this->line("  • Total permissions: " . $permissions->count());
        $this->line("  • Total routes: " . count($routes));
        $this->line("  • Total roles: " . Role::count());
        $this->line("  • Total users: " . User::count());
    }
}
