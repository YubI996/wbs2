<?php

namespace Tests\Feature\Api;

use App\Models\JenisAduan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AduanApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'sk_live_wbs_superapps_2026';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed jenis aduan
        JenisAduan::create([
            'slug' => 1,
            'name' => 'Pelanggaran Disiplin Pegawai',
            'is_active' => true,
        ]);
        
        Storage::fake('public');
    }

    /**
     * Test mendapatkan daftar jenis aduan (public endpoint)
     */
    public function test_can_get_jenis_aduans_without_api_key(): void
    {
        $response = $this->getJson('/api/jenis-aduans');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['slug', 'name'],
                ],
            ]);
    }

    /**
     * Test membuat aduan tanpa API key (harus ditolak)
     */
    public function test_cannot_create_aduan_without_api_key(): void
    {
        $response = $this->postJson('/api/aduans', []);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'API key tidak ditemukan. Sertakan header X-API-Key.',
            ]);
    }

    /**
     * Test membuat aduan dengan API key tidak valid
     */
    public function test_cannot_create_aduan_with_invalid_api_key(): void
    {
        $response = $this->postJson('/api/aduans', [], [
            'X-API-Key' => 'invalid-key',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'API key tidak valid.',
            ]);
    }

    /**
     * Test membuat aduan dengan data lengkap (tanpa file)
     */
    public function test_can_create_aduan_with_valid_data(): void
    {
        $response = $this->postJson('/api/aduans', [
            'email' => 'pelapor@example.com',
            'nama' => 'John Doe',
            'phone' => '081234567890',
            'jenis_aduan' => 1,
            'identitas_terlapor' => 'Kepala Bagian X',
            'what' => 'Terjadi penyalahgunaan anggaran',
            'who' => 'Kepala Bagian X',
            'when_date' => '2026-01-15',
            'where_location' => 'Kantor Dinas Y',
            'why' => 'Untuk kepentingan pribadi',
            'how' => 'Kronologis lengkap',
            'lokasi_kejadian' => 'Jl. Contoh No. 123, Bontang',
        ], [
            'X-API-Key' => $this->apiKey,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Aduan berhasil disimpan.',
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'nomor_registrasi',
                    'tracking_password',
                    'status',
                    'status_label',
                    'pelapor_id',
                    'files_uploaded',
                    'created_at',
                ],
                'message',
            ]);
    }

    /**
     * Test validasi data wajib
     */
    public function test_validation_for_required_fields(): void
    {
        $response = $this->postJson('/api/aduans', [
            'email' => 'invalid-email',
        ], [
            'X-API-Key' => $this->apiKey,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'nama',
                'jenis_aduan',
                'identitas_terlapor',
                'what',
                'who',
                'when_date',
                'where_location',
                'lokasi_kejadian',
            ]);
    }

    /**
     * Test deteksi fake file extension
     */
    public function test_detects_fake_file_extension(): void
    {
        // Create a text file but rename to .jpg (fake extension)
        $fakeImage = UploadedFile::fake()->create('fake.jpg', 100, 'text/plain');

        $response = $this->postJson('/api/aduans', [
            'email' => 'pelapor@example.com',
            'nama' => 'John Doe',
            'jenis_aduan' => 1,
            'identitas_terlapor' => 'Kepala Bagian X',
            'what' => 'Terjadi penyalahgunaan anggaran',
            'who' => 'Kepala Bagian X',
            'when_date' => '2026-01-15',
            'where_location' => 'Kantor Dinas Y',
            'lokasi_kejadian' => 'Jl. Contoh No. 123',
            'file_bukti' => [$fakeImage],
        ], [
            'X-API-Key' => $this->apiKey,
        ]);

        // Should fail because extension doesn't match MIME type
        $response->assertStatus(422);
    }

    /**
     * Test cek status aduan
     */
    public function test_can_check_aduan_status(): void
    {
        // First create an aduan
        $createResponse = $this->postJson('/api/aduans', [
            'email' => 'pelapor@example.com',
            'nama' => 'John Doe',
            'jenis_aduan' => 1,
            'identitas_terlapor' => 'Kepala Bagian X',
            'what' => 'Terjadi penyalahgunaan anggaran',
            'who' => 'Kepala Bagian X',
            'when_date' => '2026-01-15',
            'where_location' => 'Kantor Dinas Y',
            'lokasi_kejadian' => 'Jl. Contoh No. 123',
        ], [
            'X-API-Key' => $this->apiKey,
        ]);

        $createResponse->assertStatus(201);

        $nomorRegistrasi = $createResponse->json('data.nomor_registrasi');
        $trackingPassword = $createResponse->json('data.tracking_password');

        // Now check status
        $statusResponse = $this->postJson('/api/aduans/status', [
            'nomor_registrasi' => $nomorRegistrasi,
            'tracking_password' => $trackingPassword,
        ], [
            'X-API-Key' => $this->apiKey,
        ]);

        $statusResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'nomor_registrasi',
                    'status',
                    'status_label',
                    'timeline',
                ],
            ]);
    }

    /**
     * Test cek status dengan password salah
     */
    public function test_cannot_check_status_with_wrong_password(): void
    {
        // First create an aduan
        $createResponse = $this->postJson('/api/aduans', [
            'email' => 'pelapor@example.com',
            'nama' => 'John Doe',
            'jenis_aduan' => 1,
            'identitas_terlapor' => 'Kepala Bagian X',
            'what' => 'Terjadi penyalahgunaan anggaran',
            'who' => 'Kepala Bagian X',
            'when_date' => '2026-01-15',
            'where_location' => 'Kantor Dinas Y',
            'lokasi_kejadian' => 'Jl. Contoh No. 123',
        ], [
            'X-API-Key' => $this->apiKey,
        ]);

        $nomorRegistrasi = $createResponse->json('data.nomor_registrasi');

        // Try with wrong password
        $statusResponse = $this->postJson('/api/aduans/status', [
            'nomor_registrasi' => $nomorRegistrasi,
            'tracking_password' => 'wrong-password',
        ], [
            'X-API-Key' => $this->apiKey,
        ]);

        $statusResponse->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Password tracking tidak valid.',
            ]);
    }
}
