<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * O preço do filme passa a vir só da faixa: price_tier_id vira obrigatório e daily_price sai.
     * Os aluguéis não mudam, porque rentals.total_price guarda o valor cobrado na reserva.
     */
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->foreignId('price_tier_id')->nullable(false)->change();
            $table->dropColumn('daily_price');
        });
    }

    public function down(): void
    {
        // Devolve a diária a partir da faixa. O valor livre que existia antes das faixas não é recuperado.
        Schema::table('movies', function (Blueprint $table) {
            $table->decimal('daily_price', 8, 2)->nullable()->after('stock');
        });

        DB::table('price_tiers')->pluck('daily_price', 'id')->each(function ($price, $tierId) {
            DB::table('movies')->where('price_tier_id', $tierId)->update(['daily_price' => $price]);
        });

        Schema::table('movies', function (Blueprint $table) {
            $table->decimal('daily_price', 8, 2)->nullable(false)->change();
            $table->foreignId('price_tier_id')->nullable()->change();
        });
    }
};
