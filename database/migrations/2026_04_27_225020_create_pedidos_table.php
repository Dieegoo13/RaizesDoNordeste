<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')
                ->constrained('users')
                ->onDelete('restrict');
            $table->foreignId('unidade_id')
                ->constrained('unidades')
                ->onDelete('restrict');
            $table->decimal('total', 10, 2);
            $table->enum('status', [
                'AGUARDANDO_PAGAMENTO',
                'RECEBIDO',
                'EM_PREPARO',
                'PRONTO',
                'ENTREGUE',
                'CANCELADO',
            ])->default('AGUARDANDO_PAGAMENTO');
            $table->enum('canal_pedido', [
                'APP',
                'TOTEM',
                'BALCAO',
                'PICKUP',
                'WEB',
            ]);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
