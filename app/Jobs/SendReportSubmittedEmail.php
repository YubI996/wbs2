<?php

namespace App\Jobs;

use App\Mail\ReportSubmittedMail;
use App\Models\Aduan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReportSubmittedEmail implements ShouldQueue
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
        public string $trackingPassword,
        public string $email
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->email)
                ->send(new ReportSubmittedMail($this->aduan, $this->trackingPassword));
            
            Log::info('Report submitted email sent', [
                'aduan_id' => $this->aduan->id,
                'nomor_registrasi' => $this->aduan->nomor_registrasi,
                'email' => $this->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send report submitted email', [
                'aduan_id' => $this->aduan->id,
                'email' => $this->email,
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
        Log::error('SendReportSubmittedEmail job failed', [
            'aduan_id' => $this->aduan->id,
            'email' => $this->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
