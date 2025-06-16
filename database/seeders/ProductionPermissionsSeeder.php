<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Hash;

class ProductionPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds for production environment.
     * Este seeder é otimizado para uso em produção com permissões organizadas
     */
    public function run(): void
    {
        $this->command->info('🚀 Setting up production permissions...');

        // Limpar cache de permissions
        app()['cache']->forget('spatie.permission.cache');

        // Criar permissões organizadas por módulo
        $this->createPermissions();
        
        // Criar roles com hierarquia clara
        $this->createRoles();
        
        // Atribuir permissões aos roles
        $this->assignPermissionsToRoles();
        
        // Criar usuário administrador padrão se não existir
        $this->createDefaultAdmin();
        
        // Criar estrutura de menu otimizada
        $this->createMenuStructure();

        $this->command->info('✅ Production permissions setup completed!');
        $this->displaySummary();
    }

    private function createPermissions(): void
    {
        $this->command->info('📝 Creating permissions...');

        $permissions = [
            // Administração Geral
            'admin' => [
                ['name' => 'admin.access', 'description' => 'Acessar painel administrativo'],
                ['name' => 'admin.dashboard', 'description' => 'Visualizar dashboard administrativo'],
            ],
            
            // Gestão de Usuários
            'users' => [
                ['name' => 'users.index', 'description' => 'Listar usuários'],
                ['name' => 'users.show', 'description' => 'Visualizar usuário'],
                ['name' => 'users.create', 'description' => 'Criar usuários'],
                ['name' => 'users.edit', 'description' => 'Editar usuários'],
                ['name' => 'users.delete', 'description' => 'Deletar usuários'],
                ['name' => 'users.roles', 'description' => 'Gerenciar roles de usuários'],
            ],
            
            // Gestão de Roles
            'roles' => [
                ['name' => 'roles.index', 'description' => 'Listar roles'],
                ['name' => 'roles.show', 'description' => 'Visualizar role'],
                ['name' => 'roles.create', 'description' => 'Criar roles'],
                ['name' => 'roles.edit', 'description' => 'Editar roles'],
                ['name' => 'roles.delete', 'description' => 'Deletar roles'],
            ],
            
            // Gestão de Permissões
            'permissions' => [
                ['name' => 'permissions.index', 'description' => 'Listar permissões'],
                ['name' => 'permissions.show', 'description' => 'Visualizar permissão'],
                ['name' => 'permissions.create', 'description' => 'Criar permissões'],
                ['name' => 'permissions.edit', 'description' => 'Editar permissões'],
                ['name' => 'permissions.delete', 'description' => 'Deletar permissões'],
            ],
            
            // Configurações do Sistema
            'settings' => [
                ['name' => 'settings.general', 'description' => 'Configurações gerais'],
                ['name' => 'settings.security', 'description' => 'Configurações de segurança'],
                ['name' => 'settings.appearance', 'description' => 'Configurações de aparência'],
                ['name' => 'settings.advanced', 'description' => 'Configurações avançadas'],
            ],
            
            // Gestão de Menus
            'menus' => [
                ['name' => 'menus.index', 'description' => 'Listar menus'],
                ['name' => 'menus.create', 'description' => 'Criar menus'],
                ['name' => 'menus.edit', 'description' => 'Editar menus'],
                ['name' => 'menus.delete', 'description' => 'Deletar menus'],
                ['name' => 'menus.reorder', 'description' => 'Reordenar menus'],
            ],
            
            // Relatórios
            'reports' => [
                ['name' => 'reports.users', 'description' => 'Relatórios de usuários'],
                ['name' => 'reports.permissions', 'description' => 'Relatórios de permissões'],
                ['name' => 'reports.activity', 'description' => 'Relatórios de atividade'],
                ['name' => 'reports.export', 'description' => 'Exportar relatórios'],
            ],
            
            // Perfil Pessoal
            'profile' => [
                ['name' => 'profile.view', 'description' => 'Visualizar próprio perfil'],
                ['name' => 'profile.edit', 'description' => 'Editar próprio perfil'],
                ['name' => 'profile.password', 'description' => 'Alterar própria senha'],
            ],
        ];

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
    }

    private function createRoles(): void
    {
        $this->command->info('👥 Creating roles...');

        $roles = [
            [
                'name' => 'Super Administrador',
                'description' => 'Acesso completo ao sistema - use com extremo cuidado',
            ],
            [
                'name' => 'Administrador',
                'description' => 'Administrador geral com acesso a gestão de usuários e configurações',
            ],
            [
                'name' => 'Gerente',
                'description' => 'Gerente com acesso limitado a relatórios e visualizações',
            ],
            [
                'name' => 'Usuário',
                'description' => 'Usuário padrão com acesso básico ao sistema',
            ],
            [
                'name' => 'Visualizador',
                'description' => 'Acesso apenas para visualização, sem edições',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => 'web'],
                ['description' => $roleData['description']]
            );
        }
    }

    private function assignPermissionsToRoles(): void
    {
        $this->command->info('🔗 Assigning permissions to roles...');

        // Super Administrador - Todas as permissões
        $superAdmin = Role::where('name', 'Super Administrador')->first();
        $superAdmin->givePermissionTo(Permission::all());

        // Administrador - Gestão completa exceto configurações avançadas
        $admin = Role::where('name', 'Administrador')->first();
        $admin->givePermissionTo([
            'admin.access', 'admin.dashboard',
            'users.index', 'users.show', 'users.create', 'users.edit', 'users.delete', 'users.roles',
            'roles.index', 'roles.show', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.index', 'permissions.show', 'permissions.create', 'permissions.edit', 'permissions.delete',
            'settings.general', 'settings.security', 'settings.appearance',
            'menus.index', 'menus.create', 'menus.edit', 'menus.delete', 'menus.reorder',
            'reports.users', 'reports.permissions', 'reports.activity', 'reports.export',
            'profile.view', 'profile.edit', 'profile.password',
        ]);

        // Gerente - Acesso de visualização e relatórios
        $manager = Role::where('name', 'Gerente')->first();
        $manager->givePermissionTo([
            'admin.access', 'admin.dashboard',
            'users.index', 'users.show',
            'roles.index', 'roles.show',
            'permissions.index', 'permissions.show',
            'settings.general',
            'reports.users', 'reports.permissions', 'reports.activity', 'reports.export',
            'profile.view', 'profile.edit', 'profile.password',
        ]);

        // Usuário - Acesso básico
        $user = Role::where('name', 'Usuário')->first();
        $user->givePermissionTo([
            'profile.view', 'profile.edit', 'profile.password',
        ]);

        // Visualizador - Apenas visualização
        $viewer = Role::where('name', 'Visualizador')->first();
        $viewer->givePermissionTo([
            'admin.access', 'admin.dashboard',
            'users.index', 'users.show',
            'roles.index', 'roles.show',
            'permissions.index', 'permissions.show',
            'reports.users', 'reports.permissions', 'reports.activity',
            'profile.view',
        ]);
    }

    private function createDefaultAdmin(): void
    {
        $this->command->info('👤 Creating default admin user...');

        $admin = User::firstOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name' => 'Administrador do Sistema',
                'password' => Hash::make('admin123!@#'),
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->hasRole('Super Administrador')) {
            $admin->assignRole('Super Administrador');
            $this->command->warn('⚠️ Default admin created with credentials:');
            $this->command->warn('Email: admin@sistema.com');
            $this->command->warn('Password: admin123!@#');
            $this->command->warn('🔒 CHANGE THIS PASSWORD IMMEDIATELY IN PRODUCTION!');
        }
    }

    private function createMenuStructure(): void
    {
        $this->command->info('📱 Creating menu structure...');

        // Limpar menus existentes
        MenuItem::truncate();

        $menus = [
            [
                'title' => 'Dashboard',
                'route_name' => 'dashboard',
                'icon' => 'dashboard',
                'permission_required' => 'admin.dashboard',
                'order_index' => 1,
                'module' => 'Principal',
            ],
            [
                'title' => 'Gestão de Usuários',
                'route_name' => 'admin.users.index',
                'icon' => 'users',
                'permission_required' => 'users.index',
                'order_index' => 2,
                'module' => 'Administração',
            ],
            [
                'title' => 'Roles e Permissões',
                'route_name' => 'admin.roles.index',
                'icon' => 'shield',
                'permission_required' => 'roles.index',
                'order_index' => 3,
                'module' => 'Administração',
            ],
            [
                'title' => 'Configurações',
                'route_name' => 'settings.general',
                'icon' => 'settings',
                'permission_required' => 'settings.general',
                'order_index' => 4,
                'module' => 'Sistema',
            ],
            [
                'title' => 'Relatórios',
                'route_name' => 'reports.users',
                'icon' => 'chart-bar',
                'permission_required' => 'reports.users',
                'order_index' => 5,
                'module' => 'Relatórios',
            ],
        ];

        foreach ($menus as $menuData) {
            MenuItem::create(array_merge($menuData, ['is_active' => true]));
        }
    }

    private function displaySummary(): void
    {
        $this->command->info("\n📊 SUMMARY:");
        $this->command->line("✓ Permissions: " . Permission::count());
        $this->command->line("✓ Roles: " . Role::count());
        $this->command->line("✓ Users: " . User::count());
        $this->command->line("✓ Menu Items: " . MenuItem::count());
        
        $this->command->info("\n🔐 SECURITY REMINDERS:");
        $this->command->warn("• Change default admin password immediately");
        $this->command->warn("• Review permissions before going to production");
        $this->command->warn("• Implement proper role assignment workflow");
        $this->command->warn("• Monitor permission usage with audit logs");
    }
}
