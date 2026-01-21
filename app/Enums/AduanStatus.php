<?php

namespace App\Enums;

enum AduanStatus: string
{
    case PENDING = 'pending';           // Baru masuk
    case VERIFIKASI = 'verifikasi';     // Sedang diverifikasi
    case PROSES = 'proses';             // Dalam proses penanganan
    case INVESTIGASI = 'investigasi';   // Dalam investigasi
    case SELESAI = 'selesai';           // Selesai ditangani
    case DITOLAK = 'ditolak';           // Ditolak

    /**
     * Get the label for display
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::VERIFIKASI => 'Sedang Diverifikasi',
            self::PROSES => 'Dalam Proses',
            self::INVESTIGASI => 'Dalam Investigasi',
            self::SELESAI => 'Selesai',
            self::DITOLAK => 'Ditolak',
        };
    }

    /**
     * Get the color for display (Filament/Tailwind)
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::VERIFIKASI => 'warning',
            self::PROSES => 'info',
            self::INVESTIGASI => 'primary',
            self::SELESAI => 'success',
            self::DITOLAK => 'danger',
        };
    }

    /**
     * Get the icon for display
     */
    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'heroicon-o-clock',
            self::VERIFIKASI => 'heroicon-o-magnifying-glass',
            self::PROSES => 'heroicon-o-cog',
            self::INVESTIGASI => 'heroicon-o-document-magnifying-glass',
            self::SELESAI => 'heroicon-o-check-circle',
            self::DITOLAK => 'heroicon-o-x-circle',
        };
    }

    /**
     * Check if status can be changed to given status
     */
    public function canTransitionTo(AduanStatus $newStatus): bool
    {
        return match($this) {
            self::PENDING => in_array($newStatus, [self::VERIFIKASI, self::DITOLAK]),
            self::VERIFIKASI => in_array($newStatus, [self::PROSES, self::DITOLAK]),
            self::PROSES => in_array($newStatus, [self::INVESTIGASI, self::SELESAI]),
            self::INVESTIGASI => in_array($newStatus, [self::SELESAI]),
            self::SELESAI => false,
            self::DITOLAK => false,
        };
    }

    /**
     * Get all statuses as options for select
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
            ->toArray();
    }
}
