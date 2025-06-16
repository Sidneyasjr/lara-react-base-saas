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

        // Criar Permissions baseadas nas rotas existentes
        $permissions = [
            'admin' => [
                ['name' => 'admin.access', 'description' => 'Acessar área administrativa'],
            ],
            'users' => [
                ['name' => 'admin.users.index', 'description' => 'Listar usuários'],
                ['name' => 'admin.users.create', 'description' => 'Criar usuários'],
                ['name' => 'admin.users.store', 'description' => 'Salvar usuários'],
                ['name' => 'admin.users.show', 'description' => 'Visualizar usuário'],
                ['name' => 'admin.users.edit', 'description' => 'Editar usuário'],
                ['name' => 'admin.users.update', 'description' => 'Atualizar usuário'],
                ['name' => 'admin.users.destroy', 'description' => 'Deletar usuário'],
                ['name' => 'admin.users.permissions', 'description' => 'Gerenciar permissões do usuário'],
                ['name' => 'admin.users.roles.update', 'description' => 'Atualizar roles do usuário'],
            ],
            'roles' => [
                ['name' => 'admin.roles.index', 'description' => 'Listar roles'],
                ['name' => 'admin.roles.create', 'description' => 'Criar roles'],
                ['name' => 'admin.roles.store', 'description' => 'Salvar roles'],
                ['name' => 'admin.roles.show', 'description' => 'Visualizar role'],
                ['name' => 'admin.roles.edit', 'description' => 'Editar role'],
                ['name' => 'admin.roles.update', 'description' => 'Atualizar role'],
                ['name' => 'admin.roles.destroy', 'description' => 'Deletar role'],
                ['name' => 'admin.roles.permissions', 'description' => 'Gerenciar permissões de roles'],
                ['name' => 'admin.roles.assign-user', 'description' => 'Atribuir role a usuário'],
                ['name' => 'admin.roles.remove-user', 'description' => 'Remover role de usuário'],
            ],
            'permissions' => [
                ['name' => 'admin.permissions.index', 'description' => 'Listar permissões'],
                ['name' => 'admin.permissions.create', 'description' => 'Criar permissões'],
                ['name' => 'admin.permissions.store', 'description' => 'Salvar permissões'],
                ['name' => 'admin.permissions.show', 'description' => 'Visualizar permissão'],
                ['name' => 'admin.permissions.edit', 'description' => 'Editar permissão'],
                ['name' => 'admin.permissions.update', 'description' => 'Atualizar permissão'],
                ['name' => 'admin.permissions.destroy', 'description' => 'Deletar permissão'],
                ['name' => 'admin.permissions.api', 'description' => 'API de permissões'],
                ['name' => 'admin.permissions.bulk-assign-role', 'description' => 'Atribuição em massa de roles'],
                ['name' => 'admin.permissions.bulk-remove-role', 'description' => 'Remoção em massa de roles'],
            ],
            'menus' => [
                ['name' => 'admin.menus.index', 'description' => 'Listar menus'],
                ['name' => 'admin.menus.create', 'description' => 'Criar menus'],
                ['name' => 'admin.menus.store', 'description' => 'Salvar menus'],
                ['name' => 'admin.menus.edit', 'description' => 'Editar menu'],
                ['name' => 'admin.menus.update', 'description' => 'Atualizar menu'],
                ['name' => 'admin.menus.destroy', 'description' => 'Deletar menu'],
                ['name' => 'admin.menus.search', 'description' => 'Buscar menus'],
                ['name' => 'admin.menus.reorder', 'description' => 'Reordenar menus'],
                ['name' => 'admin.menus.toggle', 'description' => 'Ativar/Desativar menu'],
                ['name' => 'admin.menus.clear-cache', 'description' => 'Limpar cache de menus'],
            ],
            'settings' => [
                ['name' => 'settings.appearance', 'description' => 'Configurações de aparência'],
                ['name' => 'settings.profile.edit', 'description' => 'Editar perfil'],
                ['name' => 'settings.profile.update', 'description' => 'Atualizar perfil'],
                ['name' => 'settings.profile.destroy', 'description' => 'Deletar perfil'],
                ['name' => 'settings.password.edit', 'description' => 'Editar senha'],
                ['name' => 'settings.password.update', 'description' => 'Atualizar senha'],
            ],
            'api' => [
                ['name' => 'api.menu', 'description' => 'API de menu'],
                ['name' => 'api.menu.breadcrumb', 'description' => 'API de breadcrumb'],
            ],
            'auth' => [
                ['name' => 'auth.login', 'description' => 'Fazer login'],
                ['name' => 'auth.register', 'description' => 'Registrar-se'],
                ['name' => 'auth.logout', 'description' => 'Fazer logout'],
                ['name' => 'auth.password.request', 'description' => 'Solicitar redefinição de senha'],
                ['name' => 'auth.password.email', 'description' => 'Enviar email de redefinição'],
                ['name' => 'auth.password.reset', 'description' => 'Redefinir senha'],
                ['name' => 'auth.password.store', 'description' => 'Salvar nova senha'],
                ['name' => 'auth.password.confirm', 'description' => 'Confirmar senha'],
                ['name' => 'auth.verification.notice', 'description' => 'Notificação de verificação'],
                ['name' => 'auth.verification.verify', 'description' => 'Verificar email'],
                ['name' => 'auth.verification.send', 'description' => 'Enviar verificação'],
            ],
            'general' => [
                ['name' => 'dashboard', 'description' => 'Acessar dashboard'],
                ['name' => 'home', 'description' => 'Acessar página inicial'],
                ['name' => 'toast-demo', 'description' => 'Demo de notificações'],
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

        // Admin - gerencia usuários, roles, permissões e menus
        $admin->givePermissionTo([
            'admin.access',
            // Usuários
            'admin.users.index', 'admin.users.create', 'admin.users.store', 'admin.users.show',
            'admin.users.edit', 'admin.users.update', 'admin.users.destroy', 'admin.users.permissions',
            'admin.users.roles.update',
            // Roles
            'admin.roles.index', 'admin.roles.create', 'admin.roles.store', 'admin.roles.show',
            'admin.roles.edit', 'admin.roles.update', 'admin.roles.destroy', 'admin.roles.permissions',
            'admin.roles.assign-user', 'admin.roles.remove-user',
            // Permissões
            'admin.permissions.index', 'admin.permissions.create', 'admin.permissions.store',
            'admin.permissions.show', 'admin.permissions.edit', 'admin.permissions.update',
            'admin.permissions.destroy', 'admin.permissions.api', 'admin.permissions.bulk-assign-role',
            'admin.permissions.bulk-remove-role',
            // Menus
            'admin.menus.index', 'admin.menus.create', 'admin.menus.store', 'admin.menus.edit',
            'admin.menus.update', 'admin.menus.destroy', 'admin.menus.search', 'admin.menus.reorder',
            'admin.menus.toggle', 'admin.menus.clear-cache',
            // Settings
            'settings.appearance', 'settings.profile.edit', 'settings.profile.update',
            'settings.password.edit', 'settings.password.update',
            // API
            'api.menu', 'api.menu.breadcrumb',
            // Dashboard
            'dashboard',
        ]);

        // Manager - acesso limitado de gestão
        $manager->givePermissionTo([
            'admin.access',
            // Usuários (apenas visualizar e editar)
            'admin.users.index', 'admin.users.show', 'admin.users.edit', 'admin.users.update',
            // Roles (apenas visualizar)
            'admin.roles.index', 'admin.roles.show',
            // Permissões (apenas visualizar)
            'admin.permissions.index', 'admin.permissions.show',
            // Menus (apenas visualizar)
            'admin.menus.index',
            // Settings pessoais
            'settings.profile.edit', 'settings.profile.update', 'settings.password.edit', 'settings.password.update',
            // API
            'api.menu', 'api.menu.breadcrumb',
            // Dashboard
            'dashboard',
        ]);

        // User - acesso básico
        $user->givePermissionTo([
            // Settings pessoais
            'settings.profile.edit', 'settings.profile.update', 'settings.password.edit', 'settings.password.update',
            // API
            'api.menu', 'api.menu.breadcrumb',
            // Dashboard
            'dashboard', 'home',
            // Auth (todos precisam)
            'auth.login', 'auth.logout', 'auth.password.request', 'auth.password.email',
            'auth.password.reset', 'auth.password.store', 'auth.password.confirm',
            'auth.verification.notice', 'auth.verification.verify', 'auth.verification.send',
        ]);

        $this->command->info('Roles e Permissions criados com sucesso!');
        $this->command->info('Roles criados: Super Admin, Admin, Manager, User');
        $this->command->info('Total de permissions: ' . Permission::count());
    }
}
