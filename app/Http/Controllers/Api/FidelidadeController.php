<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FidelidadePonto;
use App\Models\User;
use App\Services\FidelidadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FidelidadeController extends Controller
{
    public function __construct(private FidelidadeService $fidelidadeService) {}

    /**
     * @OA\Get(
     *     path="/fidelidade/saldo/{clienteId}",
     *     summary="Consultar saldo de pontos do cliente",
     *     tags={"Fidelidade"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="clienteId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Saldo de pontos"),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Usuário não encontrado")
     * )
     */
    public function saldo(int $clienteId): JsonResponse
    {
        $authUser = auth('api')->user();

        if ($authUser->profile === 'CLIENTE' && $authUser->id !== $clienteId) {
            return $this->errorResponse(
                'ACESSO_NEGADO',
                'Você só pode consultar seu próprio saldo.',
                [],
                403
            );
        }

        $usuario = User::find($clienteId);

        if (!$usuario) {
            return $this->errorResponse('NAO_ENCONTRADO', 'Usuário não encontrado.', [], 404);
        }

        return response()->json($this->fidelidadeService->saldo($usuario));
    }

    /**
     * @OA\Get(
     *     path="/fidelidade/historico/{clienteId}",
     *     summary="Histórico de pontos do cliente",
     *     tags={"Fidelidade"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="clienteId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Histórico de pontos paginado"),
     *     @OA\Response(response=403, description="Sem permissão")
     * )
     */
    public function historico(Request $request, int $clienteId): JsonResponse
    {
        $authUser = auth('api')->user();

        if ($authUser->profile === 'CLIENTE' && $authUser->id !== $clienteId) {
            return $this->errorResponse('ACESSO_NEGADO', 'Acesso negado.', [], 403);
        }

        $historico = FidelidadePonto::where('usuario_id', $clienteId)
            ->orderByDesc('created_at')
            ->paginate($request->get('limit', 10));

        return response()->json([
            'data' => $historico->items(),
            'meta' => [
                'total'     => $historico->total(),
                'page'      => $historico->currentPage(),
                'limit'     => $historico->perPage(),
                'last_page' => $historico->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/fidelidade/resgatar",
     *     summary="Resgatar pontos de fidelidade",
     *     tags={"Fidelidade"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"pontos"},
     *             @OA\Property(property="pontos", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Pontos resgatados com sucesso"),
     *     @OA\Response(response=422, description="Saldo insuficiente ou abaixo do mínimo")
     * )
     */
    public function resgatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pontos' => 'required|integer|min:100',
        ], [
            'pontos.min' => 'O mínimo para resgate é 100 pontos.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'VALIDACAO_INVALIDA',
                'Dados inválidos.',
                $validator->errors()->toArray(),
                422
            );
        }

        $usuario = auth('api')->user();

        try {
            $transacao = $this->fidelidadeService->resgatar($usuario, $request->pontos);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'SALDO_INSUFICIENTE',
                $e->getMessage(),
                [],
                422
            );
        }

        return response()->json([
            'message'      => "Resgate de {$request->pontos} pontos realizado com sucesso.",
            'saldo_atual'  => $usuario->saldoPontos(),
            'transacao_id' => $transacao->id,
        ], 201);
    }
}
