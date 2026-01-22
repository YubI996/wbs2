<?php

namespace App\Http\Controllers\Api;

use App\Enums\AduanStatus;
use App\Enums\ReportChannel;
use App\Http\Controllers\Controller;
use App\Models\Aduan;
use App\Models\JenisAduan;
use App\Models\Pelapor;
use App\Models\User;
use App\Services\FileValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AduanController extends Controller
{
    public function __construct(
        protected FileValidationService $fileValidator
    ) {}

    /**
     * Store a newly created aduan from SuperApps.
     * POST /api/aduans
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            // Reporter identity
            'email' => 'required|email|max:255',
            'nama' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'nik' => 'nullable|string|max:16',
            'nip' => 'nullable|string|max:18',
            
            // Aduan details
            'jenis_aduan' => 'required|integer|exists:jenis_aduans,slug',
            'identitas_terlapor' => 'required|string|max:500',
            'what' => 'required|string|max:5000',
            'who' => 'required|string|max:500',
            'when_date' => 'required|date',
            'where_location' => 'required|string|max:500',
            'why' => 'nullable|string|max:2000',
            'how' => 'nullable|string|max:2000',
            'lokasi_kejadian' => 'required|string|max:500',
            
            // File bukti (optional, can be multiple)
            'file_bukti' => 'nullable|array|max:5',
            'file_bukti.*' => 'file|max:10240',
        ], [
            // Pesan validasi dalam Bahasa Indonesia
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'nama.required' => 'Nama wajib diisi.',
            'jenis_aduan.required' => 'Jenis aduan wajib dipilih.',
            'jenis_aduan.exists' => 'Jenis aduan tidak valid.',
            'identitas_terlapor.required' => 'Identitas terlapor wajib diisi.',
            'what.required' => 'Penjelasan "Apa yang terjadi" wajib diisi.',
            'who.required' => 'Penjelasan "Siapa yang terlibat" wajib diisi.',
            'when_date.required' => 'Tanggal kejadian wajib diisi.',
            'where_location.required' => 'Lokasi kejadian wajib diisi.',
            'lokasi_kejadian.required' => 'Alamat lokasi kejadian wajib diisi.',
            'file_bukti.max' => 'Maksimal 5 file bukti.',
            'file_bukti.*.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        DB::beginTransaction();

        try {
            // Step 1: Get or Create Pelapor
            $pelapor = Pelapor::where('email', $validated['email'])->first();
            
            if (!$pelapor) {
                $pelapor = Pelapor::create([
                    'nama' => $validated['nama'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'is_anonim' => false,
                    'notify_email' => true,
                ]);

                Log::info('Auto-registered pelapor from SuperApps', [
                    'pelapor_id' => $pelapor->id,
                    'email' => $validated['email'],
                ]);
            }

            // Step 2: Check if user exists (for internal tracking)
            $userId = null;
            if (!empty($validated['nip'])) {
                $user = User::where('nip', $validated['nip'])
                    ->orWhere('email', $validated['email'])
                    ->first();
                $userId = $user?->id;
            }

            // Step 3: Create Aduan
            $aduan = Aduan::create([
                'pelapor_id' => $pelapor->id,
                'user_id' => $userId,
                'jenis_aduan_id' => $validated['jenis_aduan'],
                'identitas_terlapor' => $validated['identitas_terlapor'],
                'what' => $validated['what'],
                'who' => $validated['who'],
                'when_date' => $validated['when_date'],
                'where_location' => $validated['where_location'],
                'why' => $validated['why'] ?? null,
                'how' => $validated['how'] ?? null,
                'lokasi_kejadian' => $validated['lokasi_kejadian'],
                'status' => AduanStatus::PENDING,
                'channel' => ReportChannel::SUPERAPPS, // From external API
            ]);

            // Tracking password sudah di-generate saat create via boot
            $trackingPassword = $aduan->getPlainTrackingPassword();

            // Step 4: Handle file uploads with security validation
            $uploadedFiles = [];
            if ($request->hasFile('file_bukti')) {
                foreach ($request->file('file_bukti') as $file) {
                    // Validate file (detects fake extensions!)
                    $fileInfo = $this->fileValidator->validate($file, 'file_bukti');
                    
                    // Generate unique filename
                    $filename = $this->fileValidator->generateFilename($file, $pelapor->id);
                    
                    // Store file
                    $path = $file->storeAs('bukti/' . $aduan->id, $filename, 'public');
                    
                    // Create bukti record
                    $aduan->buktiPendukungs()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => \App\Enums\FileType::fromMime($fileInfo['mime_type']),
                        'mime_type' => $fileInfo['mime_type'],
                        'file_size' => $fileInfo['size'],
                    ]);
                    
                    $uploadedFiles[] = $filename;
                }
            }

            // Step 5: Create initial timeline entry
            $aduan->timelines()->create([
                'old_status' => null,
                'new_status' => AduanStatus::PENDING->value,
                'komentar' => 'Laporan diterima melalui SuperApps',
                'is_public' => true,
            ]);

            DB::commit();

            Log::info('Aduan created from SuperApps', [
                'aduan_id' => $aduan->id,
                'nomor_registrasi' => $aduan->nomor_registrasi,
                'pelapor_id' => $pelapor->id,
                'files_count' => count($uploadedFiles),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $aduan->id,
                    'nomor_registrasi' => $aduan->nomor_registrasi,
                    'tracking_password' => $trackingPassword,
                    'status' => $aduan->status->value,
                    'status_label' => $aduan->status->label(),
                    'pelapor_id' => $pelapor->id,
                    'files_uploaded' => count($uploadedFiles),
                    'created_at' => $aduan->created_at->toIso8601String(),
                ],
                'message' => 'Aduan berhasil disimpan.',
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            // Cleanup any uploaded files
            if (!empty($uploadedFiles) && isset($aduan)) {
                Storage::disk('public')->deleteDirectory('bukti/' . $aduan->id);
            }

            Log::error('Aduan creation from SuperApps failed', [
                'email' => $validated['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan aduan. Silakan coba lagi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get aduan status by nomor registrasi and tracking password.
     * POST /api/aduans/status
     */
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomor_registrasi' => 'required|string',
            'tracking_password' => 'required|string',
        ], [
            'nomor_registrasi.required' => 'Nomor registrasi wajib diisi.',
            'tracking_password.required' => 'Password tracking wajib diisi.',
        ]);

        $aduan = Aduan::where('nomor_registrasi', $validated['nomor_registrasi'])->first();

        if (!$aduan) {
            return response()->json([
                'success' => false,
                'message' => 'Aduan tidak ditemukan.',
            ], 404);
        }

        if (!$aduan->verifyTrackingPassword($validated['tracking_password'])) {
            return response()->json([
                'success' => false,
                'message' => 'Password tracking tidak valid.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nomor_registrasi' => $aduan->nomor_registrasi,
                'status' => $aduan->status->value,
                'status_label' => $aduan->status->label(),
                'jenis_aduan' => $aduan->jenisAduan?->name,
                'created_at' => $aduan->created_at->toIso8601String(),
                'timeline' => $aduan->publicTimelines->map(fn ($t) => [
                    'status' => $t->new_status,
                    'komentar' => $t->komentar,
                    'tanggal' => $t->created_at->toIso8601String(),
                ]),
            ],
        ]);
    }

    /**
     * Get list of jenis aduan for dropdown.
     * GET /api/jenis-aduans
     */
    public function jenisAduans(): JsonResponse
    {
        $jenisAduans = JenisAduan::where('is_active', true)
            ->orderBy('name')
            ->get(['slug', 'name', 'description']);

        return response()->json([
            'success' => true,
            'data' => $jenisAduans,
        ]);
    }
}
