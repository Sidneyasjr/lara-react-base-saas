<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class MenuItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpa a tabela
        DB::table('menu_items')->truncate();

        // Menu Principal - Dashboard
        MenuItem::create([
            'title' => 'Dashboard',
            'route_name' => 'dashboard',
            'icon' => 'dashboard',
            'permission_required' => null, // Todos podem acessar
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Principal',
        ]);

        // Menu de Usuários - agora itens diretos
        MenuItem::create([
            'title' => 'Lista de Usuários',
            'route_name' => 'users.index',
            'icon' => 'users',
            'permission_required' => 'users.view',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Usuários',
        ]);

        MenuItem::create([
            'title' => 'Criar Usuário',
            'route_name' => 'users.create',
            'icon' => 'user',
            'permission_required' => 'users.create',
            'order_index' => 2,
            'is_active' => true,
            'module' => 'Usuários',
        ]);

        // Menu de Permissões e Roles - itens diretos
        MenuItem::create([
            'title' => 'Permissões',
            'route_name' => 'permissions.index',
            'icon' => 'key',
            'permission_required' => 'permissions.view',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Segurança',
        ]);

        MenuItem::create([
            'title' => 'Roles',
            'route_name' => 'roles.index',
            'icon' => 'lock',
            'permission_required' => 'roles.view',
            'order_index' => 2,
            'is_active' => true,
            'module' => 'Segurança',
        ]);

        // Menu de Configurações - itens diretos
        MenuItem::create([
            'title' => 'Configurações Gerais',
            'route_name' => 'settings.general',
            'icon' => 'settings',
            'permission_required' => 'settings.general',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Configurações',
        ]);

        MenuItem::create([
            'title' => 'Gerenciar Menus',
            'route_name' => 'settings.menus',
            'icon' => 'menu',
            'permission_required' => 'settings.menus',
            'order_index' => 2,
            'is_active' => true,
            'module' => 'Configurações',
        ]);

        // Menu de Relatórios - itens diretos
        MenuItem::create([
            'title' => 'Relatório de Usuários',
            'route_name' => 'reports.users',
            'icon' => 'chart',
            'permission_required' => 'reports.users',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Relatórios',
        ]);

        MenuItem::create([
            'title' => 'Relatório de Permissões',
            'route_name' => 'reports.permissions',
            'icon' => 'chart',
            'permission_required' => 'reports.permissions',
            'order_index' => 2,
            'is_active' => true,
            'module' => 'Relatórios',
        ]);

        // Menu do Perfil do Usuário
        MenuItem::create([
            'title' => 'Meu Perfil',
            'route_name' => 'profile.edit',
            'icon' => 'user',
            'permission_required' => null, // Todos podem acessar
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Perfil',
        ]);

        $this->command->info('Menu items criados com sucesso!');
    }
}
