<?php

namespace App\Console\Commands;

use App\Models\ContentItem;
use App\Models\StateHistory;
use App\Notifications\ContentStatusChanged;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AutoApproveExpiredContent extends Command
{
    protected $signature   = 'content:auto-approve-expired';
    protected $description = 'Aprueba automáticamente los contenidos cuya fecha límite ha vencido';

    public function handle(): int
    {
        $expired = ContentItem::where('status', 'ready_for_review')
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', now())
            ->with(['clinic', 'createdBy'])
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No hay contenidos vencidos pendientes de aprobación.');
            return self::SUCCESS;
        }

        foreach ($expired as $item) {
            DB::transaction(function () use ($item) {
                $item->update(['status' => 'approved']);

                StateHistory::create([
                    'content_item_id' => $item->id,
                    'user_id'         => null,
                    'from_status'     => 'ready_for_review',
                    'to_status'       => 'approved',
                ]);
            });

            try {
                $item->createdBy?->notify(
                    new ContentStatusChanged($item, 'ready_for_review', 'approved')
                );
            } catch (\Exception $e) {
                Log::warning('Auto-approve: email fallido', [
                    'content_item_id' => $item->id,
                    'error'           => $e->getMessage(),
                ]);
            }

            $this->line("Aprobado: [{$item->id}] {$item->title}");
        }

        $this->info("Total aprobados: {$expired->count()}");
        return self::SUCCESS;
    }
}
