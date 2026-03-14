<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\MediaAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// ─── NOTA DE DISEÑO ───────────────────────────────────────────────────────────
// Los archivos se guardan en el disco "local" (storage/app/private/) — privado.
// No son accesibles por URL pública. El frontend recibe file_path como referencia.
// TODO Sprint 3+: añadir endpoint GET /content-items/{id}/media/{mediaId}/download
// que sirva el archivo con Storage::download() tras verificar autenticación.
// ─────────────────────────────────────────────────────────────────────────────

class MediaAssetController extends Controller
{
    // POST /content-items/{contentItem}/media
    // Acepta un único archivo por llamada. Para subir varios, llamar varias veces.
    public function store(Request $request, ContentItem $contentItem): JsonResponse
    {
        $this->authorizeAccess($request, $contentItem);

        $request->validate([
            'file' => ['required', 'file', 'max:102400', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,webm'],
        ]);

        $file = $request->file('file');

        // Guardamos en media/{content_item_id}/nombre-unico.ext
        $path = $file->store("media/{$contentItem->id}", 'local');

        $asset = MediaAsset::create([
            'content_item_id' => $contentItem->id,
            'file_path'       => $path,
            'file_name'       => $file->getClientOriginalName(),
            'mime_type'       => $file->getMimeType(),
            'size'            => $file->getSize(),
        ]);

        return response()->json($asset, 201);
    }

    // DELETE /content-items/{contentItem}/media/{mediaAsset}
    public function destroy(Request $request, ContentItem $contentItem, MediaAsset $mediaAsset): JsonResponse
    {
        $this->authorizeAccess($request, $contentItem);

        // Verificar que el asset pertenece al content item de la URL
        abort_if($mediaAsset->content_item_id !== $contentItem->id, 404);

        Storage::disk('local')->delete($mediaAsset->file_path);
        $mediaAsset->delete();

        return response()->json(null, 204);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function authorizeAccess(Request $request, ContentItem $contentItem): void
    {
        $ownsClinic = $request->user()
            ->clinics()
            ->where('id', $contentItem->clinic_id)
            ->exists();

        abort_if(! $ownsClinic, 403, 'Access denied.');
    }
}
