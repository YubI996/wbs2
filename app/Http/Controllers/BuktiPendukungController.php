<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\BuktiPendukung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BuktiPendukungController extends Controller
{
    /**
     * Download bukti pendukung file with authorization check.
     */
    public function download(Request $request, string $aduanUuid, string $buktiUuid): StreamedResponse
    {
        // Find aduan by UUID
        $aduan = Aduan::where('uuid', $aduanUuid)->firstOrFail();

        // Find bukti by UUID
        $bukti = BuktiPendukung::where('uuid', $buktiUuid)
            ->where('aduan_id', $aduan->id)
            ->firstOrFail();

        // Check authorization
        Gate::authorize('downloadBukti', [$aduan, $bukti]);

        // Check if file exists
        if (!Storage::disk('local')->exists($bukti->file_path)) {
            abort(404, 'File tidak ditemukan');
        }

        // Stream download
        return Storage::disk('local')->download(
            $bukti->file_path,
            $bukti->file_name,
            [
                'Content-Type' => $bukti->mime_type,
            ]
        );
    }

    /**
     * Preview bukti pendukung file (for images) with authorization check.
     */
    public function preview(Request $request, string $aduanUuid, string $buktiUuid): StreamedResponse
    {
        // Find aduan by UUID
        $aduan = Aduan::where('uuid', $aduanUuid)->firstOrFail();

        // Find bukti by UUID
        $bukti = BuktiPendukung::where('uuid', $buktiUuid)
            ->where('aduan_id', $aduan->id)
            ->firstOrFail();

        // Check authorization
        Gate::authorize('downloadBukti', [$aduan, $bukti]);

        // Check if file exists
        if (!Storage::disk('local')->exists($bukti->file_path)) {
            abort(404, 'File tidak ditemukan');
        }

        // Stream file inline (for preview)
        return Storage::disk('local')->response(
            $bukti->file_path,
            $bukti->file_name,
            [
                'Content-Type' => $bukti->mime_type,
                'Content-Disposition' => 'inline; filename="' . $bukti->file_name . '"',
            ]
        );
    }
}
