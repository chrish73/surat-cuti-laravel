<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\PermohonanCuti; // Tambahkan ini

class StatusCutiNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $permohonan;

    /**
     * Create a new message instance.
     *
     * @param  PermohonanCuti  $permohonan
     * @return void
     */
    public function __construct(PermohonanCuti $permohonan)
    {
        $this->permohonan = $permohonan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Status Permohonan Cuti',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.status_cuti',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
