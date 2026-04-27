<?php

namespace App\Services;

use App\Models\Pagamento;
use App\Models\Pedido;

class PagamentoMockService
{
    /**
     * Simula o envio ao gateway externo e registra o resultado.
     * O campo "cenario" permite forçar APROVADO ou RECUSADO nos testes.
     */
    public function solicitar(
        Pedido $pedido,
        string $forma = 'MOCK',
        string $cenario = 'APROVADO'
    ): Pagamento {

        $statusRetorno = ($cenario === 'RECUSADO') ? 'RECUSADO' : 'APROVADO';

        $transacaoId = 'MOCK-TXN-'
            . now()->format('YmdHis')
            . '-PED-'
            . $pedido->id;

        return Pagamento::create([
            'pedido_id'     => $pedido->id,
            'transacao_id'  => $transacaoId,
            'status'        => $statusRetorno,
            'forma'         => $forma,
            'valor'         => $pedido->total,
            'processado_em' => now(),
        ]);
    }
}
