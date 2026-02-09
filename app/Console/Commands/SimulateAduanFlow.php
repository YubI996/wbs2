<?php

namespace App\Console\Commands;

use App\Enums\AduanStatus;
use App\Enums\FileType;
use App\Enums\ReportChannel;
use App\Jobs\SendReportSubmittedEmail;
use App\Jobs\SendStatusUpdateEmail;
use App\Models\Aduan;
use App\Models\AduanTimeline;
use App\Models\BuktiPendukung;
use App\Models\JenisAduan;
use App\Models\Pelapor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulateAduanFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wbs:simulate-flow 
                            {--delay=2 : Delay in seconds between each step}
                            {--email-notifications : Send email notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulasi lengkap alur aduan dari dibuat hingga selesai';

    protected int $delay;
    protected bool $sendEmails;
    protected Aduan $aduan;
    protected Pelapor $pelapor;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->delay = (int) $this->option('delay');
        $this->sendEmails = (bool) $this->option('email-notifications');

        $this->info('═══════════════════════════════════════════════════════');
        $this->info('🛡️  SIMULASI ALUR WBS - WHISTLE BLOWING SYSTEM');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        if ($this->sendEmails) {
            $this->warn('📧 Email notifications: ENABLED');
        } else {
            $this->comment('📧 Email notifications: DISABLED (use --email-notifications to enable)');
        }

        $this->newLine();

        try {
            DB::beginTransaction();

            // Step 1: Buat Pelapor
            $this->step1_BuatPelapor();
            $this->sleep();

            // Step 2: Buat Aduan (Status: PENDING)
            $this->step2_BuatAduan();
            $this->sleep();

            // Step 3: Upload Bukti Pendukung
            $this->step3_UploadBukti();
            $this->sleep();

            // Step 4: Verifikator Review (Status: VERIFIKASI)
            $this->step4_VerifikatorReview();
            $this->sleep();

            // Step 5: Verifikator Approve (Status: PROSES)
            $this->step5_VerifikatorApprove();
            $this->sleep();

            // Step 6: Inspektur Investigasi (Status: INVESTIGASI)
            $this->step6_InspekturInvestigasi();
            $this->sleep();

            // Step 7: Inspektur Selesaikan (Status: SELESAI)
            $this->step7_InspekturSelesaikan();
            $this->sleep();

            // Step 8: Summary
            $this->step8_Summary();

            DB::commit();

            $this->newLine();
            $this->info('═══════════════════════════════════════════════════════');
            $this->info('✅ SIMULASI BERHASIL!');
            $this->info('═══════════════════════════════════════════════════════');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();

            $this->newLine();
            $this->error('❌ SIMULASI GAGAL!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->line($e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    protected function step1_BuatPelapor(): void
    {
        $this->info('📝 STEP 1: Membuat Pelapor');
        $this->line('────────────────────────────────────────────────────');

        // Cek apakah sudah ada pelapor dengan email ini
        $existingPelapor = Pelapor::where('email', 'demo.pelapor@example.com')->first();
        
        if ($existingPelapor) {
            $this->comment('⚠️  Pelapor sudah ada, menggunakan data existing...');
            $this->pelapor = $existingPelapor;
        } else {
            $this->pelapor = Pelapor::create([
                'nama' => 'Budi Santoso',
                'email' => 'demo.pelapor@example.com',
                'phone' => '081234567890',
                'is_anonim' => false,
                'notify_email' => true,
            ]);

            $this->line('✓ Nama     : ' . $this->pelapor->nama);
            $this->line('✓ Email    : ' . $this->pelapor->email);
            $this->line('✓ Phone    : ' . $this->pelapor->phone);
            $this->line('✓ Anonim   : ' . ($this->pelapor->is_anonim ? 'Ya' : 'Tidak'));
        }

        $this->newLine();
    }

    protected function step2_BuatAduan(): void
    {
        $this->info('🚨 STEP 2: Membuat Aduan Baru (Status: PENDING)');
        $this->line('────────────────────────────────────────────────────');

        // Ambil jenis aduan pertama
        $jenisAduan = JenisAduan::first();

        if (!$jenisAduan) {
            throw new \Exception('Tidak ada jenis aduan. Jalankan seeder terlebih dahulu: php artisan db:seed');
        }

        $this->aduan = Aduan::create([
            'pelapor_id' => $this->pelapor->id,
            'user_id' => null, // Pelapor eksternal
            'jenis_aduan_id' => $jenisAduan->slug,
            'identitas_terlapor' => 'Kepala Bagian Keuangan, Dinas XYZ',
            'what' => 'Terdapat indikasi penyalahgunaan anggaran proyek pembangunan infrastruktur senilai Rp 500 juta yang tidak sesuai dengan RAB dan spesifikasi tender.',
            'who' => 'Kepala Bagian Keuangan bersama dengan vendor PT. Contoh Jaya',
            'when_date' => now()->subDays(10),
            'where_location' => 'Kantor Dinas XYZ, Kota Bontang',
            'why' => 'Diduga untuk kepentingan pribadi dan mark-up harga proyek',
            'how' => 'Vendor yang tidak memenuhi kualifikasi tender dipilih, kemudian terjadi mark-up harga material hingga 40% dari harga pasar. Dana yang tidak terpakai diduga mengalir ke rekening pribadi.',
            'lokasi_kejadian' => 'Jl. Sudirman No. 123, Bontang Utara, Kota Bontang',
            'status' => AduanStatus::PENDING,
            'channel' => ReportChannel::WEBSITE,
        ]);

        // Get tracking password sebelum di-hash
        $trackingPassword = $this->aduan->getPlainTrackingPassword();

        // Timeline awal
        $this->aduan->timelines()->create([
            'old_status' => null,
            'new_status' => AduanStatus::PENDING->value,
            'komentar' => 'Laporan diterima melalui website WBS',
            'is_public' => true,
        ]);

        $this->line('✓ Nomor Registrasi : ' . $this->aduan->nomor_registrasi);
        $this->line('✓ Password Tracking: ' . $trackingPassword);
        $this->line('✓ Status           : ' . $this->aduan->status->label());
        $this->line('✓ Jenis Aduan      : ' . $jenisAduan->name);
        $this->line('✓ Terlapor         : ' . $this->aduan->identitas_terlapor);
        $this->line('✓ Channel          : ' . $this->aduan->channel->value);

        // Send email notification
        if ($this->sendEmails) {
            SendReportSubmittedEmail::dispatch($this->aduan, $trackingPassword);
            $this->comment('📧 Email notifikasi terkirim ke pelapor');
        }

        $this->newLine();
    }

    protected function step3_UploadBukti(): void
    {
        $this->info('📎 STEP 3: Upload Bukti Pendukung');
        $this->line('────────────────────────────────────────────────────');

        $buktiData = [
            [
                'file_path' => 'bukti/' . $this->aduan->id . '/dokumen_rab.pdf',
                'file_name' => 'Rencana Anggaran Biaya (RAB) Proyek.pdf',
                'file_type' => FileType::DOKUMEN,
                'mime_type' => 'application/pdf',
                'file_size' => 2457600, // 2.4 MB
            ],
            [
                'file_path' => 'bukti/' . $this->aduan->id . '/foto_lokasi.jpg',
                'file_name' => 'Foto Lokasi Proyek.jpg',
                'file_type' => FileType::FOTO,
                'mime_type' => 'image/jpeg',
                'file_size' => 1536000, // 1.5 MB
            ],
            [
                'file_path' => 'bukti/' . $this->aduan->id . '/rekening_koran.pdf',
                'file_name' => 'Bukti Transfer Rekening.pdf',
                'file_type' => FileType::DOKUMEN,
                'mime_type' => 'application/pdf',
                'file_size' => 892000, // 892 KB
            ],
        ];

        foreach ($buktiData as $index => $data) {
            $bukti = $this->aduan->buktiPendukungs()->create($data);
            $this->line(sprintf(
                '✓ File %d: %s (%s)',
                $index + 1,
                $data['file_name'],
                $this->formatBytes($data['file_size'])
            ));
        }

        $this->comment('Total: ' . count($buktiData) . ' file bukti pendukung');
        $this->newLine();
    }

    protected function step4_VerifikatorReview(): void
    {
        $this->info('👮 STEP 4: Verifikator Melakukan Review (Status: VERIFIKASI)');
        $this->line('────────────────────────────────────────────────────');

        $verifikator = User::where('role_id', Role::VERIFIKATOR)->first();

        if (!$verifikator) {
            $this->warn('⚠️  User Verifikator tidak ditemukan, membuat timeline tanpa user_id');
        }

        $oldStatus = $this->aduan->status;
        $this->aduan->update(['status' => AduanStatus::VERIFIKASI]);

        $timeline = $this->aduan->timelines()->create([
            'old_status' => $oldStatus->value,
            'new_status' => AduanStatus::VERIFIKASI->value,
            'komentar' => 'Laporan sedang diverifikasi oleh tim. Kelengkapan dokumen dan substansi laporan sedang diperiksa.',
            'is_public' => true,
            'user_id' => $verifikator?->id,
        ]);

        $this->line('✓ Status diubah  : ' . $oldStatus->label() . ' → ' . $this->aduan->status->label());
        $this->line('✓ Oleh           : ' . ($verifikator ? $verifikator->name : 'System'));
        $this->line('✓ Komentar       : ' . $timeline->komentar);

        if ($this->sendEmails) {
            SendStatusUpdateEmail::dispatch($this->aduan);
            $this->comment('📧 Email notifikasi status update terkirim');
        }

        $this->newLine();
    }

    protected function step5_VerifikatorApprove(): void
    {
        $this->info('✅ STEP 5: Verifikator Approve (Status: PROSES)');
        $this->line('────────────────────────────────────────────────────');

        $verifikator = User::where('role_id', Role::VERIFIKATOR)->first();

        $oldStatus = $this->aduan->status;
        $this->aduan->update(['status' => AduanStatus::PROSES]);

        $timeline = $this->aduan->timelines()->create([
            'old_status' => $oldStatus->value,
            'new_status' => AduanStatus::PROSES->value,
            'komentar' => 'Laporan telah diverifikasi dan dinyatakan valid. Bukti pendukung memadai. Aduan akan dilanjutkan ke tahap penanganan oleh Inspektur.',
            'is_public' => true,
            'user_id' => $verifikator?->id,
        ]);

        $this->line('✓ Status diubah  : ' . $oldStatus->label() . ' → ' . $this->aduan->status->label());
        $this->line('✓ Oleh           : ' . ($verifikator ? $verifikator->name : 'System'));
        $this->line('✓ Komentar       : ' . $timeline->komentar);

        if ($this->sendEmails) {
            SendStatusUpdateEmail::dispatch($this->aduan);
            $this->comment('📧 Email notifikasi status update terkirim');
        }

        $this->newLine();
    }

    protected function step6_InspekturInvestigasi(): void
    {
        $this->info('🔍 STEP 6: Inspektur Melakukan Investigasi (Status: INVESTIGASI)');
        $this->line('────────────────────────────────────────────────────');

        $inspektur = User::where('role_id', Role::INSPEKTUR)->first();

        $oldStatus = $this->aduan->status;
        $this->aduan->update(['status' => AduanStatus::INVESTIGASI]);

        $timeline = $this->aduan->timelines()->create([
            'old_status' => $oldStatus->value,
            'new_status' => AduanStatus::INVESTIGASI->value,
            'komentar' => 'Tim Inspektur telah memulai investigasi lapangan. Pemeriksaan dokumen tender, wawancara dengan pihak terkait, dan audit keuangan sedang dilakukan.',
            'is_public' => true,
            'user_id' => $inspektur?->id,
        ]);

        // Timeline internal (tidak public)
        $this->aduan->timelines()->create([
            'old_status' => AduanStatus::INVESTIGASI->value,
            'new_status' => AduanStatus::INVESTIGASI->value,
            'komentar' => '[INTERNAL] Temuan awal: Ditemukan ketidaksesuaian RAB dengan realisasi. Vendor tidak memiliki sertifikasi yang disyaratkan. Sedang dilakukan penelusuran aliran dana.',
            'is_public' => false,
            'user_id' => $inspektur?->id,
        ]);

        $this->line('✓ Status diubah  : ' . $oldStatus->label() . ' → ' . $this->aduan->status->label());
        $this->line('✓ Oleh           : ' . ($inspektur ? $inspektur->name : 'System'));
        $this->line('✓ Komentar       : ' . $timeline->komentar);
        $this->comment('  + 1 catatan internal (tidak terlihat publik)');

        if ($this->sendEmails) {
            SendStatusUpdateEmail::dispatch($this->aduan);
            $this->comment('📧 Email notifikasi status update terkirim');
        }

        $this->newLine();
    }

    protected function step7_InspekturSelesaikan(): void
    {
        $this->info('🏁 STEP 7: Inspektur Menyelesaikan Kasus (Status: SELESAI)');
        $this->line('────────────────────────────────────────────────────');

        $inspektur = User::where('role_id', Role::INSPEKTUR)->first();

        $oldStatus = $this->aduan->status;
        $this->aduan->update(['status' => AduanStatus::SELESAI]);

        $timeline = $this->aduan->timelines()->create([
            'old_status' => $oldStatus->value,
            'new_status' => AduanStatus::SELESAI->value,
            'komentar' => 'Investigasi selesai. Laporan Anda TERBUKTI. Tindakan telah diambil: Kepala Bagian Keuangan diberhentikan dari jabatan dan dilakukan proses hukum. Vendor dimasukkan dalam daftar hitam. Dana sebagian berhasil dikembalikan ke negara. Terima kasih atas laporannya.',
            'is_public' => true,
            'user_id' => $inspektur?->id,
        ]);

        $this->line('✓ Status diubah  : ' . $oldStatus->label() . ' → ' . $this->aduan->status->label());
        $this->line('✓ Oleh           : ' . ($inspektur ? $inspektur->name : 'System'));
        $this->line('✓ Komentar       : ' . $timeline->komentar);
        $this->line('✓ Hasil          : TERBUKTI - Tindakan telah diambil');

        if ($this->sendEmails) {
            SendStatusUpdateEmail::dispatch($this->aduan);
            $this->comment('📧 Email notifikasi status update terkirim');
        }

        $this->newLine();
    }

    protected function step8_Summary(): void
    {
        $this->info('📊 STEP 8: Ringkasan Simulasi');
        $this->line('────────────────────────────────────────────────────');
        $this->newLine();

        // Load fresh data
        $this->aduan->load(['pelapor', 'jenisAduan', 'buktiPendukungs', 'timelines']);

        $this->table(
            ['Informasi', 'Detail'],
            [
                ['Nomor Registrasi', $this->aduan->nomor_registrasi],
                ['Pelapor', $this->aduan->pelapor->nama . ' (' . $this->aduan->pelapor->email . ')'],
                ['Jenis Aduan', $this->aduan->jenisAduan->name],
                ['Status Akhir', $this->aduan->status->label()],
                ['Channel', ucfirst($this->aduan->channel->value)],
                ['Bukti Pendukung', $this->aduan->buktiPendukungs->count() . ' file'],
                ['Timeline Entries', $this->aduan->timelines->count() . ' entri'],
                ['Tanggal Dibuat', $this->aduan->created_at->format('d/m/Y H:i')],
                ['Tanggal Selesai', $this->aduan->updated_at->format('d/m/Y H:i')],
                ['Durasi', $this->aduan->created_at->diffForHumans($this->aduan->updated_at, true)],
            ]
        );

        $this->newLine();
        $this->info('📋 Timeline Lengkap:');
        $this->line('────────────────────────────────────────────────────');

        foreach ($this->aduan->timelines as $index => $timeline) {
            $visibility = $timeline->is_public ? '🌐 Public' : '🔒 Internal';
            $this->line(sprintf(
                '%d. [%s] %s → %s',
                $index + 1,
                $visibility,
                $timeline->old_status ? AduanStatus::from($timeline->old_status)->label() : 'Baru',
                AduanStatus::from($timeline->new_status)->label()
            ));
            $this->comment('   ' . $timeline->komentar);
            $this->line('   Waktu: ' . $timeline->created_at->format('d/m/Y H:i:s'));
            $this->newLine();
        }
    }

    protected function sleep(): void
    {
        if ($this->delay > 0) {
            sleep($this->delay);
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
