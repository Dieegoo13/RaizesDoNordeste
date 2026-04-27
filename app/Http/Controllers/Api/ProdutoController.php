<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estoque;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/produtos",
     *     summary="Listar produtos disponíveis por unidade",
     *     tags={"Produtos"},
     *     @OA\Parameter(name="unidade_id", in="query", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page",       in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="limit",      in="query", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Cardápio da unidade"),
     *     @OA\Response(response=400, description="unidade_id obrigatório")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->filled('unidade_id')) {
            return $this->errorResponse(
                'PARAMETRO_OBRIGATORIO',
                'O parâmetro unidade_id é obrigatório.',
                [],
                400
            );
        }

        // Retorna apenas produtos com estoque > 0 nesta unidade
        $produtoIds = Estoque::where('unidade_id', $request->unidade_id)
            ->where('quantidade', '>', 0)
            ->pluck('produto_id');

        $produtos = Produto::whereIn('id', $produtoIds)
            ->where('disponivel', true)
            ->where(function ($q) {
                $q->whereNull('disponivel_ate')
                    ->orWhere('disponivel_ate', '>=', now()->toDateString());
            })
            ->paginate($request->get('limit', 20));

        return response()->json([
            'data' => $produtos->items(),
            'meta' => [
                'total'     => $produtos->total(),
                'page'      => $produtos->currentPage(),
                'limit'     => $produtos->perPage(),
                'last_page' => $produtos->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/produtos/{id}",
     *     summary="Detalhar produto",
     *     tags={"Produtos"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Produto encontrado"),
     *     @OA\Response(response=404, description="Produto não encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return $this->errorResponse(
                'NAO_ENCONTRADO',
                "Produto #{$id} não encontrado.",
                [],
                404
            );
        }

        return response()->json($produto);
    }
}
