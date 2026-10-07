<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('movie_id')->constrained()->restrictOnDelete();
            $table->timestamp('rented_at');
            $table->timestamp('due_at');
            $table->timestamp('returned_at')->nullable();
            $table->unsignedTinyInteger('days');
            $table->decimal('total_price', 8, 2);
            $table->enum('status', ['active', 'returned', 'late'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
