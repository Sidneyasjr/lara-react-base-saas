<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class GeneratePermissionsFromRoutes extends Command
{
    protected $signature = 'permissions:generate-from-routes 
                           {--dry-run : Show what would be generated without creating}
                           {--filter= : Filter routes by prefix}';

    protected $description = 'Generate permissions based on existing routes';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $filter = $this->option('filter');

        $this->info('🔍 Analyzing routes...');

        $routes = collect(Route::getRoutes())
            ->filter(function ($route) use ($filter) {
                $name = $route->getName();
                if (!$name) return false;
                
                if ($filter) {
                    return Str::startsWith($name, $filter);
                }
                
                // Skip auth routes and other common routes
                $skipPrefixes = ['password.', 'verification.', 'storage.'];
                foreach ($skipPrefixes as $prefix) {
                    if (Str::startsWith($name, $prefix)) {
                        return false;
                    }
                }
                
                return true;
            })
            ->map(function ($route) {
                return [
                    'name' => $route->getName(),
                    'uri' => $route->uri(),
                    'methods' => implode('|', $route->methods()),
                    'action' => $route->getActionName(),
                ];
            })
            ->sortBy('name');

        if ($routes->isEmpty()) {
            $this->warn('No routes found matching the criteria.');
            return;
        }

        $this->info("Found {$routes->count()} routes");

        // Group routes by module
        $groupedRoutes = $routes->groupBy(function ($route) {
            $parts = explode('.', $route['name']);
            return $parts[0] ?? 'general';
        });

        $permissions = collect();
        $menuItems = collect();

        foreach ($groupedRoutes as $module => $moduleRoutes) {
            $this->info("\n📁 Module: " . Str::title($module));
            
            foreach ($moduleRoutes as $route) {
                $permissionName = $route['name'];
                $description = $this->generateDescription($route['name'], $route['uri']);
                
                $permissions->push([
                    'module' => $module,
                    'name' => $permissionName,
                    'description' => $description,
                ]);

                // Generate menu items for index routes
                if (Str::endsWith($route['name'], '.index')) {
                    $title = $this->generateMenuTitle($route['name']);
                    $icon = $this->generateIcon($module);
                    
                    $menuItems->push([
                        'module' => Str::title($module),
                        'title' => $title,
                        'route_name' => $route['name'],
                        'icon' => $icon,
                        'permission_required' => $permissionName,
                    ]);
                }

                $this->line("  ✓ {$permissionName} - {$description}");
            }
        }

        if ($dryRun) {
            $this->info("\n🔍 DRY RUN - Nothing was created");
            $this->info("Would create {$permissions->count()} permissions");
            $this->info("Would create {$menuItems->count()} menu items");
            return;
        }

        // Generate seeder file
        $this->generateSeederFile($permissions, $menuItems);
        
        $this->info("\n✅ Seeder file generated successfully!");
        $this->info("Run: php artisan db:seed --class=GeneratedPermissionsSeeder");
    }

    private function generateDescription($routeName, $uri)
    {
        $parts = explode('.', $routeName);
        $action = end($parts);
        $resource = $parts[count($parts) - 2] ?? $parts[0];

        $actionMap = [
            'index' => 'Listar',
            'create' => 'Criar',
            'store' => 'Salvar',
            'show' => 'Visualizar',
            'edit' => 'Editar',
            'update' => 'Atualizar',
            'destroy' => 'Deletar',
        ];

        $actionText = $actionMap[$action] ?? Str::title($action);
        $resourceText = Str::plural($resource);

        return "{$actionText} {$resourceText}";
    }

    private function generateMenuTitle($routeName)
    {
        $parts = explode('.', $routeName);
        $resource = $parts[count($parts) - 2] ?? $parts[0];
        return Str::title(Str::plural($resource));
    }

    private function generateIcon($module)
    {
        $iconMap = [
            'admin' => 'settings',
            'users' => 'users',
            'roles' => 'shield',
            'permissions' => 'key',
            'menus' => 'menu',
            'settings' => 'cog',
            'api' => 'api',
            'dashboard' => 'dashboard',
        ];

        return $iconMap[$module] ?? 'circle';
    }

    private function generateSeederFile($permissions, $menuItems)
    {
        $permissionsArray = $permissions->groupBy('module')->map(function ($perms) {
            return $perms->map(function ($perm) {
                return "                ['name' => '{$perm['name']}', 'description' => '{$perm['description']}']";
            })->implode(",\n");
        });

        $menuItemsArray = $menuItems->map(function ($item) {
            return "        MenuItem::create([\n" .
                   "            'title' => '{$item['title']}',\n" .
                   "            'route_name' => '{$item['route_name']}',\n" .
                   "            'icon' => '{$item['icon']}',\n" .
                   "            'permission_required' => '{$item['permission_required']}',\n" .
                   "            'order_index' => 1,\n" .
                   "            'is_active' => true,\n" .
                   "            'module' => '{$item['module']}',\n" .
                   "        ]);";
        })->implode("\n\n");

        $seederContent = $this->getSeederTemplate($permissionsArray, $menuItemsArray);
        
        $seederPath = database_path('seeders/GeneratedPermissionsSeeder.php');
        file_put_contents($seederPath, $seederContent);
    }

    private function getSeederTemplate($permissionsArray, $menuItemsArray)
    {
        $permissions = $permissionsArray->map(function ($perms, $module) {
            return "            '{$module}' => [\n{$perms}\n            ]";
        })->implode(",\n");

        return "<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\MenuItem;

class GeneratedPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generated automatically from routes using: php artisan permissions:generate-from-routes
     */
    public function run(): void
    {
        // Permissions generated from routes
        \$permissions = [
{$permissions}
        ];

        // Create permissions
        foreach (\$permissions as \$module => \$modulePermissions) {
            foreach (\$modulePermissions as \$permission) {
                Permission::firstOrCreate(
                    ['name' => \$permission['name'], 'guard_name' => 'web'],
                    [
                        'module' => \$module,
                        'description' => \$permission['description'],
                    ]
                );
            }
        }

        // Create menu items
{$menuItemsArray}

        \$this->command->info('Generated permissions and menu items created successfully!');
    }
}";
    }
}
