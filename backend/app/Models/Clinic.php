<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'contact_email',
        'user_id',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contentItems(): HasMany
    {
        return $this->hasMany(ContentItem::class);
    }

    // Usuarios con rol clinic asignados a esta clínica (tabla pivot clinic_user)
    public function clinicUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'clinic_user');
    }
}
