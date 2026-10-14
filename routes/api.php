<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CadastroController;
use App\Http\Controllers\Api\ConciliacaoController;
use App\Http\Controllers\Api\ContaPagarController;
use App\Http\Controllers\Api\ContaReceberController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\MovimentoBancarioController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth.token')->group(function () {
    Route::post('/auth/switch-empresa', [AuthController::class, 'switchEmpresa']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/cadastros/{recurso}', [CadastroController::class, 'index']);
    Route::get('/cadastros/{recurso}/{id}', [CadastroController::class, 'show']);
    Route::post('/cadastros/{recurso}', [CadastroController::class, 'store']);
    Route::put('/cadastros/{recurso}/{id}', [CadastroController::class, 'update']);
    Route::delete('/cadastros/{recurso}/{id}', [CadastroController::class, 'destroy']);

    Route::get('/contas-pagar', [ContaPagarController::class, 'index']);
    Route::get('/contas-pagar/{id}', [ContaPagarController::class, 'show']);
    Route::post('/contas-pagar', [ContaPagarController::class, 'store']);
    Route::put('/contas-pagar/{id}', [ContaPagarController::class, 'update']);
    Route::delete('/contas-pagar/{id}', [ContaPagarController::class, 'destroy']);
    Route::post('/contas-pagar/{id}/baixar', [ContaPagarController::class, 'baixar']);

    Route::get('/contas-receber', [ContaReceberController::class, 'index']);
    Route::get('/contas-receber/{id}', [ContaReceberController::class, 'show']);
    Route::post('/contas-receber', [ContaReceberController::class, 'store']);
    Route::put('/contas-receber/{id}', [ContaReceberController::class, 'update']);
    Route::delete('/contas-receber/{id}', [ContaReceberController::class, 'destroy']);
    Route::post('/contas-receber/{id}/baixar', [ContaReceberController::class, 'baixar']);
    Route::get('/movimento-bancario', [MovimentoBancarioController::class, 'index']);
    Route::post('/movimento-bancario', [MovimentoBancarioController::class, 'store']);
Route::put('/movimento-bancario/{id}', [MovimentoBancarioController::class, 'update']);
Route::delete('/movimento-bancario/{id}', [MovimentoBancarioController::class, 'destroy']);

    Route::get('/conciliacao', [ConciliacaoController::class, 'index']);
    Route::get('/conciliacao/{id}', [ConciliacaoController::class, 'show']);
    Route::post('/conciliacao/{id}/conciliar', [ConciliacaoController::class, 'conciliar']);

    Route::get('/dashboard/resumo', [DashboardController::class, 'resumo']);
    Route::get('/dashboard/fluxo-caixa', [DashboardController::class, 'fluxoCaixa']);

    Route::get('/documentos', [DocumentoController::class, 'index']);
    Route::post('/documentos', [DocumentoController::class, 'store']);
    Route::put('/documentos/{id}', [DocumentoController::class, 'update']);
    Route::delete('/documentos/{id}', [DocumentoController::class, 'destroy']);
    Route::get('/documentos/{id}/vinculos', [DocumentoController::class, 'vinculos']);
    Route::post('/documentos/{id}/vincular', [DocumentoController::class, 'vincular']);
    Route::post('/documentos/importar-xml', [DocumentoController::class, 'importarXml']);
    Route::post('/documentos/{id}/confirmar-contas-pagar', [DocumentoController::class, 'confirmarContasPagar']);
});
Route::middleware(['auth:sanctum'])->prefix('preferences')->group(function () {
    Route::get('/',                   [PreferenceController::class, 'index']);
    Route::put('/',                   [PreferenceController::class, 'update']);
    Route::post('/test-email',        [PreferenceController::class, 'testEmail']);
    Route::get('/maintenance-status', [PreferenceController::class, 'maintenanceStatus']);
});