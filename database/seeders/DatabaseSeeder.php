<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuário Administrador
        User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@locadora.test',
            'password' => Hash::make('123456'),
            'role'     => 'admin',
        ]);

        // 2. Usuário Cliente
        User::factory()->create([
            'name'     => 'Cliente',
            'email'    => 'cliente@locadora.test',
            'password' => Hash::make('123456'),
            'role'     => 'client', // Certifique-se de ser 'client' ou 'user' conforme sua migration
        ]);

        // 3. Gêneros base
        $genres = collect(['Ação', 'Comédia', 'Drama', 'Terror', 'Ficção científica'])
            ->map(fn ($name) => Genre::create([
                'name'        => $name,
                'description' => "Filmes de {$name}",
            ]));

        // 4. Cria 20 filmes distribuídos aleatoriamente nos gêneros acima
        Movie::factory(20)->recycle($genres)->create();
    }
}