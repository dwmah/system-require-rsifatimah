<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationVerificationResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobApplication $application,
        public bool $isPassed
    ) {}

    public function envelope(): Envelope
    {
        $statusText = $this->isPassed ? 'Lolos Administrasi' : 'Hasil Verifikasi Berkas';

        return new Envelope(
            subject: "[{$statusText}] Pengumuman Rekrutmen - RSI Fatimah Banyuwangi",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-verification-result',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
