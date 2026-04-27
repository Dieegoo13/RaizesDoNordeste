<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cidade');
            $table->string('estado', 2)->default('PE');
            $table->string('endereco');
            $table->boolean('ativa')->default(true);
            $table->boolean('possui_cozinha_completa')->default(true);
            $table->time('horario_abertura')->default('06:00:00');
            $table->time('horario_fechamento')->default('22:00:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades');
    }
};
