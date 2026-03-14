<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\ContentItemController;
use App\Http\Controllers\Api\MediaAssetController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
});

// ─── Clinics (solo agencias) ─────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:agency'])->group(function () {
    Route::get('clinics', [ClinicController::class, 'index']);
});

// ─── Content Items + Media (solo agencias) ───────────────────────────────────
Route::middleware(['auth:sanctum', 'role:agency'])->group(function () {
    Route::apiResource('content-items', ContentItemController::class);

    // Media assets anidados bajo su content item
    // POST   /content-items/{contentItem}/media
    // DELETE /content-items/{contentItem}/media/{mediaAsset}
    Route::post('content-items/{contentItem}/media', [MediaAssetController::class, 'store']);
    Route::delete('content-items/{contentItem}/media/{mediaAsset}', [MediaAssetController::class, 'destroy']);
});

// Rutas de prueba para el middleware de roles (se eliminar­n en sprints posteriores)
Route::middleware(['auth:sanctum', 'role:agency'])->group(function () {
    Route::get('test/agency-only', fn() => response()->json(['message' => 'Hello agency!']));
});

Route::middleware(['auth:sanctum', 'role:clinic'])->group(function () {
    Route::get('test/clinic-only', fn() => response()->json(['message' => 'Hello clinic!']));
});

Route::middleware(['auth:sanctum', 'role:admin,agency'])->group(function () {
    Route::get('test/admin-or-agency', fn() => response()->json(['message' => 'Hello admin or agency!']));
});
