<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password'); // bcrypt hash — LGPD
            $table->string('cpf_hash')->nullable(); // CPF criptografado — nunca exposto
            $table->enum('profile', ['ADMIN', 'GERENTE', 'ATENDENTE', 'COZINHA', 'CLIENTE'])
                ->default('CLIENTE');
            $table->boolean('consentimentos_lgpd')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
