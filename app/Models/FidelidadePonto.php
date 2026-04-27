<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FidelidadePonto extends Model
{
    protected $table = 'fidelidade_pontos';

    protected $fillable = [
        'usuario_id',
        'pedido_id',
        'tipo',
        'pontos',
        'descricao',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
