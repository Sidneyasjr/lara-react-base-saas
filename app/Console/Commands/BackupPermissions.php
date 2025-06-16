<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Storage;

class BackupPermissions extends Command
{
    protected $signature = 'permissions:backup 
                           {--restore= : Restore from backup file}
                           {--filename= : Custom backup filename}';

    protected $description = 'Backup or restore permissions, roles and menu structure';

    public function handle()
    {
        $restoreFile = $this->option('restore');
        $filename = $this->option('filename') ?? 'permissions_backup_' . date('Y-m-d_H-i-s') . '.json';

        if ($restoreFile) {
            $this->restoreFromBackup($restoreFile);
        } else {
            $this->createBackup($filename);
        }
    }

    private function createBackup(string $filename): void
    {
        $this->info('🔄 Creating permissions backup...');

        $backup = [
            'timestamp' => now()->toISOString(),
            'version' => '1.0',
            'permissions' => Permission::all()->map(function ($permission) {
                return [
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name,
                    'module' => $permission->module,
                    'description' => $permission->description,
                ];
            })->toArray(),
            'roles' => Role::with('permissions')->get()->map(function ($role) {
                return [
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'description' => $role->description,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ];
            })->toArray(),
            'menu_items' => MenuItem::all()->map(function ($item) {
                return [
                    'title' => $item->title,
                    'route_name' => $item->route_name,
                    'icon' => $item->icon,
                    'permission_required' => $item->permission_required,
                    'order_index' => $item->order_index,
                    'is_active' => $item->is_active,
                    'module' => $item->module,
                    'parent_id' => $item->parent_id,
                ];
            })->toArray(),
        ];

        $backupPath = storage_path('app/backups');
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $fullPath = $backupPath . '/' . $filename;
        file_put_contents($fullPath, json_encode($backup, JSON_PRETTY_PRINT));

        $this->info("✅ Backup created: {$fullPath}");
        $this->info("📊 Backup contains:");
        $this->line("  • " . count($backup['permissions']) . " permissions");
        $this->line("  • " . count($backup['roles']) . " roles");
        $this->line("  • " . count($backup['menu_items']) . " menu items");
    }

    private function restoreFromBackup(string $filename): void
    {
        $backupPath = storage_path('app/backups/' . $filename);
        
        if (!file_exists($backupPath)) {
            $this->error("Backup file not found: {$backupPath}");
            return;
        }

        $this->warn('⚠️ This will overwrite existing permissions, roles and menu items!');
        if (!$this->confirm('Do you want to continue?')) {
            $this->info('Restore cancelled.');
            return;
        }

        $this->info('🔄 Restoring from backup...');

        $backup = json_decode(file_get_contents($backupPath), true);

        if (!$backup || !isset($backup['permissions'], $backup['roles'], $backup['menu_items'])) {
            $this->error('Invalid backup file format!');
            return;
        }

        // Clear existing data
        $this->info('🗑️ Clearing existing data...');
        MenuItem::query()->delete();
        Role::query()->delete();
        Permission::query()->delete();

        // Restore permissions
        $this->info('📝 Restoring permissions...');
        foreach ($backup['permissions'] as $permData) {
            Permission::create($permData);
        }

        // Restore roles
        $this->info('👥 Restoring roles...');
        foreach ($backup['roles'] as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            
            $role = Role::create($roleData);
            if (!empty($permissions)) {
                $role->givePermissionTo($permissions);
            }
        }

        // Restore menu items
        $this->info('📱 Restoring menu items...');
        foreach ($backup['menu_items'] as $menuData) {
            MenuItem::create($menuData);
        }

        $this->info("✅ Restore completed from backup: {$filename}");
        $this->info("📊 Restored:");
        $this->line("  • " . count($backup['permissions']) . " permissions");
        $this->line("  • " . count($backup['roles']) . " roles");
        $this->line("  • " . count($backup['menu_items']) . " menu items");
    }
}
