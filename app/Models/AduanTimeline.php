<?php

namespace App\Models;

use App\Enums\AduanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AduanTimeline extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'aduan_id',
        'user_id',
        'old_status',
        'new_status',
        'komentar',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get the aduan
     */
    public function aduan(): BelongsTo
    {
        return $this->belongsTo(Aduan::class);
    }

    /**
     * Get the user who made the change
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get old status as enum
     */
    public function getOldStatusEnumAttribute(): ?AduanStatus
    {
        return $this->old_status 
            ? AduanStatus::from($this->old_status) 
            : null;
    }

    /**
     * Get new status as enum
     */
    public function getNewStatusEnumAttribute(): AduanStatus
    {
        return AduanStatus::from($this->new_status);
    }

    /**
     * Get change description
     */
    public function getChangeDescriptionAttribute(): string
    {
        $newLabel = $this->new_status_enum->label();
        
        if ($this->old_status) {
            $oldLabel = $this->old_status_enum->label();
            return "Status berubah dari \"{$oldLabel}\" menjadi \"{$newLabel}\"";
        }

        return "Laporan dibuat dengan status \"{$newLabel}\"";
    }

    /**
     * Get the user name or "System"
     */
    public function getActorNameAttribute(): string
    {
        return $this->user?->name ?? 'Sistem';
    }
}
