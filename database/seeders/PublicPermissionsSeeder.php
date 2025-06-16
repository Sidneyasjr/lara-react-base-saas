<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PublicPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permissões públicas que todos os usuários devem ter
        $publicPermissions = [
            // Autenticação - todos precisam
            ['name' => 'auth.login', 'description' => 'Fazer login', 'module' => 'auth'],
            ['name' => 'auth.register', 'description' => 'Registrar-se', 'module' => 'auth'],
            ['name' => 'auth.logout', 'description' => 'Fazer logout', 'module' => 'auth'],
            ['name' => 'auth.password.request', 'description' => 'Solicitar redefinição de senha', 'module' => 'auth'],
            ['name' => 'auth.password.email', 'description' => 'Enviar email de redefinição', 'module' => 'auth'],
            ['name' => 'auth.password.reset', 'description' => 'Redefinir senha', 'module' => 'auth'],
            ['name' => 'auth.password.store', 'description' => 'Salvar nova senha', 'module' => 'auth'],
            ['name' => 'auth.password.confirm', 'description' => 'Confirmar senha', 'module' => 'auth'],
            ['name' => 'auth.verification.notice', 'description' => 'Notificação de verificação', 'module' => 'auth'],
            ['name' => 'auth.verification.verify', 'description' => 'Verificar email', 'module' => 'auth'],
            ['name' => 'auth.verification.send', 'description' => 'Enviar verificação', 'module' => 'auth'],
            
            // Páginas básicas
            ['name' => 'home', 'description' => 'Acessar página inicial', 'module' => 'general'],
            ['name' => 'dashboard', 'description' => 'Acessar dashboard', 'module' => 'general'],
            
            // API básica
            ['name' => 'api.menu', 'description' => 'API de menu', 'module' => 'api'],
            ['name' => 'api.menu.breadcrumb', 'description' => 'API de breadcrumb', 'module' => 'api'],
        ];

        foreach ($publicPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                [
                    'description' => $permission['description'],
                    'module' => $permission['module'],
                ]
            );
        }

        $this->command->info('Permissões públicas criadas com sucesso!');
        $this->command->info('Total de permissões públicas: ' . count($publicPermissions));
    }
}
