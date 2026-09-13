<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SelectionStageUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobApplication $application,
        public string $stageName,
        public ?string $stageNote = null
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Update Tahapan Seleksi ({$this->stageName}) - RSI Fatimah Banyuwangi",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.selection-stage-updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
