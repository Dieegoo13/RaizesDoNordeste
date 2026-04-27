<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PedidoController extends Controller
{
    public function __construct(private PedidoService $pedidoService) {}

    /**
     * @OA\Get(
     *     path="/pedidos",
     *     summary="Listar pedidos com filtros",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="canal_pedido", in="query", @OA\Schema(type="string", enum={"APP","TOTEM","BALCAO","PICKUP","WEB"})),
     *     @OA\Parameter(name="status",       in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="unidade_id",   in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page",         in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Lista paginada de pedidos"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Sem permissão")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeRoles(['ADMIN', 'GERENTE', 'COZINHA', 'ATENDENTE']);

        $query = Pedido::with(['cliente:id,name,email', 'unidade:id,name,cidade']);

        if ($request->filled('canal_pedido')) {
            $query->where('canal_pedido', $request->canal_pedido);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        $pedidos = $query->orderByDesc('created_at')
            ->paginate($request->get('limit', 10));

        return response()->json([
            'data' => $pedidos->items(),
            'meta' => [
                'total'     => $pedidos->total(),
                'page'      => $pedidos->currentPage(),
                'limit'     => $pedidos->perPage(),
                'last_page' => $pedidos->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/pedidos",
     *     summary="Criar novo pedido — fluxo crítico",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"unidade_id","canal_pedido","itens"},
     *             @OA\Property(property="unidade_id",         type="integer", example=1),
     *             @OA\Property(property="canal_pedido",       type="string",  enum={"APP","TOTEM","BALCAO","PICKUP","WEB"}, example="TOTEM"),
     *             @OA\Property(property="forma_pagamento",    type="string",  example="MOCK"),
     *             @OA\Property(property="cenario_pagamento",  type="string",  enum={"APROVADO","RECUSADO"}, example="APROVADO"),
     *             @OA\Property(property="itens", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="produto_id", type="integer", example=1),
     *                     @OA\Property(property="quantidade", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Pedido criado com sucesso"),
     *     @OA\Response(response=409, description="Estoque insuficiente"),
     *     @OA\Response(response=422, description="canal_pedido ausente ou inválido")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'unidade_id'         => 'required|integer|exists:unidades,id',
            'canal_pedido'       => 'required|in:APP,TOTEM,BALCAO,PICKUP,WEB',
            'forma_pagamento'    => 'nullable|string',
            'cenario_pagamento'  => 'nullable|in:APROVADO,RECUSADO',
            'itens'              => 'required|array|min:1',
            'itens.*.produto_id' => 'required|integer|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
        ], [
            'canal_pedido.required' => 'O campo canal_pedido é obrigatório (APP, TOTEM, BALCAO, PICKUP, WEB).',
            'canal_pedido.in'       => 'canal_pedido inválido. Valores aceitos: APP, TOTEM, BALCAO, PICKUP, WEB.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'VALIDACAO_INVALIDA',
                'Dados da requisição inválidos.',
                $validator->errors()->toArray(),
                422
            );
        }

        try {
            $pedido = $this->pedidoService->criar(
                $request->all(),
                auth('api')->user()
            );
        } catch (\Exception $e) {
            $body = json_decode($e->getMessage(), true);
            if ($body) return response()->json($body, $e->getCode() ?: 500);
            return $this->errorResponse('ERRO_INTERNO', $e->getMessage(), [], 500);
        }

        return response()->json($this->formatPedido($pedido), 201);
    }

    /**
     * @OA\Get(
     *     path="/pedidos/{id}",
     *     summary="Detalhar pedido",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalhes do pedido"),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Pedido não encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $pedido = Pedido::with([
            'itens.produto',
            'pagamento',
            'unidade:id,name,cidade',
            'cliente:id,name,email',
        ])->find($id);

        if (!$pedido) {
            return $this->errorResponse('NAO_ENCONTRADO', "Pedido #{$id} não encontrado.", [], 404);
        }

        $user = auth('api')->user();

        // Cliente só pode ver o próprio pedido
        if ($user->profile === 'CLIENTE' && $pedido->cliente_id !== $user->id) {
            return $this->errorResponse('ACESSO_NEGADO', 'Sem permissão para visualizar este pedido.', [], 403);
        }

        return response()->json($this->formatPedido($pedido));
    }

    /**
     * @OA\Patch(
     *     path="/pedidos/{id}/status",
     *     summary="Atualizar status do pedido",
     *     tags={"Pedidos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"RECEBIDO","EM_PREPARO","PRONTO","ENTREGUE","CANCELADO"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status atualizado"),
     *     @OA\Response(response=400, description="Transição de status inválida"),
     *     @OA\Response(response=404, description="Pedido não encontrado")
     * )
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeRoles(['ADMIN', 'GERENTE', 'COZINHA', 'ATENDENTE']);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:RECEBIDO,EM_PREPARO,PRONTO,ENTREGUE,CANCELADO',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'VALIDACAO_INVALIDA',
                'Status inválido.',
                $validator->errors()->toArray(),
                422
            );
        }

        $pedido = Pedido::find($id);

        if (!$pedido) {
            return $this->errorResponse('NAO_ENCONTRADO', "Pedido #{$id} não encontrado.", [], 404);
        }

        try {
            $pedido = $this->pedidoService->atualizarStatus(
                $pedido,
                $request->status,
                auth('api')->user()
            );
        } catch (\Exception $e) {
            $body = json_decode($e->getMessage(), true);
            if ($body) return response()->json($body, $e->getCode() ?: 400);
            return $this->errorResponse('ERRO', $e->getMessage(), [], 400);
        }

        return response()->json([
            'pedido_id'    => $pedido->id,
            'status_atual' => $pedido->status,
            'updated_at'   => $pedido->updated_at,
        ]);
    }

    private function formatPedido(Pedido $pedido): array
    {
        return [
            'pedido_id'    => $pedido->id,
            'canal_pedido' => $pedido->canal_pedido,
            'status'       => $pedido->status,
            'unidade'      => $pedido->unidade?->only(['id', 'name', 'cidade']),
            'total'        => $pedido->total,
            'itens'        => $pedido->itens->map(fn($i) => [
                'produto_id'     => $i->produto_id,
                'nome_produto'   => $i->produto?->name,
                'quantidade'     => $i->quantidade,
                'preco_unitario' => $i->preco_unitario,
                'subtotal'       => $i->subtotal,
            ]),
            'pagamento'    => $pedido->pagamento ? [
                'transacao_id'  => $pedido->pagamento->transacao_id,
                'status'        => $pedido->pagamento->status,
                'forma'         => $pedido->pagamento->forma,
                'valor'         => $pedido->pagamento->valor,
                'processado_em' => $pedido->pagamento->processado_em,
            ] : null,
            'created_at'   => $pedido->created_at,
            'updated_at'   => $pedido->updated_at,
        ];
    }
}
