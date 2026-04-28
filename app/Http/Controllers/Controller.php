<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Tag(name="Auth",       description="Autenticação e cadastro")
 * @OA\Tag(name="Unidades",   description="Unidades da rede")
 * @OA\Tag(name="Produtos",   description="Cardápio por unidade")
 * @OA\Tag(name="Estoque",    description="Controle de estoque")
 * @OA\Tag(name="Pedidos",    description="Gestão de pedidos multicanal")
 * @OA\Tag(name="Fidelidade", description="Programa de pontos")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function errorResponse(
        string $error,
        string $message,
        array  $details,
        int    $status
    ): JsonResponse {
        return response()->json([
            'error'     => $error,
            'message'   => $message,
            'details'   => $details,
            'timestamp' => now()->toIso8601String(),
            'path'      => request()->path(),
        ], $status);
    }

    protected function authorizeRoles(array $roles): void
    {
        $user = auth('api')->user();
        if (!in_array($user->profile, $roles)) {
            abort(403, json_encode([
                'error'     => 'ACESSO_NEGADO',
                'message'   => 'Você não tem permissão para acessar este recurso.',
                'details'   => [
                    'perfil_necessario' => $roles,
                    'seu_perfil'        => $user->profile,
                ],
                'timestamp' => now()->toIso8601String(),
                'path'      => request()->path(),
            ]));
        }
    }
}
