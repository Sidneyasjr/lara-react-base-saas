<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuários de teste
        User::factory(15)->create();

        // Criar um usuário específico para teste
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Criar um administrador
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
        ]);
    }
}
