<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    protected $table = 'estoque';

    protected $fillable = [
        'unidade_id',
        'produto_id',
        'quantidade',
    ];

    public function unidade()
    {
        return $this->belongsTo(Unidade::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
