<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *     title="Raízes do Nordeste API",
 *     version="1.0.0",
 *     description="API REST para a rede de lanchonetes Raízes do Nordeste."
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Tag(name="Auth",       description="Autenticação e cadastro")
 * @OA\Tag(name="Unidades",   description="Unidades da rede")
 * @OA\Tag(name="Produtos",   description="Cardápio por unidade")
 * @OA\Tag(name="Estoque",    description="Controle de estoque")
 * @OA\Tag(name="Pedidos",    description="Gestão de pedidos multicanal")
 * @OA\Tag(name="Fidelidade", description="Programa de pontos")
 *
 * @OA\PathItem(path="/api")
 */
class SwaggerDefinitions {}
