<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Login do usuário",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email",    type="string", example="admin@raizes.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token JWT retornado com sucesso"),
     *     @OA\Response(response=401, description="Credenciais inválidas"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'VALIDACAO_INVALIDA',
                'Dados inválidos.',
                $validator->errors()->toArray(),
                422
            );
        }

        $token = auth('api')->attempt([
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        if (!$token) {
            return $this->errorResponse(
                'CREDENCIAIS_INVALIDAS',
                'E-mail ou senha incorretos.',
                [],
                401
            );
        }

        $user = auth('api')->user();

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => config('jwt.ttl') * 60,
            'user' => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'profile' => $user->profile,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout do usuário",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logout realizado"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Logout realizado com sucesso.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/usuarios",
     *     summary="Cadastro de novo cliente",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","consentimentos_lgpd"},
     *             @OA\Property(property="name",                type="string",  example="Maria Santos"),
     *             @OA\Property(property="email",               type="string",  example="maria@exemplo.com"),
     *             @OA\Property(property="password",            type="string",  example="Senha@123"),
     *             @OA\Property(property="consentimentos_lgpd", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuário criado com sucesso"),
     *     @OA\Response(response=409, description="E-mail já cadastrado"),
     *     @OA\Response(response=422, description="Consentimento LGPD obrigatório")
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email',
            'password'            => 'required|string|min:6',
            'consentimentos_lgpd' => 'required|boolean|accepted',
        ], [
            'consentimentos_lgpd.accepted' => 'O consentimento LGPD é obrigatório para cadastro.',
            'email.unique'                 => 'Este e-mail já está cadastrado.',
        ]);

        if ($validator->fails()) {
            $emailJaExiste = User::where('email', $request->email)->exists();
            $status        = $emailJaExiste ? 409 : 422;

            return $this->errorResponse(
                'VALIDACAO_INVALIDA',
                'Dados inválidos.',
                $validator->errors()->toArray(),
                $status
            );
        }

        $user = User::create([
            'name'                => $request->name,
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'profile'             => 'CLIENTE',
            'consentimentos_lgpd' => true,
        ]);

        return response()->json([
            'id'                  => $user->id,
            'name'                => $user->name,
            'email'               => $user->email,
            'profile'             => $user->profile,
            'consentimentos_lgpd' => $user->consentimentos_lgpd,
            'created_at'          => $user->created_at,
        ], 201);
    }
}
