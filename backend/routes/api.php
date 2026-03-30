<?php

use App\Http\Controllers\Api\AnnotationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ContentItemController;
use App\Http\Controllers\Api\ContentStateController;
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
    Route::get('clinics',          [ClinicController::class, 'index']);
    Route::post('clinics',         [ClinicController::class, 'store']);
    Route::put('clinics/{clinic}', [ClinicController::class, 'update']);
    Route::delete('clinics/{clinic}', [ClinicController::class, 'destroy']);
});

// ─── Transiciones de estado y comentarios (agencias y clínicas) ──────────────
Route::middleware(['auth:sanctum', 'role:agency,clinic'])->group(function () {
    Route::post('content-items/{contentItem}/transition', [ContentStateController::class, 'transition']);

    // GET    /content-items/{contentItem}/comments
    // POST   /content-items/{contentItem}/comments
    // DELETE /content-items/{contentItem}/comments/{comment}
    Route::get('content-items/{contentItem}/comments', [CommentController::class, 'index']);
    Route::post('content-items/{contentItem}/comments', [CommentController::class, 'store']);
    Route::delete('content-items/{contentItem}/comments/{comment}', [CommentController::class, 'destroy']);

    // GET    /media-assets/{mediaAsset}/annotations
    // POST   /media-assets/{mediaAsset}/annotations
    // DELETE /media-assets/{mediaAsset}/annotations/{annotation}
    Route::get('media-assets/{mediaAsset}/annotations', [AnnotationController::class, 'index']);
    Route::post('media-assets/{mediaAsset}/annotations', [AnnotationController::class, 'store']);
    Route::delete('media-assets/{mediaAsset}/annotations/{annotation}', [AnnotationController::class, 'destroy']);
});

// ─── Content Items (lectura: agencia y clinic; escritura: solo agencia) ───────
Route::middleware(['auth:sanctum', 'role:agency,clinic'])->group(function () {
    Route::get('content-items',            [ContentItemController::class, 'index']);
    Route::get('content-items/{contentItem}', [ContentItemController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'role:agency'])->group(function () {
    Route::post('content-items',               [ContentItemController::class, 'store']);
    Route::put('content-items/{contentItem}',  [ContentItemController::class, 'update']);
    Route::delete('content-items/{contentItem}', [ContentItemController::class, 'destroy']);

    // Media assets
    Route::post('content-items/{contentItem}/media', [MediaAssetController::class, 'store']);
    Route::delete('content-items/{contentItem}/media/{mediaAsset}', [MediaAssetController::class, 'destroy']);

    // Usuarios de clínica
    Route::get('clinics/{clinic}/users',          [ClinicController::class, 'indexUsers']);
    Route::post('clinics/{clinic}/users',         [ClinicController::class, 'storeUser']);
    Route::delete('clinics/{clinic}/users/{user}', [ClinicController::class, 'destroyUser']);
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
