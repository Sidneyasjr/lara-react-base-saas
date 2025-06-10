<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primeiro criar roles e permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // Criar itens de menu após as permissões
        $this->call(MenuItemsSeeder::class);

        // Criar um usuário específico para teste
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        // Criar um administrador
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );

        // Criar usuários de teste apenas se não existirem
        if (User::count() <= 2) {
            User::factory(15)->create();
        }

        // Atribuir roles aos usuários criados
        if (!$adminUser->hasRole('Super Admin')) {
            $adminUser->assignRole('Super Admin');
        }
        
        if (!$testUser->hasRole('User')) {
            $testUser->assignRole('User');
        }
        
        // Atribuir roles aleatórios aos outros usuários
        $users = User::whereNotIn('email', ['test@example.com', 'admin@example.com'])
                    ->doesntHave('roles')
                    ->get();
        $roles = ['Admin', 'Manager', 'User'];
        
        foreach ($users as $user) {
            $user->assignRole($roles[array_rand($roles)]);
        }
    }
}
