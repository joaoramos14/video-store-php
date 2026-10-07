<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_id')->nullable()->unique();
            $table->string('title');
            $table->string('director')->nullable(); // Nome do diretor retornado da API
            $table->text('synopsis')->nullable();
            $table->unsignedSmallInteger('year');
            $table->unsignedSmallInteger('duration'); // Minutos 
            $table->string('cover')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->decimal('daily_price', 8, 2);
            $table->foreignId('genre_id')->constrained()->restrictOnDelete();
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};