<?php

namespace App\Services;

use App\Models\Estoque;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoService
{
    public function __construct(
        private PagamentoMockService $pagamentoService,
        private FidelidadeService    $fidelidadeService,
    ) {}

    public function criar(array $dados, User $usuario): Pedido
    {
        return DB::transaction(function () use ($dados, $usuario) {

            // 1. Validar estoque de cada item antes de criar o pedido
            foreach ($dados['itens'] as $item) {
                $estoque = Estoque::where('unidade_id', $dados['unidade_id'])
                    ->where('produto_id', $item['produto_id'])
                    ->first();

                $disponivel = $estoque ? $estoque->quantidade : 0;

                if (!$estoque || $estoque->quantidade < $item['quantidade']) {
                    abort(409, json_encode([
                        'error'   => 'ESTOQUE_INSUFICIENTE',
                        'message' => 'Quantidade indisponível para um ou mais itens.',
                        'details' => [[
                            'field' => 'itens[produto_id=' . $item['produto_id'] . '].quantidade',
                            'issue' => "Solicitado: {$item['quantidade']}. Disponível: {$disponivel}",
                        ]],
                        'timestamp' => now()->toIso8601String(),
                        'path'      => request()->path(),
                    ]));
                }
            }

            // 2. Calcular total e cachear produtos
            $total         = 0;
            $produtosCache = [];

            foreach ($dados['itens'] as $item) {
                $produto = Produto::findOrFail($item['produto_id']);
                $produtosCache[$item['produto_id']] = $produto;
                $total += $produto->preco * $item['quantidade'];
            }

            // 3. Criar o pedido
            $pedido = Pedido::create([
                'cliente_id'  => $usuario->id,
                'unidade_id'  => $dados['unidade_id'],
                'canal_pedido' => $dados['canal_pedido'],
                'status'      => 'AGUARDANDO_PAGAMENTO',
                'total'       => round($total, 2),
            ]);

            // 4. Criar os itens do pedido
            foreach ($dados['itens'] as $item) {
                $pedido->itens()->create([
                    'produto_id'     => $item['produto_id'],
                    'quantidade'     => $item['quantidade'],
                    'preco_unitario' => $produtosCache[$item['produto_id']]->preco,
                ]);
            }

            // 5. Solicitar pagamento via Mock
            $cenario   = $dados['cenario_pagamento'] ?? 'APROVADO';
            $forma     = $dados['forma_pagamento']   ?? 'MOCK';
            $pagamento = $this->pagamentoService->solicitar($pedido, $forma, $cenario);

            // 6. Atualizar status conforme retorno do gateway
            if ($pagamento->status === 'APROVADO') {

                $pedido->update(['status' => 'RECEBIDO']);

                // Debitar estoque somente se pagamento aprovado
                foreach ($dados['itens'] as $item) {
                    Estoque::where('unidade_id', $dados['unidade_id'])
                        ->where('produto_id', $item['produto_id'])
                        ->decrement('quantidade', $item['quantidade']);
                }

                // Creditar pontos de fidelidade (apenas com consentimento LGPD)
                if ($usuario->consentimentos_lgpd) {
                    $this->fidelidadeService->creditarPontos($usuario, $pedido);
                }
            } else {
                $pedido->update(['status' => 'CANCELADO']);
            }

            // 7. Log de auditoria
            Log::channel('audit')->info('PEDIDO_CRIADO', [
                'pedido_id'    => $pedido->id,
                'usuario_id'   => $usuario->id,
                'usuario'      => $usuario->email,
                'canal_pedido' => $pedido->canal_pedido,
                'status'       => $pedido->status,
                'total'        => $pedido->total,
                'pagamento'    => $pagamento->status,
                'transacao_id' => $pagamento->transacao_id,
            ]);

            return $pedido->load(['itens.produto', 'pagamento', 'unidade']);
        });
    }

    public function atualizarStatus(
        Pedido $pedido,
        string $novoStatus,
        User   $operador
    ): Pedido {

        if (!$pedido->podeTransicionarPara($novoStatus)) {
            abort(400, json_encode([
                'error'   => 'TRANSICAO_INVALIDA',
                'message' => "Não é possível mover o pedido de '{$pedido->status}' para '{$novoStatus}'.",
                'details' => [
                    'status_atual'      => $pedido->status,
                    'status_solicitado' => $novoStatus,
                    'transicoes_validas' => \App\Models\Pedido::TRANSICOES_VALIDAS[$pedido->status],
                ],
                'timestamp' => now()->toIso8601String(),
                'path'      => request()->path(),
            ]));
        }

        $statusAnterior = $pedido->status;
        $pedido->update(['status' => $novoStatus]);

        // Log de auditoria
        Log::channel('audit')->info('STATUS_PEDIDO_ATUALIZADO', [
            'pedido_id'       => $pedido->id,
            'status_anterior' => $statusAnterior,
            'status_novo'     => $novoStatus,
            'operador_id'     => $operador->id,
            'operador'        => $operador->email,
        ]);

        return $pedido;
    }
}
