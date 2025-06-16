<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class AdvancedPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Este seeder adiciona permissões mais granulares baseadas nas rotas específicas
     */
    public function run(): void
    {
        // Permissões específicas para operações CRUD detalhadas
        $advancedPermissions = [
            'users_advanced' => [
                ['name' => 'admin.users.bulk-actions', 'description' => 'Ações em massa para usuários'],
                ['name' => 'admin.users.export', 'description' => 'Exportar dados de usuários'],
                ['name' => 'admin.users.import', 'description' => 'Importar dados de usuários'],
                ['name' => 'admin.users.activity-log', 'description' => 'Ver log de atividades do usuário'],
            ],
            'roles_advanced' => [
                ['name' => 'admin.roles.clone', 'description' => 'Clonar roles'],
                ['name' => 'admin.roles.export', 'description' => 'Exportar configurações de roles'],
                ['name' => 'admin.roles.import', 'description' => 'Importar configurações de roles'],
            ],
            'permissions_advanced' => [
                ['name' => 'admin.permissions.auto-generate', 'description' => 'Gerar permissões automaticamente'],
                ['name' => 'admin.permissions.validate', 'description' => 'Validar estrutura de permissões'],
                ['name' => 'admin.permissions.cleanup', 'description' => 'Limpar permissões não utilizadas'],
            ],
            'menus_advanced' => [
                ['name' => 'admin.menus.import', 'description' => 'Importar estrutura de menus'],
                ['name' => 'admin.menus.export', 'description' => 'Exportar estrutura de menus'],
                ['name' => 'admin.menus.preview', 'description' => 'Pré-visualizar alterações de menu'],
                ['name' => 'admin.menus.backup', 'description' => 'Backup da estrutura de menus'],
                ['name' => 'admin.menus.restore', 'description' => 'Restaurar backup de menus'],
            ],
            'system_advanced' => [
                ['name' => 'system.cache.view', 'description' => 'Visualizar status do cache'],
                ['name' => 'system.cache.clear', 'description' => 'Limpar cache do sistema'],
                ['name' => 'system.logs.view', 'description' => 'Visualizar logs do sistema'],
                ['name' => 'system.logs.download', 'description' => 'Baixar logs do sistema'],
                ['name' => 'system.maintenance.toggle', 'description' => 'Ativar/Desativar modo manutenção'],
                ['name' => 'system.health.check', 'description' => 'Verificar saúde do sistema'],
            ],
            'api_advanced' => [
                ['name' => 'api.tokens.manage', 'description' => 'Gerenciar tokens de API'],
                ['name' => 'api.rate-limit.view', 'description' => 'Visualizar limites de rate limiting'],
                ['name' => 'api.logs.view', 'description' => 'Visualizar logs da API'],
            ],
        ];

        // Criar as permissions avançadas
        foreach ($advancedPermissions as $module => $modulePermissions) {
            foreach ($modulePermissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission['name'], 'guard_name' => 'web'],
                    [
                        'module' => str_replace('_advanced', '', $module),
                        'description' => $permission['description'],
                    ]
                );
            }
        }

        // Atribuir permissões avançadas apenas ao Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo(Permission::all());
        }

        // Criar uma role específica para System Administrator
        $systemAdmin = Role::firstOrCreate(
            ['name' => 'System Admin', 'guard_name' => 'web'],
            ['description' => 'Administrador de sistema com acesso a funcionalidades avançadas']
        );

        // Dar permissões de sistema ao System Admin
        $systemAdmin->givePermissionTo([
            'admin.access',
            'dashboard',
            'system.cache.view', 'system.cache.clear',
            'system.logs.view', 'system.logs.download',
            'system.maintenance.toggle', 'system.health.check',
            'api.tokens.manage', 'api.rate-limit.view', 'api.logs.view',
            'admin.menus.backup', 'admin.menus.restore',
            'admin.permissions.cleanup', 'admin.permissions.validate',
        ]);

        $this->command->info('Permissões avançadas criadas com sucesso!');
        $this->command->info('Role System Admin criada!');
        $this->command->info('Total de permissions no sistema: ' . Permission::count());
    }
}
