<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Annotation extends Model
{
    protected $fillable = ['media_asset_id', 'user_id', 'pin_number', 'x_position', 'y_position', 'comment'];

    protected function casts(): array
    {
        return [
            'x_position' => 'float',
            'y_position' => 'float',
            'pin_number' => 'integer',
        ];
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
