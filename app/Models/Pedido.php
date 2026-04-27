<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'cliente_id',
        'unidade_id',
        'total',
        'status',
        'canal_pedido',
    ];

    // Mapa de transições válidas de status
    const TRANSICOES_VALIDAS = [
        'AGUARDANDO_PAGAMENTO' => ['RECEBIDO', 'CANCELADO'],
        'RECEBIDO'             => ['EM_PREPARO', 'CANCELADO'],
        'EM_PREPARO'           => ['PRONTO'],
        'PRONTO'               => ['ENTREGUE'],
        'ENTREGUE'             => [],
        'CANCELADO'            => [],
    ];

    public function podeTransicionarPara(string $novoStatus): bool
    {
        return in_array(
            $novoStatus,
            self::TRANSICOES_VALIDAS[$this->status] ?? []
        );
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class);
    }

    public function itens()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function pagamento()
    {
        return $this->hasOne(Pagamento::class);
    }

    public function pontos()
    {
        return $this->hasMany(FidelidadePonto::class);
    }
}
