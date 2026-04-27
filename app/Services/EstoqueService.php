<?php

namespace App\Services;

use App\Models\Estoque;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EstoqueService
{
    public function movimentar(array $dados, User $operador): Estoque
    {
        $estoque = Estoque::where('unidade_id', $dados['unidade_id'])
            ->where('produto_id', $dados['produto_id'])
            ->first();

        // Cria registro zerado se ainda não existir para esta unidade
        if (!$estoque) {
            $estoque = Estoque::create([
                'unidade_id' => $dados['unidade_id'],
                'produto_id' => $dados['produto_id'],
                'quantidade' => 0,
            ]);
        }

        if ($dados['tipo'] === 'ENTRADA') {
            $estoque->increment('quantidade', $dados['quantidade']);
        } else {
            // SAIDA — valida saldo antes de debitar
            if ($estoque->quantidade < $dados['quantidade']) {
                throw new \Exception(json_encode([
                    'error'   => 'ESTOQUE_INSUFICIENTE',
                    'message' => 'Quantidade de saída maior que o saldo disponível.',
                    'details' => [[
                        'field' => 'quantidade',
                        'issue' => "Disponível: {$estoque->quantidade}. Solicitado: {$dados['quantidade']}",
                    ]],
                    'timestamp' => now()->toIso8601String(),
                    'path'      => request()->path(),
                ]), 409);
            }
            $estoque->decrement('quantidade', $dados['quantidade']);
        }

        $estoque->refresh();

        // Auditoria — log de movimentação de estoque
        Log::channel('audit')->info('ESTOQUE_MOVIMENTADO', [
            'unidade_id'  => $dados['unidade_id'],
            'produto_id'  => $dados['produto_id'],
            'tipo'        => $dados['tipo'],
            'quantidade'  => $dados['quantidade'],
            'saldo_atual' => $estoque->quantidade,
            'operador_id' => $operador->id,
            'operador'    => $operador->email,
        ]);

        return $estoque;
    }
}
