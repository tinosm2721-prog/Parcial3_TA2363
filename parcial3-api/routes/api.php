<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConstanciaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/login/verificar', [AuthController::class, 'verificar'])->middleware('throttle:10,1');
Route::post('/registro', [UserController::class, 'registro'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/cambiar-password', [AuthController::class, 'cambiarPassword']);

    // Administrador y registrador: registrar constancias y generar PDF/CSV
    Route::middleware('rol:administrador,registrador')->group(function () {
        Route::post('/constancias', [ConstanciaController::class, 'store']);
        Route::get('/mis-constancias', [ConstanciaController::class, 'misConstancias']);
        Route::patch('/constancias/{constancia}/estado', [ConstanciaController::class, 'cambiarEstado']);
        Route::get('/reportes/pdf', [ReporteController::class, 'pdf']);
        Route::get('/reportes/csv', [ReporteController::class, 'csv']);    });

    // Solo administrador
    Route::middleware('rol:administrador')->group(function () {
        Route::get('/constancias', [ConstanciaController::class, 'index']);
        Route::get('/constancias/{constancia}', [ConstanciaController::class, 'show']);
        Route::put('/constancias/{constancia}', [ConstanciaController::class, 'update']);
        Route::delete('/constancias/{constancia}', [ConstanciaController::class, 'destroy']);
        Route::post('/constancias/{constancia}/enviar-correo', [ConstanciaController::class, 'enviarCorreo']);
        Route::get('/reportes/grafico', [ReporteController::class, 'grafico']);
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::post('/usuarios', [UserController::class, 'store']);
        Route::get('/usuarios/{usuario}', [UserController::class, 'show']);
        Route::put('/usuarios/{usuario}', [UserController::class, 'update']);
        Route::patch('/usuarios/{usuario}/estado', [UserController::class, 'estado']);
    });
});