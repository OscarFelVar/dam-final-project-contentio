<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\StateHistory;
use App\Notifications\ContentStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContentStateController extends Controller
{
    // Transiciones válidas por rol:
    // agency → puede enviar a revisión o reenviar tras corrección
    // clinic → puede aprobar, rechazar o pedir correcciones
    private const TRANSITIONS = [
        'agency' => [
            'draft'         => 'ready_for_review',
            'in_correction' => 'ready_for_review',
        ],
        'clinic' => [
            'ready_for_review' => ['in_correction', 'approved', 'rejected'],
        ],
    ];

    // POST /content-items/{contentItem}/transition
    // Body: { "status": "ready_for_review" }
    public function transition(Request $request, ContentItem $contentItem): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        $user      = $request->user();
        $role      = $user->role;
        $fromStatus = $contentItem->status;
        $toStatus  = $request->input('status');

        // Verificar que la transición es válida para este rol y estado actual
        if (! $this->isValidTransition($role, $fromStatus, $toStatus)) {
            return response()->json([
                'message' => "Transición no permitida: {$fromStatus} → {$toStatus} para rol {$role}.",
            ], 422);
        }

        // Verificar acceso al content item según rol
        if ($role === 'agency') {
            $owns = $user->clinics()->where('id', $contentItem->clinic_id)->exists();
            abort_if(! $owns, 403, 'Access denied.');
        }

        // Actualizar estado y registrar en el historial (transacción atómica)
        \DB::transaction(function () use ($contentItem, $user, $fromStatus, $toStatus) {
            $contentItem->update(['status' => $toStatus]);

            StateHistory::create([
                'content_item_id' => $contentItem->id,
                'user_id'         => $user->id,
                'from_status'     => $fromStatus,
                'to_status'       => $toStatus,
            ]);
        });

        // Enviar notificación por email al destinatario correspondiente
        $this->notifyStatusChange($contentItem, $fromStatus, $toStatus);

        return response()->json($contentItem->fresh()->load(['clinic', 'mediaAssets', 'stateHistories.user']));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function notifyStatusChange(ContentItem $contentItem, string $fromStatus, string $toStatus): void
    {
        $notification = new ContentStatusChanged($contentItem, $fromStatus, $toStatus);

        if ($toStatus === 'ready_for_review') {
            // Notificar a la clínica por su email de contacto
            $clinic = $contentItem->clinic;
            if ($clinic?->contact_email) {
                Notification::route('mail', $clinic->contact_email)
                    ->notify($notification);
            }
        } else {
            // Notificar al usuario de agencia que creó el contenido
            $contentItem->load('createdBy');
            $contentItem->createdBy?->notify($notification);
        }
    }

    private function isValidTransition(string $role, string $from, string $to): bool
    {
        $allowed = self::TRANSITIONS[$role][$from] ?? null;

        if ($allowed === null) return false;

        // Para agency las transiciones son 1:1 (string)
        // Para clinic pueden ser múltiples (array)
        if (is_array($allowed)) return in_array($to, $allowed);

        return $allowed === $to;
    }
}
