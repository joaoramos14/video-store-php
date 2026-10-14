<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Gêneros padrão do TMDB (Apenas tmdb_id e name)
        $genres = [
            ['tmdb_id' => 28,    'name' => 'Ação'],
            ['tmdb_id' => 12,    'name' => 'Aventura'],
            ['tmdb_id' => 16,    'name' => 'Animação'],
            ['tmdb_id' => 35,    'name' => 'Comédia'],
            ['tmdb_id' => 80,    'name' => 'Crime'],
            ['tmdb_id' => 99,    'name' => 'Documentário'],
            ['tmdb_id' => 18,    'name' => 'Drama'],
            ['tmdb_id' => 10751, 'name' => 'Família'],
            ['tmdb_id' => 14,    'name' => 'Fantasia'],
            ['tmdb_id' => 36,    'name' => 'História'],
            ['tmdb_id' => 27,    'name' => 'Terror'],
            ['tmdb_id' => 10402, 'name' => 'Música'],
            ['tmdb_id' => 9648,  'name' => 'Mistério'],
            ['tmdb_id' => 10749, 'name' => 'Romance'],
            ['tmdb_id' => 878,   'name' => 'Ficção Científica'],
            ['tmdb_id' => 53,    'name' => 'Thriller'],
            ['tmdb_id' => 10752, 'name' => 'Guerra'],
            ['tmdb_id' => 37,    'name' => 'Faroeste'],
        ];

        foreach ($genres as $genre) {
            Genre::updateOrCreate(['tmdb_id' => $genre['tmdb_id']], $genre);
        }

        // 2. Usuário Administrador
        // updateOrCreate: o seed pode rodar várias vezes sem violar o e-mail único
        User::updateOrCreate(['email' => 'admin@locadora.test'], [
            'name'     => 'Administrador',
            'password' => Hash::make('123456'),
            'is_admin' => true,
        ]);

        // 3. Usuário Cliente Comum
        User::updateOrCreate(['email' => 'cliente@locadora.test'], [
            'name'     => 'Cliente Teste',
            'password' => Hash::make('123456'),
            'is_admin' => false,
        ]);
    }
}
