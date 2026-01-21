<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Pelapor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama',
        'phone',
        'email',
        'is_anonim',
        'encrypted_identity',
        'notify_email',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_anonim' => 'boolean',
        'notify_email' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'encrypted_identity',
    ];

    /**
     * Get aduans from this pelapor
     */
    public function aduans(): HasMany
    {
        return $this->hasMany(Aduan::class);
    }

    /**
     * Get display name (returns "Anonim" if anonymous)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->is_anonim ? 'Anonim' : $this->nama;
    }

    /**
     * Get display phone (returns null if anonymous)
     */
    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->is_anonim ? null : $this->phone;
    }

    /**
     * Decrypt the identity if admin needs to see it
     */
    public function decryptIdentity(): ?array
    {
        if (!$this->encrypted_identity) {
            return null;
        }

        try {
            return json_decode(Crypt::decryptString($this->encrypted_identity), true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encrypt and store identity for anonymous reporters
     */
    public function encryptAndStoreIdentity(string $nama, string $phone): void
    {
        $this->encrypted_identity = Crypt::encryptString(json_encode([
            'nama' => $nama,
            'phone' => $phone,
        ]));
        $this->nama = 'Anonim';
        $this->phone = '**********';
    }
}
