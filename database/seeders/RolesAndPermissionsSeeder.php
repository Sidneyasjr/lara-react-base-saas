<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar cache de permissions
        app()['cache']->forget('spatie.permission.cache');

        // Criar Permissions por módulo
        $permissions = [
            'admin' => [
                ['name' => 'access admin', 'description' => 'Acessar área administrativa'],
            ],
            'users' => [
                ['name' => 'view users', 'description' => 'Visualizar usuários'],
                ['name' => 'create users', 'description' => 'Criar usuários'],
                ['name' => 'edit users', 'description' => 'Editar usuários'],
                ['name' => 'delete users', 'description' => 'Deletar usuários'],
                ['name' => 'manage users', 'description' => 'Gerenciar usuários (todas as operações)'],
            ],
            'roles' => [
                ['name' => 'view roles', 'description' => 'Visualizar roles'],
                ['name' => 'create roles', 'description' => 'Criar roles'],
                ['name' => 'edit roles', 'description' => 'Editar roles'],
                ['name' => 'delete roles', 'description' => 'Deletar roles'],
                ['name' => 'manage roles', 'description' => 'Gerenciar roles (todas as operações)'],
            ],
            'permissions' => [
                ['name' => 'view permissions', 'description' => 'Visualizar permissões'],
                ['name' => 'create permissions', 'description' => 'Criar permissões'],
                ['name' => 'edit permissions', 'description' => 'Editar permissões'],
                ['name' => 'delete permissions', 'description' => 'Deletar permissões'],
                ['name' => 'manage permissions', 'description' => 'Gerenciar permissões (todas as operações)'],
            ],
            'settings' => [
                ['name' => 'settings.view', 'description' => 'Visualizar configurações'],
                ['name' => 'settings.edit', 'description' => 'Editar configurações'],
                ['name' => 'settings.general', 'description' => 'Configurações gerais'],
                ['name' => 'settings.menus', 'description' => 'Gerenciar menus do sistema'],
            ],
            'reports' => [
                ['name' => 'reports.view', 'description' => 'Visualizar relatórios'],
                ['name' => 'reports.users', 'description' => 'Relatório de usuários'],
                ['name' => 'reports.permissions', 'description' => 'Relatório de permissões'],
            ],
            'analytics' => [
                ['name' => 'analytics.view', 'description' => 'Visualizar analytics'],
            ],
            'audit' => [
                ['name' => 'audit.view', 'description' => 'Visualizar logs de auditoria'],
            ],
            'menu' => [
                ['name' => 'menu.view', 'description' => 'Visualizar menus'],
                ['name' => 'menu.edit', 'description' => 'Personalizar menus'],
            ],
        ];

        // Criar as permissions
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

        // Criar Roles
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['description' => 'Acesso total ao sistema com todas as permissões']
        );

        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['description' => 'Administrador com acesso à gestão de usuários e configurações']
        );

        $manager = Role::firstOrCreate(
            ['name' => 'Manager', 'guard_name' => 'web'],
            ['description' => 'Gerente com acesso limitado de gestão']
        );

        $user = Role::firstOrCreate(
            ['name' => 'User', 'guard_name' => 'web'],
            ['description' => 'Usuário básico com acesso limitado']
        );

        // Atribuir permissions aos roles
        
        // Super Admin - todas as permissions
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - gerencia usuários, roles e configurações
        $admin->givePermissionTo([
            'access admin',
            'view users', 'create users', 'edit users', 'delete users', 'manage users',
            'view roles', 'create roles', 'edit roles', 'delete roles', 'manage roles',
            'view permissions', 'create permissions', 'edit permissions', 'delete permissions', 'manage permissions',
            'settings.view', 'settings.edit', 'settings.general', 'settings.menus',
            'reports.view', 'reports.users', 'reports.permissions',
            'analytics.view',
            'menu.view', 'menu.edit',
        ]);

        // Manager - acesso limitado de gestão
        $manager->givePermissionTo([
            'access admin',
            'view users', 'create users', 'edit users',
            'view roles',
            'view permissions',
            'settings.view',
            'analytics.view',
            'menu.view',
        ]);

        // User - acesso básico
        $user->givePermissionTo([
            'menu.view',
        ]);

        $this->command->info('Roles e Permissions criados com sucesso!');
        $this->command->info('Roles criados: Super Admin, Admin, Manager, User');
        $this->command->info('Total de permissions: ' . Permission::count());
    }
}
