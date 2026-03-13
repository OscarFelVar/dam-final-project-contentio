<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
});

// Rutas de prueba para el middleware de roles (se eliminarán en sprints posteriores)
Route::middleware(['auth:sanctum', 'role:agency'])->group(function () {
    Route::get('test/agency-only', fn() => response()->json(['message' => 'Hello agency!']));
});

Route::middleware(['auth:sanctum', 'role:clinic'])->group(function () {
    Route::get('test/clinic-only', fn() => response()->json(['message' => 'Hello clinic!']));
});

Route::middleware(['auth:sanctum', 'role:admin,agency'])->group(function () {
    Route::get('test/admin-or-agency', fn() => response()->json(['message' => 'Hello admin or agency!']));
});
