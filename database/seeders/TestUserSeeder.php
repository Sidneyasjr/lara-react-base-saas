<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cria usuário admin se não existir
        $user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Teste',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Dá todas as permissões se o usuário não tem
        if (!$user->hasPermissionTo('access admin')) {
            $user->givePermissionTo([
                'access admin',
                'manage users',
                'view users',
                'edit users',
                'create users',
                'delete users',
                'manage roles',
                'view roles',
                'edit roles',
                'create roles',
                'delete roles',
                'manage permissions',
                'view permissions',
                'edit permissions',
                'create permissions',
                'delete permissions',
                'settings.menus',
                'settings.general',
            ]);
        }
        
        $this->command->info('Usuário admin criado/atualizado: admin@test.com / password');
    }
}
