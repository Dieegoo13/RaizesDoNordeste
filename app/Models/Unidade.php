<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    protected $fillable = [
        'name',
        'cidade',
        'estado',
        'endereco',
        'ativa',
        'possui_cozinha_completa',
        'horario_abertura',
        'horario_fechamento',
    ];

    protected $casts = [
        'ativa'                   => 'boolean',
        'possui_cozinha_completa' => 'boolean',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function estoques()
    {
        return $this->hasMany(Estoque::class);
    }
}
