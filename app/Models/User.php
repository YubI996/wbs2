<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'nik',
        'phone',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'slug');
    }

    /**
     * Get aduans submitted by this user (internal reporter)
     */
    public function aduans(): HasMany
    {
        return $this->hasMany(Aduan::class);
    }

    /**
     * Get timeline entries created by this user
     */
    public function aduanTimelines(): HasMany
    {
        return $this->hasMany(AduanTimeline::class);
    }

    /**
     * Check if user can access Filament panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow access based on panel ID and user role
        return match($panel->getId()) {
            'admin' => $this->isAdmin(),
            'verifikator' => $this->isVerifikator() || $this->isAdmin(),
            'inspektur' => $this->isInspektur() || $this->isAdmin(),
            'pengadu' => true, // All logged in users can access pengadu panel
            default => false,
        };
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role_id === Role::ADMIN;
    }

    /**
     * Check if user is verifikator
     */
    public function isVerifikator(): bool
    {
        return $this->role_id === Role::VERIFIKATOR;
    }

    /**
     * Check if user is inspektur
     */
    public function isInspektur(): bool
    {
        return $this->role_id === Role::INSPEKTUR;
    }

    /**
     * Check if user is pengadu (internal reporter)
     */
    public function isPengadu(): bool
    {
        return $this->role_id === Role::PENGADU;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->role_id === $roleSlug;
    }

    /**
     * Get role name
     */
    public function getRoleNameAttribute(): string
    {
        return $this->role?->name ?? 'Unknown';
    }
}

