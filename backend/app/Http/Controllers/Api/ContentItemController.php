<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// ─── NOTA DE DISEÑO (Sprint 2) ────────────────────────────────────────────────
// La agencia especifica clinic_id al crear un content item.
// Validamos que esa clínica pertenezca al usuario autenticado (owner).
// TODO Sprint 3+: cuando exista gestión completa de clínicas (clinic_user pivot),
// ampliar la validación para cubrir el modelo many-to-many agencia↔clínica.
// ─────────────────────────────────────────────────────────────────────────────

class ContentItemController extends Controller
{
    // GET /content-items
    // Devuelve todos los content items de las clínicas del usuario autenticado.
    public function index(Request $request): JsonResponse
    {
        $items = ContentItem::with(['clinic', 'mediaAssets'])
            ->whereHas('clinic', fn($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->get();

        return response()->json($items);
    }

    // POST /content-items
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clinic_id'   => ['required', 'integer', 'exists:clinics,id'],
            'title'       => ['required', 'string', 'max:255'],
            'type'        => ['required', 'in:reel,post,story'],
            'deadline_at' => ['nullable', 'date'],
        ]);

        // Verificar que la clínica pertenece al usuario autenticado
        $request->user()->load('clinics');
        $ownsClinic = $request->user()
            ->clinics()
            ->where('id', $data['clinic_id'])
            ->exists();

        if (! $ownsClinic) {
            return response()->json(['message' => 'Clinic not found.'], 403);
        }

        $item = ContentItem::create([
            ...$data,
            'created_by' => $request->user()->id,
            'status'     => 'draft',
        ]);

        return response()->json($item->load(['clinic', 'mediaAssets']), 201);
    }

    // GET /content-items/{contentItem}
    public function show(Request $request, ContentItem $contentItem): JsonResponse
    {
        $this->authorizeAccess($request, $contentItem);

        return response()->json($contentItem->load(['clinic', 'mediaAssets', 'createdBy']));
    }

    // PUT /content-items/{contentItem}
    // Solo se pueden editar title, type y deadline_at. El status lo gestiona la state machine (#18).
    public function update(Request $request, ContentItem $contentItem): JsonResponse
    {
        $this->authorizeAccess($request, $contentItem);

        $data = $request->validate([
            'title'       => ['sometimes', 'string', 'max:255'],
            'type'        => ['sometimes', 'in:reel,post,story'],
            'deadline_at' => ['nullable', 'date'],
        ]);

        $contentItem->update($data);

        return response()->json($contentItem->load(['clinic', 'mediaAssets']));
    }

    // DELETE /content-items/{contentItem}
    // Solo se pueden eliminar content items en estado draft.
    public function destroy(Request $request, ContentItem $contentItem): JsonResponse
    {
        $this->authorizeAccess($request, $contentItem);

        if ($contentItem->status !== 'draft') {
            return response()->json(['message' => 'Only draft items can be deleted.'], 422);
        }

        $contentItem->delete();

        return response()->json(null, 204);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    // Verifica que el content item pertenece a una clínica del usuario autenticado.
    private function authorizeAccess(Request $request, ContentItem $contentItem): void
    {
        $ownsClinic = $request->user()
            ->clinics()
            ->where('id', $contentItem->clinic_id)
            ->exists();

        abort_if(! $ownsClinic, 403, 'Access denied.');
    }
}
