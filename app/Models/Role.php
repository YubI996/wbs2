<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'slug';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
    ];

    /**
     * Role constants for easy reference
     */
    public const INSPEKTUR = '1';
    public const VERIFIKATOR = '2';
    public const ADMIN = '3';
    public const PENGADU = '4';

    /**
     * Get users with this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'slug');
    }

    /**
     * Check if this role is admin
     */
    public function isAdmin(): bool
    {
        return $this->slug === self::ADMIN;
    }

    /**
     * Check if this role is verifikator
     */
    public function isVerifikator(): bool
    {
        return $this->slug === self::VERIFIKATOR;
    }

    /**
     * Check if this role is inspektur
     */
    public function isInspektur(): bool
    {
        return $this->slug === self::INSPEKTUR;
    }

    /**
     * Check if this role is pengadu
     */
    public function isPengadu(): bool
    {
        return $this->slug === self::PENGADU;
    }
}
