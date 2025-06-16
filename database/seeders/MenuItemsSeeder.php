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
            'permission_required' => 'dashboard',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Principal',
        ]);

        // Home
        MenuItem::create([
            'title' => 'Início',
            'route_name' => 'home',
            'icon' => 'home',
            'permission_required' => 'home',
            'order_index' => 2,
            'is_active' => true,
            'module' => 'Principal',
        ]);

        // --- ADMINISTRAÇÃO ---
        
        // Gestão de Usuários
        MenuItem::create([
            'title' => 'Usuários',
            'route_name' => 'admin.users.index',
            'icon' => 'users',
            'permission_required' => 'admin.users.index',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Administração',
        ]);

        // Gestão de Roles
        MenuItem::create([
            'title' => 'Roles',
            'route_name' => 'admin.roles.index',
            'icon' => 'shield',
            'permission_required' => 'admin.roles.index',
            'order_index' => 2,
            'is_active' => true,
            'module' => 'Administração',
        ]);

        // Gestão de Permissões
        MenuItem::create([
            'title' => 'Permissões',
            'route_name' => 'admin.permissions.index',
            'icon' => 'key',
            'permission_required' => 'admin.permissions.index',
            'order_index' => 3,
            'is_active' => true,
            'module' => 'Administração',
        ]);

        // Gestão de Menus
        MenuItem::create([
            'title' => 'Menus',
            'route_name' => 'admin.menus.index',
            'icon' => 'menu',
            'permission_required' => 'admin.menus.index',
            'order_index' => 4,
            'is_active' => true,
            'module' => 'Administração',
        ]);

        // --- CONFIGURAÇÕES ---
        
        // Aparência
        MenuItem::create([
            'title' => 'Aparência',
            'route_name' => 'appearance',
            'icon' => 'palette',
            'permission_required' => 'settings.appearance',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Configurações',
        ]);

        // Configurações do Perfil
        MenuItem::create([
            'title' => 'Meu Perfil',
            'route_name' => 'profile.edit',
            'icon' => 'user',
            'permission_required' => 'settings.profile.edit',
            'order_index' => 2,
            'is_active' => true,
            'module' => 'Configurações',
        ]);

        // Configurações de Senha
        MenuItem::create([
            'title' => 'Alterar Senha',
            'route_name' => 'password.edit',
            'icon' => 'lock',
            'permission_required' => 'settings.password.edit',
            'order_index' => 3,
            'is_active' => true,
            'module' => 'Configurações',
        ]);

        // --- DEMOS E TESTES ---
        
        // Demo de Toast
        MenuItem::create([
            'title' => 'Demo Toast',
            'route_name' => 'toast-demo',
            'icon' => 'bell',
            'permission_required' => 'toast-demo',
            'order_index' => 1,
            'is_active' => true,
            'module' => 'Demos',
        ]);

        $this->command->info('Menu items criados com sucesso baseados nas rotas existentes!');
        $this->command->info('Total de items criados: ' . MenuItem::count());
    }
}
