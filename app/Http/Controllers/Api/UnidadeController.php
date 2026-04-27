<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unidade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnidadeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/unidades",
     *     summary="Listar unidades ativas da rede",
     *     tags={"Unidades"},
     *     @OA\Parameter(name="page",  in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", default=10)),
     *     @OA\Response(response=200, description="Lista paginada de unidades")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $unidades = Unidade::where('ativa', true)
            ->paginate($request->get('limit', 10));

        return response()->json([
            'data' => $unidades->items(),
            'meta' => [
                'total'     => $unidades->total(),
                'page'      => $unidades->currentPage(),
                'limit'     => $unidades->perPage(),
                'last_page' => $unidades->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/unidades/{id}",
     *     summary="Detalhar unidade",
     *     tags={"Unidades"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalhes da unidade"),
     *     @OA\Response(response=404, description="Unidade não encontrada")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $unidade = Unidade::find($id);

        if (!$unidade) {
            return $this->errorResponse(
                'NAO_ENCONTRADO',
                "Unidade #{$id} não encontrada.",
                [],
                404
            );
        }

        return response()->json($unidade);
    }
}
