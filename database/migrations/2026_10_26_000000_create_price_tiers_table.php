<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Faixas de preço: o filme passa a apontar para uma faixa em vez de ter diária livre.
     * - As faixas padrão são criadas aqui (e não no seeder) porque o preenchimento dos filmes depende delas.
     * - Cada filme existente vai para a faixa com a diária mais próxima da que ele tinha.
     * - movies.daily_price continua existindo até o restante do sistema passar a ler da faixa.
     */
    public function up(): void
    {
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('daily_price', 8, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('price_tiers')->insert([
            ['name' => 'Lançamento', 'daily_price' => 7.00, 'description' => 'Filmes recentes', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Catálogo',   'daily_price' => 5.00, 'description' => 'Filmes de acervo', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Clássico',   'daily_price' => 3.00, 'description' => 'Filmes antigos',   'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('movies', function (Blueprint $table) {
            $table->foreignId('price_tier_id')->nullable()->after('daily_price')->constrained()->restrictOnDelete();
        });

        $tiers = DB::table('price_tiers')->pluck('daily_price', 'id');

        DB::table('movies')->select('id', 'daily_price')->orderBy('id')->each(function ($movie) use ($tiers) {
            $closestTierId = $tiers
                ->sortBy(fn($price) => abs($price - $movie->daily_price))
                ->keys()
                ->first();

            DB::table('movies')->where('id', $movie->id)->update(['price_tier_id' => $closestTierId]);
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('price_tier_id');
        });

        Schema::dropIfExists('price_tiers');
    }
};
