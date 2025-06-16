<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\MenuItem;
use Illuminate\Support\Str;

class GeneratePermissionsReport extends Command
{
    protected $signature = 'permissions:report 
                           {--format=table : Output format (table, json, csv)}
                           {--export= : Export to file}
                           {--role= : Filter by specific role}';

    protected $description = 'Generate comprehensive permissions report';

    public function handle()
    {
        $format = $this->option('format');
        $exportFile = $this->option('export');
        $roleFilter = $this->option('role');

        $this->info('📊 Generating permissions report...');

        $data = $this->gatherReportData($roleFilter);

        switch ($format) {
            case 'json':
                $output = $this->formatAsJson($data);
                break;
            case 'csv':
                $output = $this->formatAsCsv($data);
                break;
            default:
                $this->displayAsTable($data);
                return;
        }

        if ($exportFile) {
            file_put_contents($exportFile, $output);
            $this->info("Report exported to: {$exportFile}");
        } else {
            $this->line($output);
        }
    }

    private function gatherReportData(?string $roleFilter = null): array
    {
        $roles = $roleFilter ? 
            Role::where('name', $roleFilter)->get() : 
            Role::with(['permissions', 'users'])->get();

        $permissions = Permission::all()->groupBy('module');
        $menuItems = MenuItem::all();

        return [
            'summary' => [
                'total_permissions' => Permission::count(),
                'total_roles' => Role::count(),
                'total_users' => User::count(),
                'total_menu_items' => MenuItem::count(),
                'modules' => $permissions->keys()->toArray(),
            ],
            'roles' => $roles->map(function ($role) {
                return [
                    'name' => $role->name,
                    'description' => $role->description,
                    'users_count' => $role->users->count(),
                    'permissions_count' => $role->permissions->count(),
                    'permissions' => $role->permissions->groupBy('module')->map(function ($perms, $module) {
                        return [
                            'module' => $module,
                            'count' => $perms->count(),
                            'permissions' => $perms->pluck('name')->toArray(),
                        ];
                    })->values()->toArray(),
                ];
            })->toArray(),
            'permissions_by_module' => $permissions->map(function ($perms, $module) {
                return [
                    'module' => $module,
                    'count' => $perms->count(),
                    'permissions' => $perms->map(function ($perm) {
                        return [
                            'name' => $perm->name,
                            'description' => $perm->description,
                            'roles_count' => $perm->roles->count(),
                            'roles' => $perm->roles->pluck('name')->toArray(),
                        ];
                    })->toArray(),
                ];
            })->values()->toArray(),
            'menu_analysis' => [
                'total_items' => $menuItems->count(),
                'items_with_permissions' => $menuItems->whereNotNull('permission_required')->count(),
                'items_without_permissions' => $menuItems->whereNull('permission_required')->count(),
                'modules' => $menuItems->groupBy('module')->map(function ($items, $module) {
                    return [
                        'module' => $module,
                        'count' => $items->count(),
                        'with_permissions' => $items->whereNotNull('permission_required')->count(),
                    ];
                })->values()->toArray(),
            ],
        ];
    }

    private function displayAsTable(array $data): void
    {
        // Summary
        $this->info("\n📋 SUMMARY");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Permissions', $data['summary']['total_permissions']],
                ['Total Roles', $data['summary']['total_roles']],
                ['Total Users', $data['summary']['total_users']],
                ['Total Menu Items', $data['summary']['total_menu_items']],
                ['Modules', implode(', ', $data['summary']['modules'])],
            ]
        );

        // Roles breakdown
        $this->info("\n👥 ROLES BREAKDOWN");
        $rolesTable = [];
        foreach ($data['roles'] as $role) {
            $rolesTable[] = [
                $role['name'],
                $role['users_count'],
                $role['permissions_count'],
                Str::limit($role['description'] ?? '', 40),
            ];
        }
        $this->table(['Role', 'Users', 'Permissions', 'Description'], $rolesTable);

        // Permissions by module
        $this->info("\n🔑 PERMISSIONS BY MODULE");
        $moduleTable = [];
        foreach ($data['permissions_by_module'] as $module) {
            $moduleTable[] = [
                $module['module'],
                $module['count'],
            ];
        }
        $this->table(['Module', 'Permissions Count'], $moduleTable);

        // Menu analysis
        $this->info("\n📱 MENU ANALYSIS");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Menu Items', $data['menu_analysis']['total_items']],
                ['Items with Permissions', $data['menu_analysis']['items_with_permissions']],
                ['Items without Permissions', $data['menu_analysis']['items_without_permissions']],
            ]
        );
    }

    private function formatAsJson(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    private function formatAsCsv(array $data): string
    {
        $csv = "Type,Name,Count,Details\n";
        
        // Summary
        $csv .= "Summary,Total Permissions,{$data['summary']['total_permissions']},\n";
        $csv .= "Summary,Total Roles,{$data['summary']['total_roles']},\n";
        $csv .= "Summary,Total Users,{$data['summary']['total_users']},\n";
        
        // Roles
        foreach ($data['roles'] as $role) {
            $csv .= "Role,{$role['name']},{$role['permissions_count']},{$role['users_count']} users\n";
        }
        
        // Modules
        foreach ($data['permissions_by_module'] as $module) {
            $csv .= "Module,{$module['module']},{$module['count']},\n";
        }
        
        return $csv;
    }
}
