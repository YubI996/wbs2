<?php

namespace App\Models;

use App\Enums\FileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BuktiPendukung extends Model
{
    use HasFactory;

    /**
     * The columns that should have UUIDs generated.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'aduan_id',
        'file_path',
        'file_name',
        'file_type',
        'mime_type',
        'file_size',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'file_type' => FileType::class,
        'file_size' => 'integer',
    ];

    /**
     * Get the aduan that owns this bukti
     */
    public function aduan(): BelongsTo
    {
        return $this->belongsTo(Aduan::class);
    }

    /**
     * Get the file URL
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human readable file size
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a document (PDF/Word)
     */
    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Delete the file from storage when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bukti) {
            // Generate UUID if not set
            if (!$bukti->uuid) {
                $bukti->uuid = Str::uuid()->toString();
            }
        });

        static::deleting(function ($bukti) {
            if (Storage::disk('local')->exists($bukti->file_path)) {
                Storage::disk('local')->delete($bukti->file_path);
            }
        });
    }

    /**
     * Get the secure download URL for this file.
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('bukti.download', [
            'aduanUuid' => $this->aduan->uuid,
            'buktiUuid' => $this->uuid,
        ]);
    }

    /**
     * Get the secure preview URL for this file.
     */
    public function getPreviewUrlAttribute(): string
    {
        return route('bukti.preview', [
            'aduanUuid' => $this->aduan->uuid,
            'buktiUuid' => $this->uuid,
        ]);
    }
}
