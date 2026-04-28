<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EstoqueController;
use App\Http\Controllers\Api\FidelidadeController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\UnidadeController;
use Illuminate\Support\Facades\Route;

// ── Rotas públicas ──────────────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/usuarios',   [AuthController::class, 'register']);

Route::get('/unidades',      [UnidadeController::class, 'index']);
Route::get('/unidades/{id}', [UnidadeController::class, 'show']);

Route::get('/produtos',      [ProdutoController::class, 'index']);
Route::get('/produtos/{id}', [ProdutoController::class, 'show']);

// ── Rotas autenticadas ──────────────────────────────────────────────────────
Route::middleware('auth:api')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Pedidos
    Route::post('/pedidos',              [PedidoController::class, 'store']);
    Route::get('/pedidos',               [PedidoController::class, 'index']);
    Route::get('/pedidos/{id}',          [PedidoController::class, 'show']);
    Route::patch('/pedidos/{id}/status', [PedidoController::class, 'updateStatus']);

    // Estoque
    Route::get('/estoque',                [EstoqueController::class, 'index']);
    Route::post('/estoque/movimentacao',  [EstoqueController::class, 'movimentar']);

    // Fidelidade
    Route::get('/fidelidade/saldo/{clienteId}',     [FidelidadeController::class, 'saldo']);
    Route::get('/fidelidade/historico/{clienteId}', [FidelidadeController::class, 'historico']);
    Route::post('/fidelidade/resgatar',             [FidelidadeController::class, 'resgatar']);
});
