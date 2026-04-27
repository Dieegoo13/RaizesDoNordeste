<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = [
        'name',
        'descricao',
        'preco',
        'categoria',
        'disponivel',
        'disponivel_ate',
    ];

    protected $casts = [
        'disponivel'     => 'boolean',
        'disponivel_ate' => 'date',
        'preco'          => 'float',
    ];

    public function estoques()
    {
        return $this->hasMany(Estoque::class);
    }

    public function pedidoItens()
    {
        return $this->hasMany(PedidoItem::class);
    }
}
