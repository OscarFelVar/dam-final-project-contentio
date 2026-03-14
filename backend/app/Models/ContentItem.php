<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentItem extends Model
{
    protected $fillable = [
        'clinic_id',
        'created_by',
        'title',
        'type',
        'status',
        'deadline_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline_at' => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    public function stateHistories(): HasMany
    {
        return $this->hasMany(StateHistory::class)->orderBy('created_at');
    }
}
