<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public JobApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi Lamaran Pekerjaan - RSI Fatimah Banyuwangi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-submitted',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
