<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')
                ->constrained('pedidos')
                ->onDelete('restrict');
            $table->string('transacao_id', 100); 
            $table->enum('status', ['APROVADO', 'RECUSADO', 'PENDENTE'])
                ->default('PENDENTE');
            $table->string('forma', 50)->default('MOCK');
            $table->decimal('valor', 10, 2);
            $table->timestamp('processado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
