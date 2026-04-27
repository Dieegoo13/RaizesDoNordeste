<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unidade_id')
                ->constrained('unidades')
                ->onDelete('restrict');
            $table->foreignId('produto_id')
                ->constrained('produtos')
                ->onDelete('restrict');
            $table->integer('quantidade')->default(0);
            $table->timestamps();

            // Cada produto existe apenas uma vez por unidade
            $table->unique(['unidade_id', 'produto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estoque');
    }
};
