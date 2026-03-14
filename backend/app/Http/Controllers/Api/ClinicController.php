<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// ─── NOTA DE DISEÑO (Sprint 2) ────────────────────────────────────────────────
// Por ahora solo exponemos index (listar clínicas del usuario autenticado).
// El CRUD completo de clínicas se implementará en Sprint 3+ cuando se gestione
// la relación many-to-many agencia↔clínica (tabla clinic_user).
// ─────────────────────────────────────────────────────────────────────────────

class ClinicController extends Controller
{
    // GET /clinics
    // Devuelve las clínicas que pertenecen al usuario autenticado.
    public function index(Request $request): JsonResponse
    {
        $clinics = $request->user()->clinics()->orderBy('name')->get();
        return response()->json($clinics);
    }
}
