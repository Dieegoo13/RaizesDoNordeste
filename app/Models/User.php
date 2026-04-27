<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf_hash',
        'profile',
        'consentimentos_lgpd',
    ];

    protected $hidden = [
        'password',
        'cpf_hash', // nunca exposto nas respostas (LGPD)
    ];

    protected $casts = [
        'consentimentos_lgpd' => 'boolean',
    ];

    // JWT obrigatório
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['profile' => $this->profile];
    }

    // Relacionamentos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    public function fidelidadePontos()
    {
        return $this->hasMany(FidelidadePonto::class, 'usuario_id');
    }

    // Calcula saldo de pontos dinamicamente
    public function saldoPontos(): int
    {
        $creditos = $this->fidelidadePontos()->where('tipo', 'CREDITO')->sum('pontos');
        $debitos  = $this->fidelidadePontos()->where('tipo', 'DEBITO')->sum('pontos');
        return $creditos - $debitos;
    }
}
