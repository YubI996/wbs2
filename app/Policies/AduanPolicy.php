<?php

namespace App\Policies;

use App\Enums\AduanStatus;
use App\Models\Aduan;
use App\Models\BuktiPendukung;
use App\Models\User;

class AduanPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isVerifikator() || $user->isInspektur();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Aduan $aduan): bool
    {
        // Admin can view all
        if ($user->isAdmin()) {
            return true;
        }

        // Verifikator can view pending, verifikasi, proses status
        if ($user->isVerifikator()) {
            return in_array($aduan->status, [
                AduanStatus::PENDING,
                AduanStatus::VERIFIKASI,
                AduanStatus::PROSES,
                AduanStatus::DITOLAK,
            ]);
        }

        // Inspektur can view proses, investigasi, selesai status
        if ($user->isInspektur()) {
            return in_array($aduan->status, [
                AduanStatus::PROSES,
                AduanStatus::INVESTIGASI,
                AduanStatus::SELESAI,
            ]);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin can create aduan from panel
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Aduan $aduan): bool
    {
        // Admin can update all
        if ($user->isAdmin()) {
            return true;
        }

        // Verifikator can update pending and verifikasi status
        if ($user->isVerifikator()) {
            return in_array($aduan->status, [
                AduanStatus::PENDING,
                AduanStatus::VERIFIKASI,
            ]);
        }

        // Inspektur can update proses and investigasi status
        if ($user->isInspektur()) {
            return in_array($aduan->status, [
                AduanStatus::PROSES,
                AduanStatus::INVESTIGASI,
            ]);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Aduan $aduan): bool
    {
        // Only admin can delete
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Aduan $aduan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Aduan $aduan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can download bukti pendukung.
     */
    public function downloadBukti(User $user, Aduan $aduan, BuktiPendukung $bukti): bool
    {
        // Verify bukti belongs to aduan
        if ($bukti->aduan_id !== $aduan->id) {
            return false;
        }

        // Use same rules as view
        return $this->view($user, $aduan);
    }

    /**
     * Determine whether the user can manage bukti pendukung.
     */
    public function manageBukti(User $user, Aduan $aduan): bool
    {
        // Use same rules as update
        return $this->update($user, $aduan);
    }

    /**
     * Determine whether the user can update status.
     */
    public function updateStatus(User $user, Aduan $aduan): bool
    {
        // Admin can always update status
        if ($user->isAdmin()) {
            return true;
        }

        // Verifikator can update status for pending/verifikasi
        if ($user->isVerifikator()) {
            return in_array($aduan->status, [
                AduanStatus::PENDING,
                AduanStatus::VERIFIKASI,
            ]);
        }

        // Inspektur can update status for proses/investigasi
        if ($user->isInspektur()) {
            return in_array($aduan->status, [
                AduanStatus::PROSES,
                AduanStatus::INVESTIGASI,
            ]);
        }

        return false;
    }

    /**
     * Get allowed status transitions for the user.
     */
    public static function getAllowedStatusTransitions(User $user, Aduan $aduan): array
    {
        if ($user->isAdmin()) {
            // Admin can transition to any status
            return AduanStatus::cases();
        }

        if ($user->isVerifikator()) {
            return match ($aduan->status) {
                AduanStatus::PENDING => [AduanStatus::VERIFIKASI, AduanStatus::DITOLAK],
                AduanStatus::VERIFIKASI => [AduanStatus::PROSES, AduanStatus::DITOLAK],
                default => [],
            };
        }

        if ($user->isInspektur()) {
            return match ($aduan->status) {
                AduanStatus::PROSES => [AduanStatus::INVESTIGASI, AduanStatus::SELESAI],
                AduanStatus::INVESTIGASI => [AduanStatus::SELESAI],
                default => [],
            };
        }

        return [];
    }
}
