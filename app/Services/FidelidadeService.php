<?php

namespace App\Services;

use App\Models\FidelidadePonto;
use App\Models\Pedido;
use App\Models\User;

class FidelidadeService
{
    // Regra de negócio: 1 ponto por real gasto
    const PONTOS_POR_REAL = 1;
    const MINIMO_RESGATE  = 100;

    public function creditarPontos(User $usuario, Pedido $pedido): FidelidadePonto
    {
        $pontos = (int) floor($pedido->total * self::PONTOS_POR_REAL);

        return FidelidadePonto::create([
            'usuario_id' => $usuario->id,
            'pedido_id'  => $pedido->id,
            'tipo'       => 'CREDITO',
            'pontos'     => $pontos,
            'descricao'  => "Crédito automático — Pedido #{$pedido->id}",
        ]);
    }

    public function resgatar(User $usuario, int $pontos): FidelidadePonto
    {
        if ($pontos < self::MINIMO_RESGATE) {
            throw new \Exception(
                "O mínimo para resgate é " . self::MINIMO_RESGATE . " pontos.",
                422
            );
        }

        $saldo = $usuario->saldoPontos();

        if ($pontos > $saldo) {
            throw new \Exception(
                "Saldo insuficiente. Saldo atual: {$saldo} pontos.",
                422
            );
        }

        return FidelidadePonto::create([
            'usuario_id' => $usuario->id,
            'pedido_id'  => null,
            'tipo'       => 'DEBITO',
            'pontos'     => $pontos,
            'descricao'  => "Resgate de {$pontos} pontos",
        ]);
    }

    public function saldo(User $usuario): array
    {
        $saldo = $usuario->saldoPontos();

        return [
            'usuario_id'          => $usuario->id,
            'saldo_pontos'        => $saldo,
            'pontos_por_real'     => self::PONTOS_POR_REAL,
            'minimo_resgate'      => self::MINIMO_RESGATE,
            'equivalencia_reais'  => round($saldo / 10, 2),
        ];
    }
}
