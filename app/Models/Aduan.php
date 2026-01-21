<?php

namespace App\Models;

use App\Enums\AduanStatus;
use App\Enums\ReportChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Aduan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nomor_registrasi',
        'tracking_password',
        'sequence',
        'pelapor_id',
        'user_id',
        'jenis_aduan_id',
        'identitas_terlapor',
        'what',
        'who',
        'when_date',
        'where_location',
        'why',
        'how',
        'lokasi_kejadian',
        'koordinat',
        'status',
        'channel',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => AduanStatus::class,
        'channel' => ReportChannel::class,
        'when_date' => 'date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'tracking_password',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($aduan) {
            // Generate nomor registrasi if not set
            if (!$aduan->nomor_registrasi) {
                $aduan->generateNomorRegistrasi();
            }
        });
    }

    /**
     * Generate unique registration number: WBS-YYYY-NNNNN
     */
    public function generateNomorRegistrasi(): void
    {
        $year = date('Y');
        $lastSequence = static::whereYear('created_at', $year)
            ->max('sequence') ?? 0;
        
        $this->sequence = $lastSequence + 1;
        $this->nomor_registrasi = sprintf('WBS-%s-%05d', $year, $this->sequence);
    }

    /**
     * Generate and hash tracking password
     * Returns the plain password (save this to show to user!)
     */
    public function generateTrackingPassword(): string
    {
        $plainPassword = Str::random(8);
        $this->tracking_password = Hash::make($plainPassword);
        return $plainPassword;
    }

    /**
     * Verify tracking password
     */
    public function verifyTrackingPassword(string $password): bool
    {
        return Hash::check($password, $this->tracking_password);
    }

    /**
     * Get the pelapor (public reporter)
     */
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(Pelapor::class);
    }

    /**
     * Get the user (internal reporter)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the jenis aduan (category)
     */
    public function jenisAduan(): BelongsTo
    {
        return $this->belongsTo(JenisAduan::class, 'jenis_aduan_id', 'slug');
    }

    /**
     * Get bukti pendukung (evidence files)
     */
    public function buktiPendukungs(): HasMany
    {
        return $this->hasMany(BuktiPendukung::class);
    }

    /**
     * Get timeline entries
     */
    public function timelines(): HasMany
    {
        return $this->hasMany(AduanTimeline::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get public timeline entries (visible to pelapor)
     */
    public function publicTimelines(): HasMany
    {
        return $this->hasMany(AduanTimeline::class)
            ->where('is_public', true)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Update status and create timeline entry
     */
    public function updateStatus(AduanStatus $newStatus, ?string $komentar = null, ?User $user = null, bool $isPublic = true): void
    {
        $oldStatus = $this->status;
        
        $this->status = $newStatus;
        $this->save();

        $this->timelines()->create([
            'user_id' => $user?->id,
            'old_status' => $oldStatus?->value,
            'new_status' => $newStatus->value,
            'komentar' => $komentar,
            'is_public' => $isPublic,
        ]);
    }

    /**
     * Get the reporter name (from pelapor or user)
     */
    public function getReporterNameAttribute(): string
    {
        if ($this->pelapor) {
            return $this->pelapor->display_name;
        }
        
        if ($this->user) {
            return $this->user->name;
        }

        return 'Unknown';
    }

    /**
     * Get combined kronologis text
     */
    public function getKronologisLengkapAttribute(): string
    {
        $parts = [];

        if ($this->what) {
            $parts[] = "Apa: {$this->what}";
        }
        if ($this->who) {
            $parts[] = "Siapa: {$this->who}";
        }
        if ($this->when_date) {
            $parts[] = "Kapan: {$this->when_date->format('d F Y')}";
        }
        if ($this->where_location) {
            $parts[] = "Di mana: {$this->where_location}";
        }
        if ($this->why) {
            $parts[] = "Mengapa: {$this->why}";
        }
        if ($this->how) {
            $parts[] = "Bagaimana: {$this->how}";
        }

        return implode("\n\n", $parts);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, AduanStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by channel
     */
    public function scopeChannel($query, ReportChannel $channel)
    {
        return $query->where('channel', $channel);
    }
}
