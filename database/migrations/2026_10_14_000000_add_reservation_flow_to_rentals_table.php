<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fluxo: reserved -> active -> returned, com os desvios cancelled e expired.
     * - rented_at e due_at só são preenchidos na retirada, por isso passam a aceitar vazio.
     * - status deixa de ser enum (que precisaria de ALTER específico por banco) e vira texto curto.
     *   Os valores válidos ficam em App\Models\Rental.
     */
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->timestamp('reserved_until')->nullable()->after('days');
            $table->timestamp('rented_at')->nullable()->change();
            $table->timestamp('due_at')->nullable()->change();
            $table->string('status', 20)->default('reserved')->change();
        });
    }

    public function down(): void
    {
        // Só desfaz sem perdas se não houver reservas, cancelamentos ou expirações gravados.
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('reserved_until');
            $table->timestamp('rented_at')->nullable(false)->change();
            $table->timestamp('due_at')->nullable(false)->change();
            $table->enum('status', ['active', 'returned', 'late'])->default('active')->change();
        });
    }
};
