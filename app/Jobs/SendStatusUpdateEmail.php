<?php

namespace App\Jobs;

use App\Enums\AduanStatus;
use App\Mail\StatusUpdatedMail;
use App\Models\Aduan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendStatusUpdateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Aduan $aduan,
        public AduanStatus $newStatus,
        public ?string $komentar = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get email from pelapor
        $pelapor = $this->aduan->pelapor;
        
        if (!$pelapor || !$pelapor->notify_email || !$pelapor->email) {
            Log::info('Skipping status update email - no email opt-in', [
                'aduan_id' => $this->aduan->id,
            ]);
            return;
        }
        
        try {
            Mail::to($pelapor->email)
                ->send(new StatusUpdatedMail($this->aduan, $this->newStatus, $this->komentar));
            
            Log::info('Status update email sent', [
                'aduan_id' => $this->aduan->id,
                'nomor_registrasi' => $this->aduan->nomor_registrasi,
                'email' => $pelapor->email,
                'new_status' => $this->newStatus->value,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send status update email', [
                'aduan_id' => $this->aduan->id,
                'email' => $pelapor->email,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendStatusUpdateEmail job failed', [
            'aduan_id' => $this->aduan->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
