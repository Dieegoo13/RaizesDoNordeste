<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estoque;
use App\Services\EstoqueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EstoqueController extends Controller
{
    public function __construct(private EstoqueService $estoqueService) {}

    /**
     * @OA\Get(
     *     path="/estoque",
     *     summary="Consultar estoque por unidade",
     *     tags={"Estoque"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="unidade_id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Saldo de estoque da unidade"),
     *     @OA\Response(response=400, description="unidade_id obrigatório"),
     *     @OA\Response(response=403, description="Sem permissão")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeRoles(['ADMIN', 'GERENTE']);

        if (!$request->filled('unidade_id')) {
            return $this->errorResponse(
                'PARAMETRO_OBRIGATORIO',
                'O parâmetro unidade_id é obrigatório.',
                [],
                400
            );
        }

        $estoque = Estoque::with('produto:id,name,categoria,preco')
            ->where('unidade_id', $request->unidade_id)
            ->paginate($request->get('limit', 20));

        return response()->json([
            'data' => $estoque->items(),
            'meta' => [
                'total'     => $estoque->total(),
                'page'      => $estoque->currentPage(),
                'limit'     => $estoque->perPage(),
                'last_page' => $estoque->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/estoque/movimentacao",
     *     summary="Registrar entrada ou saída de estoque",
     *     tags={"Estoque"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"unidade_id","produto_id","tipo","quantidade"},
     *             @OA\Property(property="unidade_id", type="integer", example=1),
     *             @OA\Property(property="produto_id", type="integer", example=1),
     *             @OA\Property(property="tipo",       type="string",  enum={"ENTRADA","SAIDA"}, example="ENTRADA"),
     *             @OA\Property(property="quantidade", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Movimentação registrada"),
     *     @OA\Response(response=409, description="Saldo insuficiente para saída"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function movimentar(Request $request): JsonResponse
    {
        $this->authorizeRoles(['ADMIN', 'GERENTE']);

        $validator = Validator::make($request->all(), [
            'unidade_id' => 'required|integer|exists:unidades,id',
            'produto_id' => 'required|integer|exists:produtos,id',
            'tipo'       => 'required|in:ENTRADA,SAIDA',
            'quantidade' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'VALIDACAO_INVALIDA',
                'Dados inválidos.',
                $validator->errors()->toArray(),
                422
            );
        }

        try {
            $estoque = $this->estoqueService->movimentar(
                $request->all(),
                auth('api')->user()
            );
        } catch (\Exception $e) {
            $body = json_decode($e->getMessage(), true);
            if ($body) return response()->json($body, $e->getCode() ?: 409);
            return $this->errorResponse('ERRO', $e->getMessage(), [], 500);
        }

        return response()->json([
            'unidade_id'  => $estoque->unidade_id,
            'produto_id'  => $estoque->produto_id,
            'tipo'        => $request->tipo,
            'quantidade'  => $request->quantidade,
            'saldo_atual' => $estoque->quantidade,
            'updated_at'  => $estoque->updated_at,
        ], 201);
    }
}
