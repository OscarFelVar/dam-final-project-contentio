<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaAsset extends Model
{
    protected $fillable = [
        'content_item_id',
        'file_path',
        'file_name',
        'mime_type',
        'size',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }
}
