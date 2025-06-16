<?php

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
        $permissions = [
            'admin' => [
                ['name' => 'admin.menus.clear-cache', 'description' => 'Clear-Cache menuses'],
                ['name' => 'admin.menus.create', 'description' => 'Criar menuses'],
                ['name' => 'admin.menus.destroy', 'description' => 'Deletar menuses'],
                ['name' => 'admin.menus.edit', 'description' => 'Editar menuses'],
                ['name' => 'admin.menus.index', 'description' => 'Listar menuses'],
                ['name' => 'admin.menus.reorder', 'description' => 'Reorder menuses'],
                ['name' => 'admin.menus.search', 'description' => 'Search menuses'],
                ['name' => 'admin.menus.store', 'description' => 'Salvar menuses'],
                ['name' => 'admin.menus.toggle', 'description' => 'Toggle menuses'],
                ['name' => 'admin.menus.update', 'description' => 'Atualizar menuses'],
                ['name' => 'admin.permissions.api', 'description' => 'Api permissions'],
                ['name' => 'admin.permissions.bulk-assign-role', 'description' => 'Bulk-Assign-Role permissions'],
                ['name' => 'admin.permissions.bulk-remove-role', 'description' => 'Bulk-Remove-Role permissions'],
                ['name' => 'admin.permissions.create', 'description' => 'Criar permissions'],
                ['name' => 'admin.permissions.destroy', 'description' => 'Deletar permissions'],
                ['name' => 'admin.permissions.edit', 'description' => 'Editar permissions'],
                ['name' => 'admin.permissions.index', 'description' => 'Listar permissions'],
                ['name' => 'admin.permissions.show', 'description' => 'Visualizar permissions'],
                ['name' => 'admin.permissions.store', 'description' => 'Salvar permissions'],
                ['name' => 'admin.permissions.update', 'description' => 'Atualizar permissions'],
                ['name' => 'admin.roles.assign-user', 'description' => 'Assign-User roles'],
                ['name' => 'admin.roles.create', 'description' => 'Criar roles'],
                ['name' => 'admin.roles.destroy', 'description' => 'Deletar roles'],
                ['name' => 'admin.roles.edit', 'description' => 'Editar roles'],
                ['name' => 'admin.roles.index', 'description' => 'Listar roles'],
                ['name' => 'admin.roles.permissions', 'description' => 'Permissions roles'],
                ['name' => 'admin.roles.remove-user', 'description' => 'Remove-User roles'],
                ['name' => 'admin.roles.show', 'description' => 'Visualizar roles'],
                ['name' => 'admin.roles.store', 'description' => 'Salvar roles'],
                ['name' => 'admin.roles.update', 'description' => 'Atualizar roles'],
                ['name' => 'admin.users.create', 'description' => 'Criar users'],
                ['name' => 'admin.users.destroy', 'description' => 'Deletar users'],
                ['name' => 'admin.users.edit', 'description' => 'Editar users'],
                ['name' => 'admin.users.index', 'description' => 'Listar users'],
                ['name' => 'admin.users.permissions', 'description' => 'Permissions users'],
                ['name' => 'admin.users.roles.update', 'description' => 'Atualizar roles'],
                ['name' => 'admin.users.show', 'description' => 'Visualizar users'],
                ['name' => 'admin.users.store', 'description' => 'Salvar users'],
                ['name' => 'admin.users.update', 'description' => 'Atualizar users']
            ],
            'api' => [
                ['name' => 'api.menu', 'description' => 'Menu apis'],
                ['name' => 'api.menu.breadcrumb', 'description' => 'Breadcrumb menus']
            ],
            'appearance' => [
                ['name' => 'appearance', 'description' => 'Appearance appearances']
            ],
            'dashboard' => [
                ['name' => 'dashboard', 'description' => 'Dashboard dashboards']
            ],
            'home' => [
                ['name' => 'home', 'description' => 'Home homes']
            ],
            'login' => [
                ['name' => 'login', 'description' => 'Login logins']
            ],
            'logout' => [
                ['name' => 'logout', 'description' => 'Logout logouts']
            ],
            'profile' => [
                ['name' => 'profile.destroy', 'description' => 'Deletar profiles'],
                ['name' => 'profile.edit', 'description' => 'Editar profiles'],
                ['name' => 'profile.update', 'description' => 'Atualizar profiles']
            ],
            'register' => [
                ['name' => 'register', 'description' => 'Register registers']
            ],
            'toast-demo' => [
                ['name' => 'toast-demo', 'description' => 'Toast-Demo toast-demos']
            ]
        ];

        // Create permissions
        foreach ($permissions as $module => $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission['name'], 'guard_name' => 'web'],
                    [
                        'module' => $module,
                        'description' => $permission['description'],
                    ]
                );
            }
        }

        // Create menu items
        MenuItem::create([
            'title' => 'Menuses',
            'route_name' => 'admin.menus.index',
            'icon' => 'settings',
            'permission_required' => 'admin.menus.index',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Admin',
        ]);

        MenuItem::create([
            'title' => 'Permissions',
            'route_name' => 'admin.permissions.index',
            'icon' => 'settings',
            'permission_required' => 'admin.permissions.index',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Admin',
        ]);

        MenuItem::create([
            'title' => 'Roles',
            'route_name' => 'admin.roles.index',
            'icon' => 'settings',
            'permission_required' => 'admin.roles.index',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Admin',
        ]);

        MenuItem::create([
            'title' => 'Users',
            'route_name' => 'admin.users.index',
            'icon' => 'settings',
            'permission_required' => 'admin.users.index',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Admin',
        ]);

        $this->command->info('Generated permissions and menu items created successfully!');
    }
}