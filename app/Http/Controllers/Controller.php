<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Raízes do Nordeste API",
 *     version="1.0.0",
 *     description="API REST para a rede de lanchonetes Raízes do Nordeste. Suporta múltiplos canais (APP, TOTEM, BALCAO, PICKUP, WEB), controle de estoque por unidade, pagamento via mock e programa de fidelidade.",
 *     @OA\Contact(email="dev@raizes.com")
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token JWT obtido no POST /auth/login. Formato: Bearer {token}"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor local de desenvolvimento"
 * )
 *
 * @OA\Tag(name="Auth",       description="Autenticação e cadastro")
 * @OA\Tag(name="Unidades",   description="Unidades da rede")
 * @OA\Tag(name="Produtos",   description="Cardápio por unidade")
 * @OA\Tag(name="Estoque",    description="Controle de estoque por unidade")
 * @OA\Tag(name="Pedidos",    description="Gestão de pedidos multicanal")
 * @OA\Tag(name="Fidelidade", description="Programa de pontos")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // Padrão de erro utilizado em todos os controllers
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

    // Verifica se o usuário autenticado tem o perfil necessário
    protected function authorizeRoles(array $roles): void
    {
        $user = auth('api')->user();
        if (!in_array($user->profile, $roles)) {
            abort(403, json_encode([
                'error'   => 'ACESSO_NEGADO',
                'message' => 'Você não tem permissão para acessar este recurso.',
                'details' => ['perfil_necessario' => $roles, 'seu_perfil' => $user->profile],
                'timestamp' => now()->toIso8601String(),
                'path'      => request()->path(),
            ]));
        }
    }
}
