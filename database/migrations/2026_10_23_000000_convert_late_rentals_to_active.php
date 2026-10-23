<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * O status 'late' deixa de ser gravado: o atraso passa a ser calculado pelo prazo (Rental::isLate()).
     * Os aluguéis já marcados como atrasados voltam a ser 'active'.
     */
    public function up(): void
    {
        DB::table('rentals')->where('status', 'late')->update(['status' => 'active']);
    }

    public function down(): void
    {
        DB::table('rentals')->where('status', 'active')->where('due_at', '<', now())->update(['status' => 'late']);
    }
};
