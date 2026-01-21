<?php

namespace App\Mail;

use App\Enums\AduanStatus;
use App\Models\Aduan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Aduan $aduan,
        public AduanStatus $newStatus,
        public ?string $komentar = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update Status Laporan - ' . $this->aduan->nomor_registrasi,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.status-updated',
            with: [
                'aduan' => $this->aduan,
                'newStatus' => $this->newStatus,
                'statusLabel' => $this->newStatus->label(),
                'komentar' => $this->komentar,
                'url' => config('app.url') . '/cek-status',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
