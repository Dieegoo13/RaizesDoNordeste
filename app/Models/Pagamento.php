<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $fillable = [
        'pedido_id',
        'transacao_id',
        'status',
        'forma',
        'valor',
        'processado_em',
    ];

    protected $casts = [
        'processado_em' => 'datetime',
        'valor'         => 'float',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
